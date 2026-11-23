<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingCampaign;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_images_can_be_uploaded_replaced_and_displayed_in_the_portal(): void
    {
        $this->seed();
        Storage::fake('local');
        $staff = User::factory()->create(['role_id' => 1]);
        $this->actingAs($staff);

        $fields = [
            'name' => 'Buen Fin',
            'starts_on' => today()->toDateString(),
            'ends_on' => today()->addWeek()->toDateString(),
            'audience' => 'all',
            'discount_percent' => 15,
        ];
        $campaign = MarketingCampaign::create($fields);
        $update = route('customers.campaigns.update', $campaign);
        $imageUrl = route('campaigns.image', $campaign);
        $this->get(route('customers.campaigns.edit', $campaign))->assertOk()->assertSee('Agregar imagen a la campaña');

        $this->post($update, $fields + ['_method' => 'PUT', 'image' => UploadedFile::fake()->create('archivo.pdf')])
            ->assertSessionHasErrorsIn('campaign', ['image']);
        $this->post($update, $fields + ['_method' => 'PUT', 'image' => new UploadedFile(public_path('images/portal-buen-fin.jpg'), 'buen-fin.jpg', null, null, true)])
            ->assertSessionHasNoErrors()->assertRedirect(route('customers.index'));
        $firstPath = $campaign->refresh()->image_path;
        Storage::disk('local')->assertExists($firstPath);
        $this->get($imageUrl)->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        $client = User::factory()->create(['role_id' => 4]);
        $this->actingAs($client)->get(route('portal.home'))->assertOk()->assertSee($imageUrl, false);
        $this->get($imageUrl)->assertOk();
        $this->put($update, $fields)->assertForbidden();

        $this->actingAs($staff)->post($update, $fields + ['_method' => 'PUT', 'image' => new UploadedFile(public_path('images/portal-papeleria.jpg'), 'papeleria.jpg', null, null, true)])
            ->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($firstPath);
        $replacement = $campaign->refresh()->image_path;
        Storage::disk('local')->assertExists($replacement);

        $this->delete(route('customers.campaigns.destroy', $campaign))->assertRedirect(route('customers.index'));
        Storage::disk('local')->assertMissing($replacement);
    }

    public function test_ranking_and_eligible_discounts_appear_in_the_cart(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role_id' => 1]));

        $leader = Customer::create(['name' => 'Cliente frecuente', 'preferred_discount' => 12.5]);
        $prospect = Customer::create(['name' => 'Prospecto']);
        Order::create(['customer_id' => $leader->id, 'order_number' => 'VENTA', 'status' => 'completado', 'total' => 1500]);
        $quote = Order::create(['customer_id' => $prospect->id, 'order_number' => 'COT', 'status' => 'pendiente', 'total' => 9000]);

        $campaign = [
            'name' => 'Regreso a clases',
            'starts_on' => today()->toDateString(),
            'ends_on' => today()->addWeek()->toDateString(),
            'audience' => 'top',
            'discount_percent' => 15,
        ];
        $this->post(route('customers.campaigns.store'), $campaign)
            ->assertSessionHasNoErrors()->assertRedirect(route('customers.index'));

        $this->get(route('customers.index'))->assertOk()
            ->assertSee('Clientes que más compran')
            ->assertSee('Regreso a clases')
            ->assertViewHas('topCustomers', fn ($customers) => $customers->modelKeys() === [$leader->id]);

        $this->get(route('orders.pos', $quote))->assertOk()
            ->assertViewHas('recommendations', fn ($discounts) => array_column($discounts[$leader->id], 'percent') === [12.5, 75, 15.0]
                && $discounts[$prospect->id] === []);

        $savedCampaign = MarketingCampaign::firstOrFail();
        $this->get(route('customers.campaigns.edit', $savedCampaign))->assertOk()->assertSee('Regreso a clases');
        $this->put(route('customers.campaigns.update', $savedCampaign), [...$campaign, 'discount_percent' => 20])
            ->assertSessionHasNoErrors()->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('marketing_campaigns', ['id' => $savedCampaign->id, 'discount_percent' => 20]);

        $this->patch(route('customers.campaigns.end', $savedCampaign))->assertRedirect(route('customers.index'));
        $this->get(route('customers.index'))->assertOk()->assertSee('Terminada antes de tiempo');
        $this->get(route('orders.pos', $quote))->assertOk()
            ->assertViewHas('recommendations', fn ($discounts) => array_column($discounts[$leader->id], 'percent') === [12.5, 75]);

        $this->delete(route('customers.campaigns.destroy', $savedCampaign))->assertRedirect(route('customers.index'));
        $this->assertModelMissing($savedCampaign);
    }

    public function test_sales_require_a_valid_payment_method_but_quotes_do_not(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role_id' => 2]));

        $order = Order::create(['order_number' => 'COT-PAGO', 'status' => 'pendiente']);
        $product = Product::create(['code' => 'PAGO-SRV', 'name' => 'Trabajo', 'type' => 'SERVICIO']);
        $payload = ['cart' => [['id' => $product->id, 'quantity' => 1, 'price' => 100]]];
        $url = route('orders.pos.update', $order);

        $this->postJson($url, $payload + ['status' => 'pendiente'])->assertOk();
        $this->postJson($url, $payload + ['status' => 'completado'])
            ->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        $this->postJson($url, $payload + ['status' => 'completado', 'payment_method' => 'debito'])->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_method' => 'debito', 'status' => 'completado']);
        $this->get(route('orders.pos', $order))->assertOk()->assertSee('Tarjeta de débito');
    }
}

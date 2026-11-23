<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingCampaign;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_filters_and_price_order_use_only_public_products_and_services(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['name' => 'Cliente portal', 'role_id' => 4]));
        foreach ([
            ['name' => 'Papel económico', 'code' => 'PAP-01', 'type' => 'PRODUCTO', 'category' => 'Papelería', 'price' => 10],
            ['name' => 'Papel premium', 'code' => 'PAP-02', 'type' => 'PRODUCTO', 'category' => 'Papelería', 'price' => 50],
            ['name' => 'Otra categoría', 'code' => 'PAP-03', 'type' => 'PRODUCTO', 'category' => 'Otros', 'price' => 25],
            ['name' => 'Papel interno', 'code' => 'PAP-04', 'type' => 'INSUMO', 'category' => 'Interno', 'price' => 20],
            ['name' => 'Papel cortado', 'code' => 'PAP-05', 'type' => 'SERVICIO', 'category' => 'Papelería', 'price' => 30],
        ] as $product) {
            Product::create($product);
        }

        $filters = ['buscar' => 'PAP papel', 'categoria' => 'Papelería', 'tipo' => 'PRODUCTO'];
        $this->get(route('portal.catalog', $filters + ['orden' => 'precio-desc']))
            ->assertOk()
            ->assertSeeInOrder(['Menú de navegación', 'Papelería Kopion', 'Buscar productos o servicios', 'Cliente portal · Usuario Final', 'Cerrar sesión'], false)
            ->assertSee('Limpiar filtros')
            ->assertSeeInOrder(['Papel premium', 'Papel económico'])
            ->assertDontSee('Otra categoría')->assertDontSee('Papel interno')->assertDontSee('Papel cortado');
        $this->get(route('portal.catalog', $filters + ['orden' => 'precio-asc']))
            ->assertOk()->assertSeeInOrder(['Papel económico', 'Papel premium']);
    }

    public function test_registration_stores_customer_details_without_granting_a_staff_role(): void
    {
        $this->seed();
        Notification::fake();
        $this->post(route('register'), [
            'name' => 'Cliente Nuevo', 'email' => 'cliente@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
            'role_id' => 1, 'phone' => '5512345678', 'tax_regime' => '612',
        ])->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'cliente@example.com')->firstOrFail();
        $this->assertEquals(4, $user->role_id);
        $this->assertDatabaseMissing('customers', ['email' => $user->email]);
        $this->get(route('portal.home'))->assertRedirect(route('verification.notice'));

        $user->markEmailAsVerified();
        $this->actingAs($user)->get(route('portal.home'))->assertOk()->assertSee('Qué ofrece Kopion');
        $this->assertDatabaseHas('customers', ['email' => $user->email, 'phone' => '5512345678', 'tax_regime' => '612']);
        $this->assertNotNull($user->fresh()->customer_id);
    }

    public function test_staff_registration_requires_the_configured_code_and_valid_role(): void
    {
        $this->seed();
        Notification::fake();
        config(['staff.registration_code' => '2024']);
        $credentials = [
            'name' => 'Personal', 'email' => 'personal@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ];

        $this->get(route('staff.register'))->assertOk()->assertSee('Rol del personal');
        $this->post(route('staff.register'), $credentials + ['staff_code' => 'ABCD', 'role_id' => 2])
            ->assertSessionHasErrors('staff_code');
        $this->post(route('staff.register'), $credentials + ['staff_code' => '1111', 'role_id' => 2])
            ->assertSessionHasErrors('staff_code');
        $this->post(route('staff.register'), $credentials + ['staff_code' => '2024', 'role_id' => 4])
            ->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => $credentials['email']]);
        $this->post(route('staff.register'), $credentials + ['staff_code' => '2024', 'role_id' => 2])
            ->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', ['email' => $credentials['email'], 'role_id' => 2]);
    }

    public function test_customer_catalog_and_cart_are_scoped_and_use_server_prices(): void
    {
        $this->seed();
        $customer = Customer::create(['name' => 'Cliente', 'email' => 'cliente@example.com']);
        $user = User::factory()->create(['email' => $customer->email, 'role_id' => 4]);
        $this->actingAs($user);
        $other = Customer::create(['name' => 'Otro cliente']);
        $otherOrder = $other->orders()->create(['order_number' => 'FOLIO-PRIVADO', 'status' => 'pendiente']);
        $product = Product::create(['name' => 'Pluma azul', 'type' => 'PRODUCTO', 'code' => 'PL-A', 'price' => 25, 'stock' => 10]);
        $supply = Product::create(['name' => 'Insumo privado', 'type' => 'INSUMO', 'code' => 'INS-A', 'price' => 40]);
        MarketingCampaign::create([
            'name' => 'Temporada próxima', 'starts_on' => today()->addDay(), 'ends_on' => today()->addWeek(),
            'audience' => 'all', 'discount_percent' => 15,
        ]);

        $this->get(route('portal.home'))->assertOk()->assertSee('Temporada próxima')->assertDontSee('FOLIO-PRIVADO');
        $this->get(route('portal.catalog'))->assertOk()->assertSee('Pluma azul')->assertDontSee('Insumo privado');
        $this->get(route('orders.index'))->assertForbidden();
        $this->get(route('products.index'))->assertForbidden();
        $this->get(route('customers.index'))->assertForbidden();
        $this->get(route('printers.index'))->assertForbidden();
        $this->get(route('portal.orders.cart', $otherOrder))->assertNotFound();
        $this->put(route('portal.orders.update', $otherOrder), ['quantities' => [$product->id => 1]])->assertNotFound();

        $this->get(route('portal.orders.new', ['product_id' => $product->id]))
            ->assertOk()->assertSee('Usuario Final')->assertSee('Guardar cotización')
            ->assertSee('Buscar Producto o Servicio')->assertDontSee('Cobrar / Finalizar')->assertDontSee('Descuentos sugeridos');
        $this->postJson(route('portal.orders.store'), ['quantities' => []])->assertOk();
        $this->assertDatabaseMissing('orders', ['customer_id' => $customer->id]);
        $this->postJson(route('portal.orders.store'), ['quantities' => [$product->id => 1]])
            ->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        $this->postJson(route('portal.orders.store'), [
            'quantities' => [$product->id => 1], 'payment_method' => 'efectivo',
        ])->assertOk();
        $order = Order::where('customer_id', $customer->id)->firstOrFail();
        $this->get(route('portal.orders.cart', $order))->assertOk()->assertSee('Carrito de cotización')->assertDontSee('Insumo privado');
        $this->putJson(route('portal.orders.update', $order), [
            'quantities' => [$product->id => 2], 'total' => 0.01, 'status' => 'completado', 'payment_method' => 'transferencia',
        ])->assertOk();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pendiente', 'total' => 50, 'payment_method' => 'transferencia']);
        $this->putJson(route('portal.orders.update', $order), [
            'quantities' => [$supply->id => 1], 'payment_method' => 'efectivo',
        ])->assertUnprocessable()->assertJsonValidationErrors('quantities');
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id, 'product_id' => $supply->id]);
        $this->putJson(route('portal.orders.update', $order), ['quantities' => []])->assertOk();
        $this->assertModelMissing($order);
        $this->assertModelExists($otherOrder);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PrinterReading;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrinterReadingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_refill_and_new_reading_update_the_module_and_dashboard(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role_id' => 3]));

        $this->get(route('printers.index'))
            ->assertOk()
            ->assertSee('Lexmark MX711 Esca')
            ->assertSee('Ricoh IM C2510 a color')
            ->assertSee('419348')
            ->assertSee('Nivel de ejemplo');

        $route = route('printers.readings.store', 'esca');

        $this->post($route, [
            'color' => 'negro',
            'level' => 90,
            'recorded_on' => today()->subDays(4)->toDateString(),
            'is_refill' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('printers.index'));

        $this->post($route, [
            'color' => 'negro',
            'level' => 60,
            'recorded_on' => today()->toDateString(),
            'is_refill' => '0',
        ])->assertSessionHasNoErrors()->assertRedirect(route('printers.index'));

        $this->get(route('printers.index'))
            ->assertOk()
            ->assertSee('Historial de recargas')
            ->assertSee('Nivel bajo estimado en 4 días')
            ->assertSee('90 %');

        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Tóner de impresoras en blanco y negro')
            ->assertSee('Niveles de tóner y tinta')
            ->assertSee('height: 60%', false);
    }

    public function test_only_completed_print_sales_count_pages_and_reduce_consumables_once(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role_id' => 1]));

        $order = Order::create(['order_number' => 'IMP-001', 'status' => 'pendiente']);
        $customer = Customer::create(['name' => 'Cliente de impresión']);
        $product = Product::create(['code' => 'IMP', 'name' => 'Impresión tamaño carta', 'type' => 'SERVICIO', 'category' => 'Copias e Impresiones']);
        $route = route('orders.pos.update', $order);
        $before = PrinterReading::levels();

        $this->get(route('orders.pos', $order))->assertOk()->assertSee('Tipo de impresión');
        $this->postJson($route, ['cart' => [['id' => $product->id, 'quantity' => 60, 'price' => 1]], 'status' => 'completado', 'payment_method' => 'efectivo'])
            ->assertUnprocessable()->assertJsonValidationErrors('cart.0.printer');

        $cart = [
            ['id' => $product->id, 'quantity' => 60, 'price' => 1, 'printer' => 'epson', 'print_mode' => 'color'],
            ['id' => $product->id, 'quantity' => 250, 'price' => 1, 'printer' => 'esca', 'print_mode' => 'mono'],
        ];
        $this->postJson($route, ['cart' => $cart, 'status' => 'pendiente'])->assertOk();
        $this->assertEquals(0, PrinterReading::levels()['epson']['pages']);

        $this->postJson($route, ['cart' => $cart, 'status' => 'completado', 'discount_percent' => 101])
            ->assertUnprocessable()->assertJsonValidationErrors('discount_percent');
        $this->postJson($route, [
            'cart' => $cart,
            'status' => 'completado',
            'customer_id' => $customer->id,
            'discount_percent' => 12.5,
            'payment_method' => 'transferencia',
        ])->assertOk();
        $this->postJson($route, ['cart' => $cart, 'status' => 'completado'])->assertForbidden();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'discount_percent' => 12.5, 'discount' => 38.75, 'total' => 271.25, 'payment_method' => 'transferencia']);
        $this->get(route('orders.pos', $order))->assertOk()->assertSee('Transferencia');

        $after = PrinterReading::levels();
        $this->assertEquals(60, $after['epson']['pages']);
        $this->assertEquals(250, $after['esca']['pages']);
        $this->assertEquals($before['esca']['levels']['negro']['reading']->level - 1, $after['esca']['levels']['negro']['reading']->level);
        foreach ($after['epson']['levels'] as $color => $level) {
            $this->assertEquals($before['epson']['levels'][$color]['reading']->level - 1, $level['reading']->level);
        }

        $this->get(route('printers.index'))
            ->assertOk()
            ->assertSee('Cliente de impresión')
            ->assertSee('IMP-001')
            ->assertViewHas('usageRows', fn ($rows) => $rows[6]['values'] === [250, 0, 60, 0]);

        $legacyOrder = Order::create(['order_number' => 'IMP-ANTERIOR', 'status' => 'completado']);
        OrderItem::create([
            'order_id' => $legacyOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 1,
            'subtotal' => 10,
        ]);
        foreach (['doble carta' => 20, 'oficio' => 30, 'sin especificar' => 40] as $size => $quantity) {
            $legacyService = Product::create([
                'code' => 'IMP-'.$quantity,
                'name' => 'Impresión '.$size,
                'type' => 'SERVICIO',
                'category' => 'Impresión',
            ]);
            OrderItem::create([
                'order_id' => $legacyOrder->id,
                'product_id' => $legacyService->id,
                'quantity' => $quantity,
                'unit_price' => 1,
                'subtotal' => $quantity,
            ]);
        }
        Product::create(['code' => 'PAPEL-INT', 'name' => 'Papel interno', 'type' => 'INSUMO', 'stock' => 75]);

        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Ver gráficas siguientes')
            ->assertSee('Insumos internos')
            ->assertSee('Papel interno')
            ->assertSee('410 páginas en total')
            ->assertSee('Por tamaño de papel')
            ->assertSeeInOrder(['Gráficas del dashboard', 'Cotizaciones y prospectos', 'Niveles de tóner y tinta'])
            ->assertViewHas('topProductsList', fn ($items) => $items->isEmpty())
            ->assertViewHas('servicesSold', fn ($services) => !$services->has('Impresiones'))
            ->assertViewHas('printCounts', [
                'Blanco y negro' => 250,
                'A color' => 60,
                'Sin modo registrado' => 100,
            ])
            ->assertViewHas('printSizeCounts', fn ($counts) => $counts['Carta'] === 320
                && $counts['Doble carta'] === 20 && $counts['Oficio'] === 30 && $counts['Sin tamaño registrado'] === 40);
    }

    public function test_a_monochrome_printer_rejects_a_color_reading(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role_id' => 1]));

        $this->post(route('printers.readings.store', 'esca'), [
            'color' => 'magenta',
            'level' => 100,
            'recorded_on' => today()->toDateString(),
            'is_refill' => '1',
        ])->assertSessionHasErrorsIn('esca', ['color']);

        $this->assertDatabaseMissing('printer_readings', ['printer' => 'esca', 'color' => 'magenta']);

        $this->post(route('printers.readings.store', 'ricoh'), [
            'color' => 'magenta',
            'level' => 100,
            'recorded_on' => today()->toDateString(),
            'is_refill' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('printers.index'));

        $this->assertDatabaseHas('printer_readings', ['printer' => 'ricoh', 'color' => 'magenta', 'level' => 100, 'is_refill' => true]);
    }
}

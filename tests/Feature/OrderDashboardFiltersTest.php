<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDashboardFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_chart_shows_seven_local_days_and_can_be_filtered(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role_id' => 1]));
        $this->travelTo(CarbonImmutable::parse('2026-11-23 03:00:00', 'UTC'));

        foreach ([
            ['ANTES-MEDIANOCHE', 45, 'completado', '2026-11-22 05:59:59'],
            ['DESPUES-MEDIANOCHE', 80, 'completado', '2026-11-22 06:00:00'],
            ['PENDIENTE', 200, 'pendiente', '2026-11-22 12:00:00'],
        ] as [$folio, $total, $status, $updatedAt]) {
            $order = new Order(['order_number' => $folio, 'total' => $total, 'status' => $status]);
            $order->updated_at = $updatedAt;
            $order->save();
        }

        $this->get(route('orders.index'))
            ->assertOk()->assertSee('Filtrar ventas')
            ->assertViewHas('salesRows', fn ($rows) => count($rows) === 7
                && $rows[0] === ['label' => '16/11', 'values' => [0.0]]
                && $rows[5] === ['label' => '21/11', 'values' => [45.0]]
                && $rows[6] === ['label' => '22/11', 'values' => [80.0]])
            ->assertViewHas('todaySales', 80);

        $this->get(route('orders.index', [
            'ventas_desde' => '2026-11-21', 'ventas_hasta' => '2026-11-21', 'estado' => 'pendiente',
        ]))->assertOk()->assertSee('Últimos 7 días')
            ->assertViewHas('salesRows', [['label' => '21/11', 'values' => [45.0]]]);
    }

    public function test_orders_can_be_found_by_customer_filtered_and_sorted_by_price(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role_id' => 1]));
        $customer = Customer::create(['name' => 'Ana Pérez', 'company_name' => 'Papel Azul']);

        foreach ([
            ['COT-CARA', 180, 'pendiente', 'ALTO', now()],
            ['COT-BARATA', 20, 'pendiente', 'ALTO', now()],
            ['COT-COMPLETA', 14, 'completado', 'ALTO', now()],
            ['COT-ANTERIOR', 2, 'pendiente', 'ALTO', now()->subDays(10)],
            ['COT-MEDIO', 10, 'pendiente', 'MEDIO', now()],
        ] as [$folio, $price, $status, $interest, $createdAt]) {
            $order = $customer->orders()->make([
                'order_number' => $folio, 'total' => $price, 'status' => $status, 'interest_level' => $interest,
            ]);
            $order->created_at = $createdAt;
            $order->save();
        }

        $filters = [
            'search' => 'Ana Azul', 'estado' => 'pendiente', 'interes' => 'ALTO',
            'desde' => today()->toDateString(), 'hasta' => today()->toDateString(), 'orden' => 'precio',
        ];
        $this->get(route('orders.index', $filters + ['direccion' => 'asc']))
            ->assertOk()->assertSee('Ver solo pendientes')
            ->assertSee('aria-sort="ascending"', false)
            ->assertSeeInOrder(['COT-BARATA', 'COT-CARA'])
            ->assertDontSee('COT-COMPLETA')->assertDontSee('COT-ANTERIOR')->assertDontSee('COT-MEDIO');

        $this->get(route('orders.index', $filters + ['direccion' => 'desc']))
            ->assertOk()->assertSeeInOrder(['COT-CARA', 'COT-BARATA']);

        $this->get(route('orders.index', ['hasta' => today()->toDateString(), 'estado' => 'completado']))
            ->assertOk()->assertSee('COT-COMPLETA')->assertDontSee('COT-BARATA');
    }
}

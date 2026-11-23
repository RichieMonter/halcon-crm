<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDashboardFiltersTest extends TestCase
{
    use RefreshDatabase;

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

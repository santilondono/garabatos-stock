<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;

/**
 * Reportes de ganancia (ReportController@sales_summary y @cards).
 *
 * Datos (calculados a mano):
 *   P1: purchase_price 2.000, quantity 10 unidades/paquete
 *   P2: purchase_price 1.500, quantity 6 unidades/paquete
 *
 *   Venta A 2001-03-10: P1 x2 @5.000, P2 x3 @3.000
 *     bruta = 2*10*5 + 3*6*3           = 100 + 54 = 154
 *     neta  = 2*10*(5-2) + 3*6*(3-1.5) = 60 + 27  = 87
 *   Venta B 2001-03-15: P1 x1 @4.500
 *     bruta = 1*10*4.5 = 45 ; neta = 1*10*(4.5-2) = 25
 *   Venta C 2001-03-12 ANULADA: P1 x5 @5.000  -> excluida
 *   Venta D 2001-05-01 (fuera de rango): P2 x1 @3.000 -> excluida
 *
 *   Rango 2001-03-01 .. 2001-03-31 => bruta 199, neta 112
 */
class ProfitReportTest extends FeatureTestCase
{
    private const START = '2001-03-01';
    private const END = '2001-03-31';

    private int $p1;
    private int $p2;
    private int $saleA;
    private int $saleB;
    private float $stockValueBaseline;
    private int $userId;
    private int $clientId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockValueBaseline = (float) DB::table('products')
            ->selectRaw('SUM(stock * quantity * purchase_price) as v')->value('v');

        $user = $this->createUser(self::ROLE_3, ['name' => 'Vendedor Reporte']);
        $clientId = $this->createClient(['client_name' => 'Cliente Reporte']);
        $this->userId = $user->id;
        $this->clientId = $clientId;

        $this->p1 = $this->createProduct([
            'product_reference' => 'REF-P1', 'purchase_price' => 2, 'quantity' => 10, 'stock' => 7,
        ]);
        $this->p2 = $this->createProduct([
            'product_reference' => 'REF-P2', 'purchase_price' => 1.5, 'quantity' => 6, 'stock' => 4,
        ]);

        $this->saleA = $this->createSale($user->id, $clientId, '2001-03-10 10:00:00', [
            ['product_id' => $this->p1, 'quantity_sold' => 2, 'sale_price' => 5],
            ['product_id' => $this->p2, 'quantity_sold' => 3, 'sale_price' => 3],
        ]);
        $this->saleB = $this->createSale($user->id, $clientId, '2001-03-15 10:00:00', [
            ['product_id' => $this->p1, 'quantity_sold' => 1, 'sale_price' => 4.5],
        ]);
        $this->createSale($user->id, $clientId, '2001-03-12 10:00:00', [
            ['product_id' => $this->p1, 'quantity_sold' => 5, 'sale_price' => 5],
        ], cancelled: true);
        $this->createSale($user->id, $clientId, '2001-05-01 10:00:00', [
            ['product_id' => $this->p2, 'quantity_sold' => 1, 'sale_price' => 3],
        ]);

        $this->actingAs($user);
    }

    public function test_sales_summary_totals(): void
    {
        $response = $this->get('/stock/print-sales-summary/'.self::START.'/'.self::END)->assertOk();

        $total = $response->viewData('total_summary');
        $this->assertEqualsWithDelta(199, (float) $total->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(112, (float) $total->net_profit, 0.0001);

        // Una fila por venta no anulada, ordenadas por sale_id descendente.
        $rows = $response->viewData('sales_summary');
        $this->assertSame([$this->saleB, $this->saleA], $rows->pluck('sale_id')->map(fn ($v) => (int) $v)->all());
        $this->assertEqualsWithDelta(45, (float) $rows[0]->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(25, (float) $rows[0]->net_profit, 0.0001);
        $this->assertEqualsWithDelta(154, (float) $rows[1]->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(87, (float) $rows[1]->net_profit, 0.0001);
        $this->assertSame('Vendedor Reporte', $rows[0]->user_name);
        $this->assertSame('Cliente Reporte', $rows[0]->client_name);
    }

    public function test_sales_summary_end_date_includes_sales_on_that_day(): void
    {
        // La venta B (2001-03-15 10:00) cae en el día final y debe contarse.
        $response = $this->get('/stock/print-sales-summary/2001-03-01/2001-03-15');

        $total = $response->viewData('total_summary');
        $this->assertEqualsWithDelta(199, (float) $total->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(112, (float) $total->net_profit, 0.0001);
        $this->assertSame(
            [$this->saleB, $this->saleA],
            $response->viewData('sales_summary')->pluck('sale_id')->map(fn ($v) => (int) $v)->all()
        );
        $this->assertSame('2001-03-15', $response->viewData('end_date'));
    }

    /**
     * Ventas en los bordes del rango 2001-03-10 .. 2001-03-15 (P1 x1 @5 => bruta 50, neta 30 cada una).
     *
     * @return array{first_second:int, last_second:int, next_day:int, day_before:int}
     */
    private function createBoundarySales(): array
    {
        $line = [['product_id' => $this->p1, 'quantity_sold' => 1, 'sale_price' => 5]];

        return [
            'first_second' => $this->createSale($this->userId, $this->clientId, '2001-03-10 00:00:00', $line),
            'last_second' => $this->createSale($this->userId, $this->clientId, '2001-03-15 23:59:59', $line),
            'next_day' => $this->createSale($this->userId, $this->clientId, '2001-03-16 00:00:00', $line),
            'day_before' => $this->createSale($this->userId, $this->clientId, '2001-03-09 23:59:59', $line),
        ];
    }

    public function test_sales_summary_range_boundaries(): void
    {
        $boundary = $this->createBoundarySales();

        $response = $this->get('/stock/print-sales-summary/2001-03-10/2001-03-15');

        $ids = $response->viewData('sales_summary')->pluck('sale_id')->map(fn ($v) => (int) $v)->all();
        $this->assertContains($boundary['first_second'], $ids);
        $this->assertContains($boundary['last_second'], $ids);
        $this->assertNotContains($boundary['next_day'], $ids);
        $this->assertNotContains($boundary['day_before'], $ids);
        $this->assertCount(4, $ids);

        // A (154/87) + B (45/25) + dos ventas de borde (50/30 cada una)
        $total = $response->viewData('total_summary');
        $this->assertEqualsWithDelta(299, (float) $total->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(172, (float) $total->net_profit, 0.0001);
    }

    public function test_sales_summary_single_day_range(): void
    {
        $total = $this->get('/stock/print-sales-summary/2001-03-15/2001-03-15')
            ->viewData('total_summary');

        $this->assertEqualsWithDelta(45, (float) $total->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(25, (float) $total->net_profit, 0.0001);
    }

    public function test_sales_summary_without_end_date_includes_todays_sales(): void
    {
        // Sin fecha final se sigue usando hoy como fin; ahora incluye las ventas de hoy.
        $todaySale = $this->createSale($this->userId, $this->clientId, now()->format('Y-m-d H:i:s'), [
            ['product_id' => $this->p1, 'quantity_sold' => 1, 'sale_price' => 5],
        ]);

        $response = $this->get('/stock/print-sales-summary/2001-03-01');

        $this->assertSame(now()->format('Y-m-d'), $response->viewData('end_date'));
        $this->assertContains(
            $todaySale,
            $response->viewData('sales_summary')->pluck('sale_id')->map(fn ($v) => (int) $v)->all()
        );
    }

    public function test_cards_report_end_date_includes_whole_day_and_excludes_next_day(): void
    {
        $this->createBoundarySales();

        $response = $this->get('/stock/cards?start_date=2001-03-10&end_date=2001-03-15')->assertOk();

        $profits = $response->viewData('profits');
        $this->assertEqualsWithDelta(299, (float) $profits->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(172, (float) $profits->net_profit, 0.0001);

        // P1: 2 (A) + 1 (B) + 2 de borde = 5 ; P2: 3 (A)
        $top = $response->viewData('top_selling_products')->keyBy('product_reference');
        $this->assertSame(5, (int) $top['REF-P1']->quantity_sold);
        $this->assertSame(3, (int) $top['REF-P2']->quantity_sold);
    }

    public function test_cards_report_without_end_date_includes_todays_sales(): void
    {
        $this->createSale($this->userId, $this->clientId, now()->format('Y-m-d H:i:s'), [
            ['product_id' => $this->p2, 'quantity_sold' => 2, 'sale_price' => 3],
        ]);

        $response = $this->get('/stock/cards?start_date='.now()->format('Y-m-d'))->assertOk();

        // Solo la venta de hoy: P2 x2 @3 => bruta 2*6*3 = 36 ; neta 2*6*(3-1.5) = 18
        $this->assertSame(now()->format('Y-m-d'), $response->viewData('end_date'));
        $this->assertEqualsWithDelta(36, (float) $response->viewData('profits')->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(18, (float) $response->viewData('profits')->net_profit, 0.0001);
    }

    public function test_net_profit_uses_current_purchase_price(): void
    {
        // Comportamiento actual: la ganancia neta usa products.purchase_price actual,
        // no el precio de compra vigente al momento de la venta.
        DB::table('products')->where('product_id', $this->p1)->update(['purchase_price' => 3]);

        $total = $this->get('/stock/print-sales-summary/'.self::START.'/'.self::END)
            ->viewData('total_summary');

        // neta = 2*10*(5-3) + 3*6*(3-1.5) + 1*10*(4.5-3) = 40 + 27 + 15 = 82
        $this->assertEqualsWithDelta(199, (float) $total->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(82, (float) $total->net_profit, 0.0001);
    }

    public function test_cards_report_totals(): void
    {
        $response = $this->get('/stock/cards?start_date='.self::START.'&end_date='.self::END)->assertOk();

        $profits = $response->viewData('profits');
        $this->assertEqualsWithDelta(199, (float) $profits->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(112, (float) $profits->net_profit, 0.0001);

        // Top vendidos: P1 = 2+1 = 3 paquetes (bruta 145, neta 85); P2 = 3 (bruta 54, neta 27).
        $top = $response->viewData('top_selling_products')->keyBy('product_reference');
        $this->assertCount(2, $top);
        $this->assertSame(3, (int) $top['REF-P1']->quantity_sold);
        $this->assertEqualsWithDelta(145, (float) $top['REF-P1']->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(85, (float) $top['REF-P1']->net_profit, 0.0001);
        $this->assertSame(3, (int) $top['REF-P2']->quantity_sold);
        $this->assertEqualsWithDelta(54, (float) $top['REF-P2']->gross_profit, 0.0001);
        $this->assertEqualsWithDelta(27, (float) $top['REF-P2']->net_profit, 0.0001);

        // Dinero en bodega (todos los productos, sin filtrar por fecha ni activo):
        // P1 = 7*10*2 = 140 ; P2 = 4*6*1.5 = 36 => +176 sobre lo que ya hubiera en la base.
        $this->assertEqualsWithDelta(
            $this->stockValueBaseline + 176,
            (float) $response->viewData('stock_value')->stock_value,
            0.0001
        );
    }
}

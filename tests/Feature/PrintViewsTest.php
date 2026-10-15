<?php

namespace Tests\Feature;

/**
 * Vistas de impresión: print-sale, print-stock-summary, print-sales-summary.
 */
class PrintViewsTest extends FeatureTestCase
{
    public function test_print_sale_renders(): void
    {
        $user = $this->createUser(self::ROLE_2, ['name' => 'Vendedor Print']);
        $clientId = $this->createClient(['client_name' => 'Cliente Print']);
        $p1 = $this->createProduct(['product_reference' => 'REF-PRINT-1', 'quantity' => 10]);
        $saleId = $this->createSale($user->id, $clientId, '2025-03-10 10:00:00', [
            ['product_id' => $p1, 'quantity_sold' => 2, 'sale_price' => 5],
        ]);

        $response = $this->actingAs($user)->get(route('print-sale', $saleId));

        $response->assertOk()
            ->assertViewIs('stock.sale.print')
            ->assertSee('Cliente Print')
            ->assertSee('Vendedor Print')
            ->assertSee('REF-PRINT-1');
        $this->assertEquals(100, (float) $response->viewData('sale')->total);
    }

    public function test_print_stock_summary_renders_only_active_products(): void
    {
        $this->createProduct(['product_reference' => 'REF-ACTIVE', 'active' => 1]);
        $this->createProduct(['product_reference' => 'REF-INACTIVE', 'active' => 0]);

        $this->actingAs($this->createUser(self::ROLE_2))
            ->get(route('print-stock-summary'))
            ->assertOk()
            ->assertViewIs('stock.report.stock_summary')
            ->assertSee('REF-ACTIVE')
            ->assertDontSee('REF-INACTIVE');
    }

    public function test_print_sales_summary_renders_with_and_without_dates(): void
    {
        $user = $this->createUser(self::ROLE_2);
        $clientId = $this->createClient(['client_name' => 'Cliente Resumen']);
        $p1 = $this->createProduct();
        $this->createSale($user->id, $clientId, '2001-06-10 10:00:00', [
            ['product_id' => $p1, 'quantity_sold' => 1, 'sale_price' => 5],
        ]);

        $this->actingAs($user);

        $this->get(route('print-sales-summary', ['start_date' => '2001-06-01', 'end_date' => '2001-06-30']))
            ->assertOk()
            ->assertViewIs('stock.report.sales_summary')
            ->assertSee('Cliente Resumen')
            ->assertSee('2001-06-01 - 2001-06-30');

        // Sin fechas: por defecto 2024-01-01 .. hoy.
        $response = $this->get(route('print-sales-summary'))->assertOk();
        $this->assertSame('2024-01-01', $response->viewData('start_date'));
        $this->assertSame(now()->format('Y-m-d'), $response->viewData('end_date'));
    }
}

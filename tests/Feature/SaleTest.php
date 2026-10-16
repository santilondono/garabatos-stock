<?php

namespace Tests\Feature;

use App\Mail\SaleNotificatiosMailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Crear y cancelar ventas (SaleController@store / @destroy).
 */
class SaleTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_store_creates_sale_header_details_and_decrements_stock(): void
    {
        $user = $this->createUser(self::ROLE_2);
        $clientId = $this->createClient();
        $p1 = $this->createProduct(['purchase_price' => 2, 'quantity' => 10, 'stock' => 100]);
        $p2 = $this->createProduct(['purchase_price' => 1.5, 'quantity' => 6, 'stock' => 50]);

        $response = $this->actingAs($user)->post('/stock/sales', [
            'client_id' => $clientId,
            'product_id' => [$p1, $p2],
            'quantity_sold' => [2, 3],
            'sale_price' => [5, 3],
        ]);

        $sale = DB::table('sales')->where('client_id', $clientId)->first();
        $this->assertNotNull($sale);
        $response->assertRedirect(route('sales.show', $sale->sale_id));

        $this->assertSame($user->id, (int) $sale->user_id);
        $this->assertSame(0, (int) $sale->is_cancelled);

        $details = DB::table('sales_detail')->where('sale_id', $sale->sale_id)->orderBy('product_id')->get();
        $this->assertCount(2, $details);
        $this->assertEquals([2, 3], $details->pluck('quantity_sold')->map(fn ($v) => (int) $v)->all());
        $this->assertEquals([5.0, 3.0], $details->pluck('sale_price')->map(fn ($v) => (float) $v)->all());

        // stock -= cantidad vendida; gross_revenue = stock * purchase_price * quantity
        $this->assertSame(98, (int) $this->product($p1)->stock);
        $this->assertEquals(98 * 2 * 10, (float) $this->product($p1)->gross_revenue);
        $this->assertSame(47, (int) $this->product($p2)->stock);
        $this->assertEquals(47 * 1.5 * 6, (float) $this->product($p2)->gross_revenue);

        Mail::assertSent(SaleNotificatiosMailable::class, function ($mail) {
            return $mail->hasTo('flyon-importexport@outlook.es') && $mail->hasTo('2997221689@qq.com');
        });
    }

    public function test_store_does_not_validate_available_stock(): void
    {
        // Se puede vender más de lo que hay: el stock queda negativo y se avisa
        // con una advertencia que lista solo los productos afectados.
        $clientId = $this->createClient();
        $p1 = $this->createProduct(['product_reference' => 'REF-NEG-1', 'stock' => 1]);
        $p2 = $this->createProduct(['product_reference' => 'REF-OK-1', 'stock' => 10]);

        $response = $this->actingAs($this->createUser(self::ROLE_1))->post('/stock/sales', [
            'client_id' => $clientId,
            'product_id' => [$p1, $p2],
            'quantity_sold' => [3, 4],
            'sale_price' => [5, 5],
        ]);

        $sale = DB::table('sales')->where('client_id', $clientId)->first();
        $this->assertNotNull($sale);
        $this->assertSame(2, DB::table('sales_detail')->where('sale_id', $sale->sale_id)->count());
        $response->assertRedirect(route('sales.show', $sale->sale_id));

        $this->assertSame(-2, (int) $this->product($p1)->stock);
        $this->assertSame(6, (int) $this->product($p2)->stock);

        $response->assertSessionHas('negative_stock_warning', [
            ['product_reference' => 'REF-NEG-1', 'stock_before' => 1, 'stock_after' => -2],
        ]);

        $this->get(route('sales.show', $sale->sale_id))
            ->assertOk()
            ->assertSee('alert-warning', false)
            ->assertSee('REF-NEG-1: stock before sale 1, stock now -2');
    }

    public function test_store_with_same_product_in_several_lines_reports_stock_before_whole_sale(): void
    {
        $clientId = $this->createClient();
        $p1 = $this->createProduct(['product_reference' => 'REF-NEG-2', 'stock' => 3]);

        $this->actingAs($this->createUser(self::ROLE_1))->post('/stock/sales', [
            'client_id' => $clientId,
            'product_id' => [$p1, $p1],
            'quantity_sold' => [2, 2],
            'sale_price' => [5, 5],
        ])->assertSessionHas('negative_stock_warning', [
            ['product_reference' => 'REF-NEG-2', 'stock_before' => 3, 'stock_after' => -1],
        ]);

        $this->assertSame(-1, (int) $this->product($p1)->stock);
    }

    public function test_store_with_enough_stock_has_no_warning(): void
    {
        $clientId = $this->createClient();
        $p1 = $this->createProduct(['stock' => 3]);

        $response = $this->actingAs($this->createUser(self::ROLE_1))->post('/stock/sales', [
            'client_id' => $clientId,
            'product_id' => [$p1],
            'quantity_sold' => [3],
            'sale_price' => [5],
        ]);

        $this->assertSame(0, (int) $this->product($p1)->stock);
        $response->assertSessionMissing('negative_stock_warning');

        $sale = DB::table('sales')->where('client_id', $clientId)->first();
        $this->get(route('sales.show', $sale->sale_id))
            ->assertOk()
            ->assertDontSee('alert-warning', false);
    }

    public function test_create_form_includes_hidden_stock_warning(): void
    {
        $this->createProduct(['product_reference' => 'REF-FORM-1', 'stock' => 4]);

        $this->actingAs($this->createUser(self::ROLE_1))
            ->get('/stock/sales/create')
            ->assertOk()
            ->assertSee('id="stock_warning" class="alert alert-warning"', false)
            ->assertSee('style="display: none;"', false);
    }

    public function test_store_requires_client_id(): void
    {
        $this->actingAs($this->createUser(self::ROLE_1))
            ->post('/stock/sales', ['product_id' => [], 'quantity_sold' => [], 'sale_price' => []])
            ->assertSessionHasErrors('client_id');
    }

    public function test_show_displays_sale_total(): void
    {
        $user = $this->createUser(self::ROLE_1);
        $clientId = $this->createClient(['client_name' => 'Cliente Show']);
        $p1 = $this->createProduct(['quantity' => 10]);
        $saleId = $this->createSale($user->id, $clientId, '2025-03-10 10:00:00', [
            ['product_id' => $p1, 'quantity_sold' => 2, 'sale_price' => 5],
        ]);

        $response = $this->actingAs($user)->get(route('sales.show', $saleId));

        $response->assertOk()->assertSee('Cliente Show');
        // total = quantity_sold * sale_price * products.quantity
        $this->assertEquals(100, (float) $response->viewData('sale')->total);
    }

    public function test_destroy_cancels_sale_and_restores_stock(): void
    {
        $user = $this->createUser(self::ROLE_2);
        $clientId = $this->createClient();
        $p1 = $this->createProduct(['purchase_price' => 2, 'quantity' => 10, 'stock' => 98]);
        $saleId = $this->createSale($user->id, $clientId, '2025-03-10 10:00:00', [
            ['product_id' => $p1, 'quantity_sold' => 2, 'sale_price' => 5],
        ]);

        $this->actingAs($user)
            ->delete("/stock/sales/{$saleId}")
            ->assertRedirect('/stock/sales');

        $this->assertSame(1, (int) DB::table('sales')->where('sale_id', $saleId)->value('is_cancelled'));
        // Los detalles no se borran.
        $this->assertSame(1, DB::table('sales_detail')->where('sale_id', $saleId)->count());
        $this->assertSame(100, (int) $this->product($p1)->stock);
        $this->assertEquals(100 * 2 * 10, (float) $this->product($p1)->gross_revenue);
    }

    public function test_destroy_on_already_cancelled_sale_does_not_change_stock(): void
    {
        $user = $this->createUser(self::ROLE_1);
        $clientId = $this->createClient();
        $p1 = $this->createProduct(['stock' => 98]);
        $saleId = $this->createSale($user->id, $clientId, '2025-03-10 10:00:00', [
            ['product_id' => $p1, 'quantity_sold' => 2, 'sale_price' => 5],
        ]);

        $this->actingAs($user);
        $this->delete("/stock/sales/{$saleId}");
        $productAfterFirst = $this->product($p1);

        $this->delete("/stock/sales/{$saleId}")->assertRedirect('/stock/sales');

        $productAfterSecond = $this->product($p1);
        $this->assertSame(100, (int) $productAfterSecond->stock);
        $this->assertEquals($productAfterFirst, $productAfterSecond);
        $this->assertSame(1, (int) DB::table('sales')->where('sale_id', $saleId)->value('is_cancelled'));
    }

    public function test_cancelled_sale_is_excluded_from_sales_summary_report(): void
    {
        $user = $this->createUser(self::ROLE_3);
        $clientId = $this->createClient();
        $p1 = $this->createProduct(['purchase_price' => 2, 'quantity' => 10]);
        $saleId = $this->createSale($user->id, $clientId, '2001-06-10 10:00:00', [
            ['product_id' => $p1, 'quantity_sold' => 2, 'sale_price' => 5],
        ]);

        $this->actingAs($user);

        $before = $this->get('/stock/print-sales-summary/2001-06-01/2001-06-30');
        $this->assertCount(1, $before->viewData('sales_summary'));
        $this->assertEquals(100, (float) $before->viewData('total_summary')->gross_profit);

        $this->delete("/stock/sales/{$saleId}");

        $after = $this->get('/stock/print-sales-summary/2001-06-01/2001-06-30');
        $this->assertCount(0, $after->viewData('sales_summary'));
        $this->assertNull($after->viewData('total_summary')->gross_profit);

        $cards = $this->get('/stock/cards?start_date=2001-06-01&end_date=2001-06-30');
        $this->assertCount(0, $cards->viewData('top_selling_products'));
        $this->assertNull($cards->viewData('profits')->gross_profit);
    }
}

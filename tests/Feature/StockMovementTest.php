<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;

/**
 * Entradas (EntryController) y salidas (OutputController) de stock.
 */
class StockMovementTest extends FeatureTestCase
{
    public function test_entry_increments_stock_and_updates_purchase_price(): void
    {
        $user = $this->createUser(self::ROLE_1);
        $p1 = $this->createProduct(['purchase_price' => 2, 'quantity' => 10, 'stock' => 100, 'comming' => 0]);

        $this->actingAs($user)->post('/stock/entries', [
            'product_id' => [$p1],
            'quantity_entered' => [20],
            'purchase_price' => [2.5],
        ])->assertRedirect('/stock/entries');

        $entry = DB::table('entries')->where('user_id', $user->id)->first();
        $this->assertNotNull($entry);
        $this->assertSame(0, (int) $entry->is_comming);

        $detail = DB::table('entries_detail')->where('entry_id', $entry->entry_id)->first();
        $this->assertSame($p1, (int) $detail->product_id);
        $this->assertSame(20, (int) $detail->quantity_entered);
        $this->assertEquals(2.5, (float) $detail->purchase_price);

        $product = $this->product($p1);
        $this->assertSame(120, (int) $product->stock);
        $this->assertEquals(2.5, (float) $product->purchase_price);
        $this->assertEquals(120 * 2.5 * 10, (float) $product->gross_revenue);
        $this->assertSame(0, (int) $product->comming);
    }

    public function test_entry_marked_as_comming_also_increments_comming(): void
    {
        $user = $this->createUser(self::ROLE_3);
        $p1 = $this->createProduct(['stock' => 100, 'comming' => 5]);

        $this->actingAs($user)->post('/stock/entries', [
            'is_comming' => 'on',
            'product_id' => [$p1],
            'quantity_entered' => [20],
            'purchase_price' => [2],
        ]);

        $this->assertSame(1, (int) DB::table('entries')->where('user_id', $user->id)->value('is_comming'));
        // Comportamiento actual: una entrada "en camino" suma al stock Y a comming.
        $this->assertSame(120, (int) $this->product($p1)->stock);
        $this->assertSame(25, (int) $this->product($p1)->comming);
    }

    public function test_entry_destroy_clears_comming_without_touching_stock(): void
    {
        $user = $this->createUser(self::ROLE_4);
        $p1 = $this->createProduct(['stock' => 120, 'comming' => 25]);
        $entryId = DB::table('entries')->insertGetId([
            'entry_date' => '2025-03-10 10:00:00', 'user_id' => $user->id, 'is_comming' => 1,
        ]);
        DB::table('entries_detail')->insert([
            'entry_id' => $entryId, 'product_id' => $p1, 'quantity_entered' => 20, 'purchase_price' => 2,
        ]);

        $this->actingAs($user)->delete("/stock/entries/{$entryId}")->assertRedirect('/stock/entries');

        $this->assertSame(0, (int) DB::table('entries')->where('entry_id', $entryId)->value('is_comming'));
        $this->assertSame(5, (int) $this->product($p1)->comming);
        $this->assertSame(120, (int) $this->product($p1)->stock);
    }

    public function test_output_decrements_stock(): void
    {
        $user = $this->createUser(self::ROLE_1);
        $p1 = $this->createProduct(['purchase_price' => 2, 'quantity' => 10, 'stock' => 100]);
        $p2 = $this->createProduct(['purchase_price' => 1.5, 'quantity' => 6, 'stock' => 50]);

        $this->actingAs($user)->post('/stock/outputs', [
            'reason' => 'Producto dañado',
            'product_id' => [$p1, $p2],
            'quantity_taken_out' => [7, 50],
        ])->assertRedirect('/stock/outputs');

        $output = DB::table('outputs')->where('user_id', $user->id)->first();
        $this->assertSame('Producto dañado', $output->reason);
        $this->assertSame(2, DB::table('outputs_detail')->where('output_id', $output->output_id)->count());

        $this->assertSame(93, (int) $this->product($p1)->stock);
        $this->assertEquals(93 * 2 * 10, (float) $this->product($p1)->gross_revenue);
        $this->assertSame(0, (int) $this->product($p2)->stock);
        $this->assertEquals(0, (float) $this->product($p2)->gross_revenue);
    }

    public function test_output_show_lists_details(): void
    {
        $user = $this->createUser(self::ROLE_1);
        $p1 = $this->createProduct(['product_reference' => 'REF-OUT-1']);
        $outputId = DB::table('outputs')->insertGetId([
            'output_date' => '2025-03-10 10:00:00', 'user_id' => $user->id, 'reason' => 'x',
        ]);
        DB::table('outputs_detail')->insert([
            'output_id' => $outputId, 'product_id' => $p1, 'quantity_taken_out' => 3,
        ]);

        $this->actingAs($user)->get("/stock/outputs/{$outputId}")
            ->assertOk()
            ->assertSee('REF-OUT-1');
    }
}

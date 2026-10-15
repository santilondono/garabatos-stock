<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Base para los tests de Feature.
 *
 * Usa la base MySQL `garabatos_stock_test` (esquema real de producción) y
 * DatabaseTransactions para hacer rollback de cada test. Los datos se crean
 * con DB::table()->insert() usando los nombres de columna reales de la base,
 * porque los modelos Role y SaleDetail no coinciden con ella
 * (roles.id / roles.role_description; sales_detail.sale_datail_id).
 */
abstract class FeatureTestCase extends TestCase
{
    use DatabaseTransactions;

    // Roles que usa el middleware `role` en routes/web.php.
    protected const ROLE_1 = 1;   // acceso a users/outputs/entries/products/roles
    protected const ROLE_2 = 2;   // sin acceso a rutas protegidas por rol
    protected const ROLE_3 = 3;   // acceso a todo + stock/cards
    protected const ROLE_4 = 4;   // acceso a todo + stock/cards

    protected function setUp(): void
    {
        parent::setUp();

        // Salvaguarda: nunca correr contra otra base que no sea la de pruebas.
        $this->assertSame(
            'garabatos_stock_test',
            DB::connection()->getDatabaseName(),
            'Los tests deben correr contra garabatos_stock_test (ver phpunit.xml).'
        );

        $this->ensureRoles();
    }

    protected function ensureRoles(): void
    {
        foreach ([1 => 'Role 1', 2 => 'Role 2', 3 => 'Role 3', 4 => 'Role 4'] as $id => $description) {
            if (! DB::table('roles')->where('id', $id)->exists()) {
                DB::table('roles')->insert(['id' => $id, 'role_description' => $description]);
            }
        }
    }

    protected function createUser(int $roleId, array $overrides = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'name' => 'Test User '.uniqid(),
            'email' => uniqid('test_', true).'@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return User::findOrFail($id);
    }

    protected function createClient(array $overrides = []): int
    {
        return DB::table('clients')->insertGetId(array_merge([
            'client_name' => 'Cliente Test',
            'shipping_mark' => 'MARK-TEST',
            'country' => 'Colombia',
        ], $overrides));
    }

    protected function createProduct(array $overrides = []): int
    {
        return DB::table('products')->insertGetId(array_merge([
            'product_reference' => 'REF-'.substr(uniqid(), -8),
            'image' => null,
            'list_description' => 'List desc',
            'product_description' => 'Producto test',
            'purchase_price' => 2.000,
            'sale_price' => 5.000,
            'weight' => 1.000,
            'length' => 10.00,
            'width' => 10.00,
            'height' => 10.00,
            'cubic_meter' => 0.0010000000,
            'quantity' => 10,
            'stock' => 100,
            'gross_revenue' => 0,
            'active' => 1,
            'comming' => 0,
        ], $overrides));
    }

    /**
     * Crea una venta con sus detalles directamente en la base.
     *
     * @param  array<int, array{product_id:int, quantity_sold:int, sale_price:float}>  $details
     */
    protected function createSale(int $userId, int $clientId, string $saleDate, array $details, bool $cancelled = false): int
    {
        $saleId = DB::table('sales')->insertGetId([
            'sale_date' => $saleDate,
            'user_id' => $userId,
            'client_id' => $clientId,
            'is_cancelled' => $cancelled ? 1 : 0,
        ]);

        foreach ($details as $detail) {
            DB::table('sales_detail')->insert([
                'sale_id' => $saleId,
                'product_id' => $detail['product_id'],
                'quantity_sold' => $detail['quantity_sold'],
                'sale_price' => $detail['sale_price'],
            ]);
        }

        return $saleId;
    }

    protected function product(int $productId): object
    {
        return DB::table('products')->where('product_id', $productId)->first();
    }
}

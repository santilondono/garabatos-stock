<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Autenticación y permisos por rol (middleware `role` = CheckUserRole).
 */
class AuthAndRolesTest extends FeatureTestCase
{
    public static function roleProtectedRoutes(): array
    {
        return [
            'users' => ['/security/users'],
            'outputs' => ['/stock/outputs'],
            'entries' => ['/stock/entries'],
            'products' => ['/stock/products'],
            'roles' => ['/stock/roles'],
            'reportes (cards)' => ['/stock/cards'],
        ];
    }

    public static function authOnlyRoutes(): array
    {
        return [
            'home' => ['/home'],
            'stock-now' => ['/stock-now'],
            'sales' => ['/stock/sales'],
            'clients' => ['/stock/clients'],
            'print-stock-summary' => ['/stock/print-stock-summary'],
            'print-sales-summary' => ['/stock/print-sales-summary'],
        ];
    }

    public function test_login_page_is_public(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_user_can_log_in_and_is_redirected_home(): void
    {
        $user = $this->createUser(self::ROLE_1);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('authOnlyRoutes')]
    public function test_guest_is_redirected_to_login_on_auth_routes(string $uri): void
    {
        $this->get($uri)->assertRedirect('/login');
    }

    public static function roleProtectedRoutesWithAuthMiddleware(): array
    {
        return array_diff_key(self::roleProtectedRoutes(), ['users' => true]);
    }

    #[DataProvider('roleProtectedRoutesWithAuthMiddleware')]
    public function test_guest_on_role_protected_routes_is_redirected_to_login(string $uri): void
    {
        // `auth` (declarado en el constructor del controlador) está en la lista de
        // prioridad de middlewares, así que corre antes que `role` y redirige.
        $this->get($uri)->assertRedirect('/login');
    }

    public function test_guest_on_users_route_is_forbidden(): void
    {
        // Comportamiento actual: UserController no declara middleware `auth`,
        // así que solo actúa `role` y un invitado recibe 403 en vez de redirección.
        $this->get('/security/users')->assertForbidden();
    }

    public function test_guest_on_print_sale_is_redirected_to_login(): void
    {
        $this->get('/print-sale/1')->assertRedirect('/login');
    }

    #[DataProvider('roleProtectedRoutes')]
    public function test_role_without_access_is_forbidden(string $uri): void
    {
        $this->actingAs($this->createUser(self::ROLE_2))
            ->get($uri)
            ->assertForbidden();
    }

    public function test_role_1_cannot_access_cards_report(): void
    {
        $this->actingAs($this->createUser(self::ROLE_1))
            ->get('/stock/cards')
            ->assertForbidden();
    }

    public function test_role_without_access_is_forbidden_on_write_actions(): void
    {
        $this->actingAs($this->createUser(self::ROLE_2));

        $this->post('/stock/entries', [])->assertForbidden();
        $this->post('/stock/outputs', [])->assertForbidden();
        $this->post('/stock/products', [])->assertForbidden();
        $this->delete('/stock/products/1')->assertForbidden();
    }

    public static function allowedRoles(): array
    {
        return ['rol 1' => [self::ROLE_1], 'rol 3' => [self::ROLE_3], 'rol 4' => [self::ROLE_4]];
    }

    #[DataProvider('allowedRoles')]
    public function test_allowed_roles_can_access_role_1_3_4_routes(int $roleId): void
    {
        $this->actingAs($this->createUser($roleId));

        $this->get('/stock/products')->assertOk();
        $this->get('/stock/entries')->assertOk();
        $this->get('/stock/outputs')->assertOk();
        // UserController@index está vacío: responde 200 sin contenido.
        $this->get('/security/users')->assertOk();
    }

    public function test_roles_index_lists_the_four_roles_for_allowed_role(): void
    {
        $response = $this->actingAs($this->createUser(self::ROLE_1))
            ->get('/stock/roles')
            ->assertOk()
            ->assertSeeInOrder(['<td>admin</td>', '<td>user</td>', '<td>guest</td>', '<td>manager</td>'], false);

        $roles = collect($response->viewData('roles')->items());
        $this->assertSame([1, 2, 3, 4], $roles->pluck('id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame(['admin', 'user', 'guest', 'manager'], $roles->pluck('role_description')->all());
    }

    public function test_roles_3_and_4_can_access_cards_report(): void
    {
        $this->actingAs($this->createUser(self::ROLE_3))->get('/stock/cards')->assertOk();
        $this->actingAs($this->createUser(self::ROLE_4))->get('/stock/cards')->assertOk();
    }

    public function test_any_authenticated_role_can_access_sales(): void
    {
        $this->actingAs($this->createUser(self::ROLE_2))
            ->get('/stock/sales')
            ->assertOk();
    }
}

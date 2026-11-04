<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\StoreAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Tests\Fixtures\Modules\Probe\ProbeServiceProvider;
use Tests\TestCase;

class ModuleConventionTest extends TestCase
{
    use RefreshDatabase;

    private function user(?string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    private function registerProbeModule(): void
    {
        $this->app->register(ProbeServiceProvider::class);
        Route::getRoutes()->refreshNameLookups();
    }

    public function test_every_module_registers_its_own_view_namespace(): void
    {
        $hints = View::getFinder()->getHints();

        foreach (['Admin', 'Catalog', 'Ordering', 'Payments', 'Fulfillment', 'Reporting'] as $module) {
            $alias = strtolower($module);
            $this->assertArrayHasKey($alias, $hints, "Namespace view '{$alias}::' tidak terdaftar");
            $normalizedHints = array_map(fn (string $path): string => str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), $hints[$alias]);
            $expected = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, app_path("Modules/{$module}/resources/views"));
            $this->assertContains($expected, $normalizedHints);
        }
    }

    public function test_module_route_file_is_loaded_inside_portal_group(): void
    {
        $this->registerProbeModule();

        $route = Route::getRoutes()->getByName('tenant.probe');

        $this->assertNotNull($route, 'Route modul tidak dimuat oleh provider');
        $this->assertSame('tenant/{tenant}/probe', $route->uri());

        // Menguji bahwa route modul otomatis mewarisi middleware portal tenant
        foreach (['web', 'auth', 'verified', 'tenant'] as $middleware) {
            $this->assertContains($middleware, $route->gatherMiddleware());
        }
        $this->assertTrue($route->enforcesScopedBindings(), 'Route portal tenant wajib menegakkan scoped bindings.');
    }

    public function test_module_route_is_guarded_like_core_portal_routes(): void
    {
        $this->registerProbeModule();

        // 1. Siapkan user yang sah dan kaitkan ke toko yang dibuat
        $member = $this->user('tenant');
        $store = $this->storeWithAdmin($member);
        $url = route('tenant.probe', ['tenant' => $store->slug]);

        // Skenario A: Guest (belum login) dialihkan ke login
        $this->get($url)->assertRedirect(route('login'));

        // Skenario B: Admin platform mencoba masuk -> 403 Forbidden (bukan anggota toko ini)
        $this->actingAs($this->user('admin'));
        $this->get($url)->assertForbidden();

        // Skenario C: User dengan role tenant lain (bukan admin toko ini) -> 403 Forbidden
        $otherTenantUser = $this->user('tenant');
        $this->actingAs($otherTenantUser);
        $this->get($url)->assertForbidden();

        // Skenario D: Anggota sah ($member) -> 200 OK dan halaman probe modul terbuka
        $this->actingAs($member);
        $this->get($url)
            ->assertOk()
            ->assertSee('Probe page')
            ->assertSee('Probe count: 0');
    }

    public function test_module_livewire_component_is_resolved_through_module_namespace(): void
    {
        $this->registerProbeModule();

        Livewire::test('probe::counter')
            ->assertSee('Probe count: 0')
            ->call('increment')
            ->assertSee('Probe count: 1');
    }

    public function test_every_portal_route_carries_the_portal_middleware(): void
    {
        $this->registerProbeModule();

        $portals = [
            'tenant.' => ['web', 'auth', 'verified', 'tenant'],
            'admin.' => ['web', 'auth', 'verified', 'role:admin'],
            'customer.' => ['web'],
        ];

        $checked = 0;

        /** @var RoutingRoute $route */
        foreach (Route::getRoutes() as $route) {
            foreach ($portals as $prefix => $required) {
                if (str_starts_with((string) $route->getName(), $prefix)) {
                    foreach ($required as $middleware) {
                        $this->assertContains($middleware, $route->gatherMiddleware(), "{$route->getName()} tanpa {$middleware}");
                    }
                    $checked++;
                }
            }
        }

        $this->assertGreaterThanOrEqual(4, $checked);
    }

    /**
     * Membuat data toko aktif dan mendaftarkan user yang diberikan
     * sebagai admin pengelola di tabel store_admins.
     */
    private function storeWithAdmin(User $user, string $status = 'aktif'): Store
    {
        $store = Store::query()->create([
            'slug' => 'toko-'.fake()->unique()->slug(),
            'name' => fake()->company(),
            'status' => $status,
        ]);

        StoreAdmin::query()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'operator',
        ]);

        return $store;
    }
}

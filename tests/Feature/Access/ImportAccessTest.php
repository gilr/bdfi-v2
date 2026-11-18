<?php

namespace Tests\Feature\Access;

use App\Enums\UserRole;
use App\Livewire\BookImport;
use App\Livewire\BookImportAnalysis;
use App\Models\User;
use Livewire\Livewire;

/**
 * Imports de publications (analyse + import).
 *
 * Règle (pour l'instant) : de l'invité à l'admin, les liens sont visibles dans le menu
 * Outils mais inactifs, et les pages sont refusées ; seul le sysadmin y accède.
 */
class ImportAccessTest extends AccessTestCase
{
    private const IMPORT_PAGES = ['/admin/outils/import-analyse', '/admin/outils/import'];

    /** Rôles qui voient les liens inactifs et n'ont pas accès aux imports */
    public static function nonSysAdminRoles(): array
    {
        return [
            'invité'    => [UserRole::GUEST],
            'membre'    => [UserRole::MEMBER],
            'admin'     => [UserRole::ADMIN],
        ];
    }

    /** @dataProvider nonSysAdminRoles */
    public function test_import_links_are_visible_but_inactive(UserRole $role): void
    {
        $this->actingAs(User::factory()->role($role)->create());

        $this->get('/admin/outils')
            ->assertOk()
            ->assertSee("Analyse d'un fichier d'import", false)
            ->assertSee("Importer un fichier d'import", false)
            ->assertDontSee("href=\"outils/import-analyse\"", false)
            ->assertDontSee("href=\"outils/import\"", false);
    }

    /** @dataProvider nonSysAdminRoles */
    public function test_import_pages_and_actions_are_denied(UserRole $role): void
    {
        $this->actingAs(User::factory()->role($role)->create());

        foreach (self::IMPORT_PAGES as $uri) {
            $this->get($uri)->assertForbidden();
        }
        Livewire::test(BookImport::class)->assertForbidden();
        Livewire::test(BookImportAnalysis::class)->assertForbidden();
    }

    public function test_sysadmin_has_active_links_and_access(): void
    {
        $this->actingAs(User::factory()->role(UserRole::SYSADMIN)->create());

        $this->get('/admin/outils')
            ->assertOk()
            ->assertSee("href=\"outils/import-analyse\"", false)
            ->assertSee("href=\"outils/import\"", false);

        foreach (self::IMPORT_PAGES as $uri) {
            $this->get($uri)->assertOk();
        }
    }
}

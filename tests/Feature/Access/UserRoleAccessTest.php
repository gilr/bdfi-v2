<?php

namespace Tests\Feature\Access;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Accès d'un membre connecté avec le rôle USER (simple utilisateur).
 *
 * Règle : User = oui, Admin = non, Filament = non.
 * (+ les zones non protégées restent accessibles)
 */
class UserRoleAccessTest extends AccessTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->role(UserRole::USER)->create());
    }

    public function test_user_can_access_user_zone(): void
    {
        $this->assertZoneIsGranted('user');
    }

    public function test_user_main_pages_are_displayed(): void
    {
        foreach (['/user', '/user/preferences', '/user/bibliotheque', '/user/gestion-biblio',
                  '/user/affiche-collection', '/user/mancoliste', '/user/profile'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_my_library_menu_follows_user_preference(): void
    {
        // Option non activée dans la page utilisateur : pas de menu "Ma biblio"
        $this->actingAs(User::factory()->create(['gestion_biblio' => false]));
        $this->get('/user')->assertOk()->assertDontSee('Ma biblio');

        // Option activée : menu présent, et la page s'affiche
        $this->actingAs(User::factory()->create(['gestion_biblio' => true]));
        $this->get('/user')->assertOk()->assertSee('Ma biblio');
        $this->get(route('user/bibliotheque'))->assertOk();
    }

    public function test_user_cannot_access_admin_zone(): void
    {
        $this->assertZoneIsDenied('admin');
    }

    public function test_user_cannot_access_filament_tables(): void
    {
        $this->assertZoneIsDenied('filament');
    }

    public function test_user_can_access_public_zones(): void
    {
        foreach (['/', '/auteurs', '/ouvrages', '/textes', '/series', '/collections',
                  '/editeurs', '/prix', '/stats', '/site'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }
}

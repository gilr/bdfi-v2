<?php

namespace Tests\Feature\Access;

/**
 * Accès d'une personne NON CONNECTÉE.
 *
 * Règle : un visiteur anonyme ne peut accéder ni à la zone 'user', ni à la zone 'admin',
 * ni aux tables Filament (redirection vers une page de connexion ou refus 401/403).
 */
class GuestAccessTest extends AccessTestCase
{
    public function test_guest_cannot_access_user_zone(): void
    {
        $this->assertZoneIsDenied('user');
    }

    public function test_guest_cannot_access_admin_zone(): void
    {
        $this->assertZoneIsDenied('admin');
    }

    public function test_guest_cannot_access_filament_tables(): void
    {
        $this->assertZoneIsDenied('filament');
    }

    /**
     * "Ma biblio" (menu du haut, si activé dans la page utilisateur) : mêmes droits que la zone user.
     * Déjà couvert par la zone 'user' ci-dessus, mais explicité ici pour documenter la règle.
     */
    public function test_guest_cannot_access_my_library(): void
    {
        $requests = [
            ['GET',  '/user/bibliotheque'],
            ['GET',  '/user/gestion-biblio'],
            ['GET',  '/user/affiche-collection'],
            ['GET',  '/user/mancoliste'],
            ['POST', '/user/ajouter-collection'],
            ['POST', '/user/modifier-collection'],
            ['POST', '/user/retirer-collection'],
            ['POST', '/user/ajouter-publication'],
            ['POST', '/user/retirer-publication'],
        ];
        foreach ($requests as [$method, $uri]) {
            $this->call($method, $uri)->assertRedirect(route('login'));
        }
    }

    public function test_guest_can_still_reach_login_pages(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get('/filament/login')->assertOk();
    }
}

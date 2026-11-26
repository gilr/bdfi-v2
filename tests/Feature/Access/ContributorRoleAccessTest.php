<?php

namespace Tests\Feature\Access;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Accès d'un utilisateur connecté avec le rôle PROPONENT (Contributeur).
 *
 * Règles :
 *  - Zone user : oui.
 *  - Zone admin réduite à la contribution : accueil admin, page formulaires (vérifications
 *    rapides + propositions), formulaire de proposition, rapports.
 *  - Tout le reste de la zone admin (autres formulaires, validations, outils, labs,
 *    téléchargements) est refusé, et n'apparaît pas dans les menus.
 *  - Filament et retirages : refusés.
 */
class ContributorRoleAccessTest extends AccessTestCase
{
    /** Routes admin ouvertes au contributeur (méthode, uri) */
    private const CONTRIBUTION_ROUTES = [
        ['GET',  'admin'],
        ['GET',  'admin/formulaires'],
        ['GET',  'admin/formulaires/proposer-publication'],
        ['POST', 'admin/formulaires/proposer-publication'],
        ['POST', 'admin/formulaires/proposer-publication/{id}/retirer'],
        ['GET',  'admin/rapports'],
        ['GET',  'admin/rapports/dates-bizarres'],
        ['GET',  'admin/rapports/manque-date-naissance'],
        ['GET',  'admin/rapports/manque-date-deces'],
        ['GET',  'admin/rapports/etat-biographies-{i}'],
        ['GET',  'admin/rapports/manque-nationalite'],
        ['GET',  'admin/rapports/erreur-nationalite'],
        ['GET',  'admin/rapports/prix-{an}'],
        ['GET',  'admin/rapports/erreurs-variantes'],
        ['GET',  'admin/rapports/evenements-a-completer'],
        ['POST', 'admin/rapports/evenements-a-completer/clore'],   // route ouverte, action réservée aux membres
        ['GET',  'admin/rapports/anniversaires-fb-jour'],
        ['GET',  'admin/rapports/anniversaires-fb-semaine'],
        ['GET',  'admin/rapports/anniversaires-fb-mois'],
    ];

    private User $contributor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contributor = User::factory()->role(UserRole::PROPONENT)->create();
        $this->actingAs($this->contributor);
    }

    public function test_contributor_can_access_user_zone(): void
    {
        $this->assertZoneIsGranted('user');
    }

    public function test_contributor_can_display_contribution_pages(): void
    {
        $failures = [];
        foreach (self::CONTRIBUTION_ROUTES as [$method, $uri]) {
            if ($method !== 'GET') {
                continue;
            }
            $uri = '/' . str_replace(['{i}', '{an}'], ['0', '2000'], $uri);
            $status = $this->get($uri)->getStatusCode();
            if ($status !== 200) {
                $failures[] = "GET $uri -> $status";
            }
        }
        $this->assertSame([], $failures, "Pages de contribution non affichées :\n" . implode("\n", $failures));
    }

    /** Toutes les autres routes admin sont refusées (lues dynamiquement : une nouvelle route est couverte d'office). */
    public function test_contributor_is_denied_the_rest_of_admin_zone(): void
    {
        $allowed = collect(self::CONTRIBUTION_ROUTES)->map(fn ($r) => $r[0] . ' ' . $r[1]);
        $failures = [];
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uri = $route->uri();
            if ($uri !== 'admin' && !str_starts_with($uri, 'admin/')) {
                continue;
            }
            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                if ($allowed->contains("$method $uri")) {
                    continue;
                }
                $response = $this->from('/__origine-test__')->call($method, '/' . preg_replace('/\{[^}]+\}/', '1', $uri));
                if (!$this->isDenied($response)) {
                    $failures[] = "$method /$uri -> " . $response->getStatusCode();
                }
            }
        }
        $this->assertSame([], $failures, "Routes admin accessibles à un contributeur :\n" . implode("\n", $failures));
    }

    public function test_contributor_cannot_access_filament(): void
    {
        $this->assertZoneIsDenied('filament');
    }

    public function test_contributor_cannot_access_reprints(): void
    {
        $this->get('/retirages')->assertForbidden();
    }

    public function test_contributor_can_only_propose_not_publish(): void
    {
        // Le contributeur peut proposer (formulaire vide : erreur de validation, pas de refus de droits)...
        $this->post('/admin/formulaires/proposer-publication', [])->assertSessionHasErrors('name');
        // ... mais pas créer directement une publication
        $this->post('/admin/formulaires/ajout-publication', ['publication_status' => 'paru'])->assertForbidden();
    }

    public function test_menu_hides_tools_labs_and_tables(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('href="' . route('admin/formulaires') . '"', false)
            ->assertSee('href="' . route('admin/rapports') . '"', false)
            ->assertDontSee('href="' . route('admin/outils') . '"', false)
            ->assertDontSee('href="' . route('admin/labs') . '"', false)
            ->assertDontSee('href="' . url('/filament') . '"', false)
            ->assertSee('Espace contributeur')
            ->assertDontSee('Backup de tables');
    }

    public function test_forms_page_only_offers_proposals(): void
    {
        $this->get('/admin/formulaires')
            ->assertOk()
            ->assertSee("href=\"formulaires/proposer-publication\"", false)
            ->assertDontSee("href=\"formulaires/ajout-auteur\"", false)
            ->assertDontSee("href=\"formulaires/modifier-auteur\"", false)
            ->assertDontSee("href=\"formulaires/publications-proposees\"", false)
            ->assertDontSee('Créations rapides');
    }

    public function test_admin_links_open_only_contribution_pages(): void
    {
        $user = $this->contributor;
        foreach (['/admin', 'formulaires/proposer-publication', '/admin/rapports/dates-bizarres', 'rapports', '/textes/x'] as $url) {
            $this->assertTrue($user->canOpenAdminLink($url), "$url devrait être ouvrable");
        }
        foreach (['/filament/authors/1', 'formulaires/ajout-auteur', '/admin/formulaires/gerer-variantes?titre=1', '/admin/outils', 'labs'] as $url) {
            $this->assertFalse($user->canOpenAdminLink($url), "$url ne devrait pas être ouvrable");
        }
        $guest = User::factory()->role(UserRole::GUEST)->create();
        $this->assertTrue($guest->canOpenAdminLink('/filament/authors/1'));
    }
}

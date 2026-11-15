<?php

namespace Tests\Feature\Access;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource;
use App\Livewire\AuthorSearchAndEdit;
use App\Livewire\BookImport;
use App\Livewire\BookImportAnalysis;
use App\Livewire\ConverterContent;
use App\Livewire\ConverterFormat;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

/**
 * Accès d'un membre connecté avec le rôle GUEST (Invité) : "mode découverte".
 *
 * Règles :
 *  - Zone user : oui.
 *  - Zone admin : voit les pages de menu (accueil, formulaires, outils, rapports),
 *    ouvre les formulaires et outils, consulte les rapports ; mais aucune exécution :
 *    enregistrements, conversions, modification d'auteur, téléchargements et imports refusés.
 *  - Filament : consultation des tables (sauf utilisateurs), aucune création/modification/suppression.
 */
class GuestRoleAccessTest extends AccessTestCase
{
    /** Pages admin que l'invité doit pouvoir afficher */
    private const VISIBLE_ADMIN_PAGES = [
        // Menus
        '/admin',
        '/admin/formulaires',
        '/admin/outils',
        '/admin/rapports',
        // Formulaires (affichés, bouton d'enregistrement désactivé)
        '/admin/formulaires/ajout-auteur',
        '/admin/formulaires/modifier-auteur',
        '/admin/formulaires/ajout-editeur',
        '/admin/formulaires/ajout-collection',
        '/admin/formulaires/ajout-texte',
        '/admin/formulaires/ajout-publication',
        '/admin/formulaires/ajout-publication?statut=annonce',
        '/admin/formulaires/proposer-publication',
        '/admin/formulaires/ajout-annee-prix',
        '/admin/formulaires/publications-proposees',
        '/admin/formulaires/programmes-echus',
        '/admin/formulaires/programmes-non-echus',
        // Outils (affichés, conversion désactivée)
        '/admin/outils/conversion-format',
        '/admin/outils/conversion-content',
        // Rapports
        '/admin/rapports/dates-bizarres',
        '/admin/rapports/manque-date-naissance',
        '/admin/rapports/manque-date-deces',
        '/admin/rapports/etat-biographies-0',
        '/admin/rapports/manque-nationalite',
        '/admin/rapports/erreur-nationalite',
        '/admin/rapports/prix-2000',
        '/admin/rapports/anniversaires-fb-jour',
        '/admin/rapports/anniversaires-fb-semaine',
        '/admin/rapports/anniversaires-fb-mois',
    ];

    /** Requêtes admin qui doivent être refusées (exécutions) */
    private const DENIED_ADMIN_REQUESTS = [
        ['POST', '/admin/formulaires/ajout-auteur'],
        ['POST', '/admin/formulaires/ajout-editeur'],
        ['POST', '/admin/formulaires/ajout-collection'],
        ['POST', '/admin/formulaires/ajout-texte'],
        ['POST', '/admin/formulaires/ajout-publication'],
        ['POST', '/admin/formulaires/programme-parution'],
        ['POST', '/admin/formulaires/proposer-publication'],
        ['POST', '/admin/formulaires/ajout-annee-prix'],
        ['PUT',  '/admin/formulaires/publications-proposees'],
        ['PUT',  '/admin/formulaires/programmes-echus'],
        ['PUT',  '/admin/formulaires/programmes-non-echus'],
        ['GET',  '/admin/telechargements'],
        ['GET',  '/admin/telechargements/Author'],
        ['GET',  '/admin/telechargements/User'],
        ['GET',  '/admin/outils/import'],
        ['GET',  '/admin/outils/import-analyse'],
    ];

    private User $guest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guest = User::factory()->role(UserRole::GUEST)->create();
        $this->actingAs($this->guest);
    }

    // ----- Zone user -----

    public function test_guest_can_access_user_zone(): void
    {
        $this->assertZoneIsGranted('user');
    }

    // ----- Zone admin -----

    public function test_guest_can_display_admin_menus_forms_tools_and_reports(): void
    {
        $failures = [];
        foreach (self::VISIBLE_ADMIN_PAGES as $uri) {
            $status = $this->get($uri)->getStatusCode();
            if ($status !== 200) {
                $failures[] = "GET $uri -> $status";
            }
        }
        $this->assertSame([], $failures, "Pages admin non affichées pour un invité :\n" . implode("\n", $failures));
    }

    public function test_guest_sees_discovery_notice_on_forms(): void
    {
        $this->get('/admin/formulaires/ajout-auteur')
            ->assertSee('Mode découverte')
            ->assertSee('disabled', false);
    }

    public function test_guest_cannot_execute_admin_actions(): void
    {
        $failures = [];
        foreach (self::DENIED_ADMIN_REQUESTS as [$method, $uri]) {
            $response = $this->call($method, $uri);
            if (!$this->isDenied($response)) {
                $failures[] = "$method $uri -> " . $response->getStatusCode();
            }
        }
        $this->assertSame([], $failures, "Exécutions admin autorisées pour un invité :\n" . implode("\n", $failures));
    }

    public function test_guest_cannot_run_livewire_actions(): void
    {
        Livewire::test(ConverterFormat::class)->call('convert')->assertForbidden();
        Livewire::test(ConverterContent::class)->call('convert')->assertForbidden();
        Livewire::test(AuthorSearchAndEdit::class)->call('save')->assertForbidden();
    }

    public function test_guest_cannot_open_imports(): void
    {
        Livewire::test(BookImport::class)->assertForbidden();
        Livewire::test(BookImportAnalysis::class)->assertForbidden();
    }

    // ----- Filament -----

    public function test_guest_can_list_filament_tables_except_users(): void
    {
        $failures = [];
        foreach ($this->filamentResources() as $resource) {
            $expected = $resource === UserResource::class ? 403 : 200;
            $status = $this->get($resource::getUrl('index'))->getStatusCode();
            if ($status !== $expected) {
                $failures[] = class_basename($resource) . " index -> $status (attendu $expected)";
            }
        }
        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_guest_cannot_open_filament_create_pages(): void
    {
        $failures = [];
        foreach ($this->filamentResources() as $resource) {
            if (!$resource::hasPage('create')) {
                continue;
            }
            $status = $this->get($resource::getUrl('create'))->getStatusCode();
            if ($status !== 403) {
                $failures[] = class_basename($resource) . " create -> $status";
            }
        }
        $this->assertSame([], $failures, "Pages de création Filament accessibles à un invité :\n" . implode("\n", $failures));
    }

    /**
     * Droits Filament par enregistrement (policies) : vérifiés sur une instance vide
     * de chaque modèle, sans avoir à créer de données.
     */
    public function test_guest_has_read_only_rights_on_filament_records(): void
    {
        $failures = [];
        foreach ($this->filamentResources() as $resource) {
            $model = $resource::getModel();
            $record = new $model();
            $name = class_basename($resource);

            $canView = $resource !== UserResource::class;
            if ($resource::canView($record) !== $canView) {
                $failures[] = "$name : view " . ($canView ? 'refusé' : 'autorisé');
            }
            foreach (['canCreate' => [], 'canEdit' => [$record], 'canDelete' => [$record],
                      'canForceDelete' => [$record], 'canRestore' => [$record]] as $method => $args) {
                if ($resource::$method(...$args)) {
                    $failures[] = "$name : $method autorisé";
                }
            }
        }
        $this->assertSame([], $failures, "Droits Filament incorrects pour un invité :\n" . implode("\n", $failures));
    }

    // ----- Outils -----

    /** @return class-string<\Filament\Resources\Resource>[] */
    private function filamentResources(): array
    {
        return Filament::getPanel('admin')->getResources();
    }
}

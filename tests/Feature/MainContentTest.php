<?php

namespace Tests\Feature;

use App\Bdfi\MainContentFinder;
use App\Enums\UserRole;
use App\Livewire\MainContentPicker;
use App\Models\Author;
use App\Models\Publication;
use App\Models\TableOfContent;
use App\Models\Title;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Contenu principal d'une publication : texte existant identique rattaché, ou nouveau texte créé avec elle */
class MainContentTest extends TestCase
{
    use RefreshDatabase;

    private function author(string $name): Author
    {
        return Author::create(['name' => $name, 'first_name' => 'Jean']);
    }

    private function title(string $name, array $authors, array $extra = []): Title
    {
        $title = Title::create($extra + [
            'name' => $name, 'type' => 'novel', 'variant_type' => 'premier',
            'is_genre' => 'oui', 'genre_stat' => 'sf', 'target_audience' => 'inconnu',
        ]);
        $title->authors()->attach(array_map(fn ($a) => $a->id, $authors));

        return $title;
    }

    private function data(array $overrides = []): array
    {
        return $overrides + [
            'publication_status'   => 'paru',
            'name'                 => 'Silo',
            'type'                 => 'roman',
            'support'              => 'papier',
            'format'               => 'inconnu',
            'approximate_parution' => '2020-05-00',
            'is_genre'             => 'oui',
            'genre_stat'           => 'sf',
            'target_audience'      => 'inconnu',
        ];
    }

    public function test_candidates_are_same_title_and_exactly_same_authors(): void
    {
        $a = $this->author('Howey');
        $b = $this->author('Autre');
        $same = $this->title('Silo', [$a]);
        $this->title('Silo', [$a, $b]);   // autres auteurs : écarté
        $this->title('Autre titre', [$a]);   // autre titre : écarté

        $ids = MainContentFinder::candidates('Silo', [$a->id])->pluck('id')->all();

        $this->assertSame([$same->id], $ids);
        $this->assertSame([], MainContentFinder::candidates('Silo', [])->all(), 'sans auteur : aucun rapprochement');
    }

    public function test_candidates_and_new_title_must_have_a_type_compatible_with_the_publication(): void
    {
        $member = User::factory()->role(UserRole::MEMBER)->create();
        $a = $this->author('Howey');
        $novel = $this->title('Silo', [$a]);   // roman

        $this->assertCount(1, MainContentFinder::candidates('Silo', [$a->id], 'roman'));
        $this->assertCount(0, MainContentFinder::candidates('Silo', [$a->id], 'fiction'), 'pas de roman pour une fiction courte');

        // Fiction courte : ni le roman existant, ni un nouveau texte de type roman
        $this->actingAs($member)->post('/admin/formulaires/ajout-publication',
            $this->data(['type' => 'fiction', 'author1_id' => $a->id, 'title_choice' => (string) $novel->id]))
            ->assertSessionHasErrors('title_choice');
        $this->post('/admin/formulaires/ajout-publication',
            $this->data(['type' => 'fiction', 'author1_id' => $a->id, 'title_choice' => 'new', 'new_title_type' => 'novel']))
            ->assertSessionHasErrors('new_title_type');
        $this->post('/admin/formulaires/ajout-publication',
            $this->data(['type' => 'fiction', 'author1_id' => $a->id, 'title_choice' => 'new', 'new_title_type' => 'shortstory']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Publication::count());
    }

    public function test_type_table_and_extracts(): void
    {
        $member = User::factory()->role(UserRole::MEMBER)->create();
        $a = $this->author('Howey');

        // Roman : plus de novella, théâtre admis ; non-fiction : lettre admise, plus de chroniques
        $roman = array_map(fn ($t) => $t->value, \App\Enums\PublicationContent::ROMAN->mainContentTypes());
        $this->assertNotContains('novella', $roman);
        $this->assertContains('theatre', $roman);
        $essai = array_map(fn ($t) => $t->value, \App\Enums\PublicationContent::NON_FICTION->mainContentTypes());
        $this->assertContains('letter', $essai);
        $this->assertNotContains('chroniques', $essai);

        // Extrait (de type roman) : admis pour une fiction courte seulement
        $extract = $this->title('Silo', [$a], ['variant_type' => 'extrait']);
        $this->assertSame([$extract->id], MainContentFinder::candidates('Silo', [$a->id], 'fiction')->pluck('id')->all());
        $this->assertCount(1, MainContentFinder::candidates('Silo', [$a->id], 'roman'), 'type roman : admis comme roman');
        $this->assertCount(0, MainContentFinder::candidates('Silo', [$a->id], 'non-fiction'));

        $this->actingAs($member)->post('/admin/formulaires/ajout-publication', $this->data([
            'type' => 'fiction', 'author1_id' => $a->id, 'title_choice' => 'new', 'new_title_type' => 'novel',
            'new_title_extract' => '1', 'new_title_novelization' => 'film',
        ]))->assertSessionHasNoErrors();
        $created = Title::where('id', '!=', $extract->id)->first();
        $this->assertSame('extrait', $created->variant_type);
        $this->assertSame('film', $created->is_novelization->value);

        // Extrait refusé pour un roman
        $this->post('/admin/formulaires/ajout-publication', $this->data([
            'type' => 'roman', 'name' => 'Autre', 'author1_id' => $a->id, 'title_choice' => 'new', 'new_title_type' => 'novel', 'new_title_extract' => '1',
        ]))->assertSessionHasErrors('new_title_extract');
    }

    public function test_author_search_finds_first_name_then_name(): void
    {
        $this->author('Williams');   // prénom Jean (voir author())
        Author::create(['name' => 'Williams', 'first_name' => 'Tad']);

        Livewire::test(\App\Livewire\EntityPicker::class, ['model' => Author::class, 'fieldName' => 'author1', 'label' => 'Auteur'])
            ->set('search', 'Tad Williams')
            ->assertSet('results', fn ($r) => count($r) === 1 && $r[0]['label'] === 'Tad Williams');
    }

    public function test_creation_page_displays_the_picker_with_and_without_old_input(): void
    {
        $member = User::factory()->role(UserRole::MEMBER)->create();

        $this->actingAs($member)->get('/admin/formulaires/ajout-publication')
            ->assertOk()->assertSee('Chercher le contenu principal');

        // Après une erreur de validation : le formulaire est réaffiché avec les anciennes valeurs
        $a = $this->author('Howey');
        $this->post('/admin/formulaires/ajout-publication', $this->data(['author1_id' => $a->id, 'title_choice' => 'new']));
        $this->followingRedirects()->actingAs($member)->get('/admin/formulaires/ajout-publication')->assertOk();
    }

    public function test_choice_is_required(): void
    {
        $member = User::factory()->role(UserRole::MEMBER)->create();

        $this->actingAs($member)->post('/admin/formulaires/ajout-publication', $this->data())
            ->assertSessionHasErrors('title_choice');

        $this->assertSame(0, Publication::count());
    }

    public function test_existing_identical_title_is_attached(): void
    {
        $member = User::factory()->role(UserRole::MEMBER)->create();
        $a = $this->author('Howey');
        $title = $this->title('Silo', [$a]);

        $this->actingAs($member)->post('/admin/formulaires/ajout-publication',
            $this->data(['author1_id' => $a->id, 'title_choice' => (string) $title->id]))
            ->assertSessionHasNoErrors();

        $publication = Publication::first();
        $this->assertSame([$title->id], $publication->titles->pluck('id')->all());
        $this->assertSame(1, Title::count(), 'aucun nouveau texte');
    }

    public function test_title_that_is_not_identical_is_refused(): void
    {
        $member = User::factory()->role(UserRole::MEMBER)->create();
        $a = $this->author('Howey');
        $other = $this->title('Un autre titre', [$a]);

        $this->actingAs($member)->post('/admin/formulaires/ajout-publication',
            $this->data(['author1_id' => $a->id, 'title_choice' => (string) $other->id]))
            ->assertSessionHasErrors('title_choice');

        $this->assertSame(0, Publication::count());
    }

    public function test_new_title_is_created_with_the_publication(): void
    {
        $member = User::factory()->role(UserRole::MEMBER)->create();
        $a = $this->author('Howey');

        $this->actingAs($member)->post('/admin/formulaires/ajout-publication', $this->data([
            'author1_id' => $a->id, 'title_choice' => 'new', 'new_title_type' => 'novel',
            'new_title_copyright' => '2012-01-00', 'new_title_vo' => 'Wool', 'new_title_translators' => 'Dupont Jean',
        ]))->assertSessionHasNoErrors();

        $title = Title::first();
        $this->assertSame('Silo', $title->name);
        $this->assertSame('Wool', $title->title_vo);
        $this->assertSame('2012-01-00', $title->copyright);
        $this->assertSame('2020-05-00', $title->copyright_fr, 'date fr = parution de la publication');
        $this->assertSame([$a->id], $title->authors->pluck('id')->all());
        $this->assertSame(1, TableOfContent::count());
        $this->assertSame($title->id, Publication::first()->titles->first()->id);
    }

    public function test_new_title_requires_a_type(): void
    {
        $member = User::factory()->role(UserRole::MEMBER)->create();

        $this->actingAs($member)->post('/admin/formulaires/ajout-publication', $this->data(['title_choice' => 'new']))
            ->assertSessionHasErrors('new_title_type');

        $this->assertSame(0, Publication::count());
        $this->assertSame(0, Title::count());
    }

    public function test_picker_lists_candidates_with_their_details(): void
    {
        $a = $this->author('Howey');
        $this->title('Silo', [$a], ['copyright_fr' => '2013-10-00', 'title_vo' => 'Wool', 'translators' => 'Dupont Jean']);

        Livewire::test(MainContentPicker::class)
            ->call('search', 'Silo', (string) $a->id, '', 'roman')
            ->assertSee('1ère parution fr')
            ->assertSee('2013-10-00')
            ->assertSee('Wool')
            ->assertSee('Dupont Jean')
            ->assertSet('newType', 'novel');
    }
}

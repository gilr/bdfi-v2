<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\PublicationSummaryEditor;
use App\Models\Author;
use App\Models\Publication;
use App\Models\TableOfContent;
use App\Models\Title;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Formulaire de sommaire : contenu principal en racine, rattachements, création et rattachement de textes */
class PublicationSummaryTest extends TestCase
{
    use RefreshDatabase;

    private Author $author;
    private Publication $publication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->role(UserRole::MEMBER)->create());
        $this->author = Author::create(['name' => 'Howey', 'first_name' => 'Hugh']);
        $this->publication = Publication::create([
            'status' => 'paru', 'name' => 'Silo', 'type' => 'roman', 'support' => 'papier', 'format' => 'inconnu',
            'approximate_parution' => '2020-05-00', 'is_genre' => 'oui', 'genre_stat' => 'sf', 'target_audience' => 'inconnu',
            'isbn' => '?', 'is_verified' => false,
        ]);
        $this->publication->authors()->attach($this->author->id, ['role' => 'author']);
    }

    private function title(string $name, array $extra = [], bool $withAuthor = true): Title
    {
        $title = Title::create($extra + [
            'name' => $name, 'type' => 'novel', 'variant_type' => 'premier',
            'is_genre' => 'oui', 'genre_stat' => 'sf', 'target_audience' => 'inconnu',
        ]);
        if ($withAuthor) {
            $title->authors()->attach($this->author->id);
        }

        return $title;
    }

    private function attach(Title $title, float $level, int $order): TableOfContent
    {
        return TableOfContent::create(['publication_id' => $this->publication->id, 'title_id' => $title->id, 'level' => $level, 'order' => $order]);
    }

    private function editor()
    {
        return Livewire::test(PublicationSummaryEditor::class)->call('selectPublication', $this->publication->id);
    }

    public function test_shows_main_content_at_root_and_others_below(): void
    {
        $main = $this->attach($this->title('Silo'), 0, 1);
        $this->attach($this->title('Préface'), 1, 2);

        $this->editor()
            ->assertSee('contenu principal')
            ->assertSee('Préface')
            ->assertSet("rows.{$main->id}.level", '0');
    }

    public function test_without_main_content_candidates_are_proposed_and_main_can_be_attached(): void
    {
        $same = $this->title('Silo');
        $this->title('Silo', ['type' => 'shortstory']);   // type incompatible avec un roman

        $this->editor()
            ->assertSee('pas de contenu principal')
            ->call('attach', $same->id)   // refusé : pas de contenu principal
            ->assertSet('error', "Le contenu principal doit d'abord être rattaché ou créé.")
            ->call('attachMain', $same->id)
            ->assertHasNoErrors();

        $row = TableOfContent::first();
        $this->assertSame($same->id, $row->title_id);
        $this->assertEquals(0.0, (float) $row->level);
        $this->assertSame(1, (int) $row->order);
    }

    public function test_save_rows_updates_pages_and_renumbers_with_main_first(): void
    {
        $this->attach($this->title('Silo'), 0, 1);
        $a = $this->attach($this->title('Texte A'), 1, 5);
        $b = $this->attach($this->title('Texte B'), 1, 2);

        $this->editor()
            ->set("rows.{$a->id}.start_page", '15')
            ->set("rows.{$a->id}.level", '1.5')
            ->call('saveRows')
            ->assertHasNoErrors();

        $this->assertSame(['15'], [$a->fresh()->start_page]);
        $this->assertEquals(1.5, (float) $a->fresh()->level);
        $this->assertSame(2, (int) $b->fresh()->order, 'B (ordre 2) avant A (ordre 5)');
        $this->assertSame(3, (int) $a->fresh()->order);
    }

    public function test_only_the_main_content_is_level_zero(): void
    {
        $this->attach($this->title('Silo'), 0, 1);
        $a = $this->attach($this->title('Texte A'), 1, 2);

        $this->editor()
            ->set("rows.{$a->id}.level", '0')
            ->call('saveRows')
            ->assertHasErrors("rows.{$a->id}.level");
    }

    public function test_remove_breaks_the_link_but_never_for_main_content(): void
    {
        $main = $this->attach($this->title('Silo'), 0, 1);
        $other = $this->title('Texte A');
        $row = $this->attach($other, 1, 2);

        $this->editor()
            ->call('removeRow', $main->id)
            ->assertSet('error', 'Le contenu principal ne peut pas être retiré du sommaire.')
            ->call('removeRow', $row->id);

        $this->assertSame(1, TableOfContent::count());
        $this->assertNotNull(Title::find($other->id), 'le texte reste en base');
    }

    public function test_attach_an_existing_text_at_the_end(): void
    {
        $this->attach($this->title('Silo'), 0, 1);
        $other = $this->title('Un autre texte', [], false);

        $this->editor()
            ->set('addSearch', 'autre texte')
            ->assertSee('Un autre texte')
            ->call('attach', $other->id)
            ->call('attach', $other->id)   // déjà présent
            ->assertSet('error', '« Un autre texte » est déjà dans ce sommaire.');

        $this->assertSame(2, TableOfContent::count());
        $this->assertSame(2, (int) TableOfContent::where('title_id', $other->id)->value('order'));
    }

    public function test_create_main_content_with_publication_data(): void
    {
        $this->editor()
            ->call('openNew')
            ->assertSet('new.name', 'Silo')
            ->assertSet('new.type', 'novel')
            ->set('new.title_vo', 'Wool')
            ->call('createText')
            ->assertHasNoErrors();

        $title = Title::first();
        $this->assertSame('Silo', $title->name);
        $this->assertSame('Wool', $title->title_vo);
        $this->assertSame([$this->author->id], $title->authors->pluck('id')->all());
        $this->assertSame('premier', $title->variant_type);
        $this->assertEquals(0.0, (float) TableOfContent::first()->level);
    }

    public function test_main_content_type_must_be_compatible(): void
    {
        $this->editor()
            ->call('openNew')
            ->set('new.type', 'shortstory')
            ->call('createText')
            ->assertHasErrors('new.type');

        $this->assertSame(0, Title::count());
    }

    public function test_create_new_text_with_new_author_and_duplicate_warning(): void
    {
        $this->attach($this->title('Silo'), 0, 1);
        $existing = $this->title('Nouvelle X', ['type' => 'shortstory']);   // même titre et mêmes auteurs que celui à créer

        $c = $this->editor()
            ->call('openNew')
            ->set('new.name', 'Nouvelle X')
            ->set('new.type', 'shortstory')
            ->set('newAuthorFirst', 'Jean')->set('newAuthorLast', 'Nouveau')
            ->call('createAuthor')
            ->assertSet('error', '');
        $newAuthor = Author::where('name', 'Nouveau')->first();
        $this->assertNotNull($newAuthor);

        // Auteurs : l'auteur créé seulement => pas de doublon avec le texte existant (auteur différent)
        $c->call('removeAuthor', $this->author->id)
            ->set('new.level', '1')
            ->call('createText')
            ->assertHasNoErrors()
            ->assertSet('error', '');

        $created = Title::where('name', 'Nouvelle X')->where('id', '!=', $existing->id)->first();
        $this->assertSame([$newAuthor->id], $created->authors->pluck('id')->all());
        $this->assertSame(2, TableOfContent::count());
    }

    public function test_identical_text_is_signalled_before_creation(): void
    {
        $this->attach($this->title('Silo'), 0, 1);
        $this->title('Nouvelle X', ['type' => 'shortstory']);

        $this->editor()
            ->call('openNew')
            ->set('new.name', 'Nouvelle X')
            ->set('new.type', 'shortstory')
            ->set('new.level', '1')
            ->call('createText')   // auteurs de la publication = ceux du texte existant
            ->assertSet('duplicates', fn ($d) => count($d) === 1);

        $this->assertSame(1, Title::where('name', 'Nouvelle X')->count());
    }

    public function test_existing_author_cannot_be_recreated(): void
    {
        $this->editor()
            ->call('openNew')
            ->set('newAuthorFirst', 'Hugh')->set('newAuthorLast', 'Howey')
            ->call('createAuthor');

        $this->assertSame(1, Author::where('name', 'Howey')->count());
    }

    public function test_direct_link_opens_the_publication(): void
    {
        $this->get('/admin/formulaires/modifier-sommaire?id=' . $this->publication->id)
            ->assertOk()->assertSee('Sommaire de Silo');
    }
}

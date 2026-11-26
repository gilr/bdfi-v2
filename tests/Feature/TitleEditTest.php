<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\TitleSearchAndEdit;
use App\Models\Author;
use App\Models\Title;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Modification d'un texte (oeuvre) : la création isolée n'existe plus */
class TitleEditTest extends TestCase
{
    use RefreshDatabase;

    private function title(): Title
    {
        $title = Title::create([
            'name' => 'Silo', 'type' => 'novel', 'variant_type' => 'premier', 'copyright' => '2012-01-00',
            'is_genre' => 'oui', 'genre_stat' => 'sf', 'target_audience' => 'inconnu',
        ]);
        $title->authors()->attach(Author::create(['name' => 'Howey', 'first_name' => 'Hugh'])->id);

        return $title;
    }

    public function test_member_edits_a_title_and_keeps_its_slug(): void
    {
        $title = $this->title();
        $slug = $title->slug;
        $this->actingAs(User::factory()->role(UserRole::MEMBER)->create());

        Livewire::test(TitleSearchAndEdit::class)
            ->set('search', 'Sil')
            ->assertSee('Howey')
            ->call('selectTitle', $title->id)
            ->set('name', 'Silo (révisé)')
            ->set('copyright_fr', '2013-10-00')
            ->set('title_vo', 'Wool')
            ->set('is_novelization', 'film')
            ->set('synopsis', 'Un silo.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('success', 'Modifications enregistrées avec succès.');

        $fresh = $title->fresh();
        $this->assertSame('Silo (révisé)', $fresh->name);
        $this->assertSame('2013-10-00', $fresh->copyright_fr);
        $this->assertSame('Wool', $fresh->title_vo);
        $this->assertSame('film', $fresh->is_novelization->value);
        $this->assertSame('Un silo.', $fresh->synopsis);
        $this->assertSame($slug, $fresh->slug, "l'adresse de la page ne change pas");
        $this->assertNull($fresh->information, 'champ vide : null comme dans les données');
    }

    public function test_invalid_date_is_refused(): void
    {
        $title = $this->title();
        $this->actingAs(User::factory()->role(UserRole::MEMBER)->create());

        Livewire::test(TitleSearchAndEdit::class)
            ->call('selectTitle', $title->id)
            ->set('copyright', 'demain')
            ->call('save')
            ->assertHasErrors('copyright');

        $this->assertSame('2012-01-00', $title->fresh()->copyright);
    }

    public function test_direct_link_selects_the_title(): void
    {
        $title = $this->title();
        $this->actingAs(User::factory()->role(UserRole::MEMBER)->create());

        $this->get('/admin/formulaires/modifier-oeuvre?id=' . $title->id)->assertOk()->assertSee('Modifier le texte');
    }

    public function test_isolated_title_creation_is_gone(): void
    {
        $this->actingAs(User::factory()->role(UserRole::MEMBER)->create());

        $this->get('/admin/formulaires/ajout-texte')->assertNotFound();
        $this->post('/admin/formulaires/ajout-texte', ['name' => 'x'])->assertStatus(404);
    }
}

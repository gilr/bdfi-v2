<?php

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Enums\UserRole;
use App\Models\Proposal;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Propositions d'ajout d'ouvrage par les contributeurs : stockage à part, doublons, retrait */
class ProposalTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return $overrides + [
            'name'                 => 'Un roman proposé',
            'type'                 => 'roman',
            'support'              => 'papier',
            'format'               => 'inconnu',
            'approximate_parution' => '2020-05-00',
            'is_genre'             => 'oui',
            'genre_stat'           => 'sf',
            'target_audience'      => 'inconnu',
            'isbn'                 => '978-2-9999-0001-1',
            'publisher_name'       => 'Un éditeur non référencé',
            'author1_name'         => 'Dupont',
            'author1_first_name'   => 'Jean',
            'private'              => 'Source : test',
        ];
    }

    public function test_proposal_is_stored_apart_and_not_in_publications(): void
    {
        $user = User::factory()->role(UserRole::PROPONENT)->create();

        $this->actingAs($user)->post('/admin/formulaires/proposer-publication', $this->validData())
            ->assertSessionHasNoErrors()->assertRedirect('/admin/formulaires/proposer-publication');

        $proposal = Proposal::first();
        $this->assertSame($user->id, $proposal->user_id);
        $this->assertSame(ProposalStatus::SUBMITTED, $proposal->status);
        $this->assertSame('Un roman proposé', $proposal->payload['titre']);
        $this->assertSame('Un éditeur non référencé', $proposal->payload['editeur']);
        // Sommaire réduit au contenu principal, même titre que le livre
        $this->assertSame('Un roman proposé', $proposal->payload['sommaire'][0]['titre']);
        $this->assertSame(0, Publication::count());
    }

    public function test_same_book_already_proposed_is_refused(): void
    {
        $user = User::factory()->role(UserRole::PROPONENT)->create();

        $this->actingAs($user)->post('/admin/formulaires/proposer-publication', $this->validData());
        $this->post('/admin/formulaires/proposer-publication', $this->validData(['name' => 'Autre titre']))
            ->assertSessionHasErrors('doublon');

        $this->assertSame(1, Proposal::count());
    }

    public function test_future_book_and_missing_publisher_are_refused(): void
    {
        $user = User::factory()->role(UserRole::PROPONENT)->create();

        $this->actingAs($user)->post('/admin/formulaires/proposer-publication', $this->validData(['approximate_parution' => '2999-01-01']))
            ->assertSessionHasErrors('approximate_parution');
        $this->post('/admin/formulaires/proposer-publication', $this->validData(['publisher_name' => '']))
            ->assertSessionHasErrors('publisher_name');

        $this->assertSame(0, Proposal::count());
    }

    public function test_only_the_author_can_withdraw_a_pending_proposal(): void
    {
        $author = User::factory()->role(UserRole::PROPONENT)->create();
        $other = User::factory()->role(UserRole::PROPONENT)->create();

        $this->actingAs($author)->post('/admin/formulaires/proposer-publication', $this->validData());
        $proposal = Proposal::first();

        $this->actingAs($other)->post("/admin/formulaires/proposer-publication/{$proposal->id}/retirer")->assertForbidden();
        $this->actingAs($author)->post("/admin/formulaires/proposer-publication/{$proposal->id}/retirer")->assertRedirect();

        $this->assertSame(ProposalStatus::WITHDRAWN, $proposal->fresh()->status);
    }
}

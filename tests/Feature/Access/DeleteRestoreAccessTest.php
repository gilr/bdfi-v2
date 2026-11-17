<?php

namespace Tests\Feature\Access;

use App\Enums\UserRole;
use App\Models\Author;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use ReflectionMethod;

/**
 * Suppression (soft-delete) et restauration.
 *
 * Tables non internes :
 *   Invité, Contributeur : ni D ni R
 *   Membre            : d = supprimer si créateur et personne d'autre n'a modifié depuis
 *                       r = restaurer si on est celui qui a supprimé
 *   Admin, Sysadmin   : D et R sans condition
 * Tables internes (pays, types de site, types de lien, utilisateurs) : D et R réservés aux admins.
 *
 * Le forceDelete est couvert par ForceDeleteAccessTest.
 */
class DeleteRestoreAccessTest extends AccessTestCase
{
    private const INTERNAL_POLICIES = ['CountryPolicy', 'WebsiteTypePolicy', 'RelationshipTypePolicy', 'UserPolicy'];

    /** @var array<string, User> */
    private array $users;
    private User $other;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (UserRole::cases() as $role) {
            $this->users[$role->value] = User::factory()->role($role)->create();
        }
        $this->other = User::factory()->role(UserRole::MEMBER)->create();
    }

    public function test_non_internal_tables_follow_the_matrix(): void
    {
        $failures = [];
        foreach ($this->policies(internal: false) as $policyClass => $modelClass) {
            $policy = new $policyClass();
            foreach ($this->users as $role => $user) {
                $isAdmin = in_array($role, [UserRole::ADMIN->value, UserRole::SYSADMIN->value], true);
                $isMember = $role === UserRole::MEMBER->value;

                $cases = [
                    // [description, action, attributs, attendu]
                    ['fiche d\'un autre',            'delete',  ['created_by' => $this->other->id, 'updated_by' => $this->other->id], $isAdmin],
                    ['sa fiche non modifiée',        'delete',  ['created_by' => $user->id, 'updated_by' => $user->id], $isAdmin || $isMember],
                    ['sa fiche modifiée par autre',  'delete',  ['created_by' => $user->id, 'updated_by' => $this->other->id], $isAdmin],
                    ['supprimée par un autre',       'restore', ['deleted_by' => $this->other->id], $isAdmin],
                    ['supprimée par lui-même',       'restore', ['deleted_by' => $user->id], $isAdmin || $isMember],
                ];
                foreach ($cases as [$label, $action, $attributes, $expected]) {
                    $record = $this->makeRecord($modelClass, $attributes);
                    $allowed = (bool) $policy->$action($user, $record);
                    if ($allowed !== $expected) {
                        $failures[] = sprintf('%s::%s (%s) : rôle %s -> %s', class_basename($policyClass), $action, $label, $role, $allowed ? 'autorisé' : 'refusé');
                    }
                }
            }
        }
        $this->assertSame([], $failures, "Écarts avec la matrice des droits :\n" . implode("\n", $failures));
    }

    public function test_internal_tables_are_reserved_to_admins(): void
    {
        $failures = [];
        foreach ($this->policies(internal: true) as $policyClass => $modelClass) {
            $policy = new $policyClass();
            foreach ($this->users as $role => $user) {
                $expected = in_array($role, [UserRole::ADMIN->value, UserRole::SYSADMIN->value], true);
                $record = $this->makeRecord($modelClass, ['created_by' => $user->id, 'updated_by' => $user->id, 'deleted_by' => $user->id]);
                foreach (['delete', 'restore'] as $action) {
                    $allowed = (bool) $policy->$action($user, $record);
                    if ($allowed !== $expected) {
                        $failures[] = sprintf('%s::%s : rôle %s -> %s', class_basename($policyClass), $action, $role, $allowed ? 'autorisé' : 'refusé');
                    }
                }
            }
        }
        $this->assertSame([], $failures, "Tables internes :\n" . implode("\n", $failures));
    }

    /** Une modification par un autre utilisateur, visible dans l'historique, retire le droit « d » au créateur. */
    public function test_member_cannot_delete_own_record_edited_by_someone_else(): void
    {
        $member = $this->users[UserRole::MEMBER->value];

        $this->actingAs($member);
        $author = Author::create(['name' => 'Test']);
        $this->assertTrue($member->can('delete', $author->fresh()));

        $this->actingAs($this->other);
        $author->update(['name' => 'Test modifié']);
        // Le créateur se remet en dernier modificateur : seul l'historique trahit la modification de l'autre
        $this->actingAs($member);
        $author->update(['name' => 'Test re-modifié']);

        $this->assertSame($member->id, (int) $author->fresh()->updated_by);
        $this->assertFalse($member->can('delete', $author->fresh()));
        $this->assertTrue($this->users[UserRole::ADMIN->value]->can('delete', $author->fresh()));
    }

    /** Une fiche supprimée par un membre peut être restaurée par lui, pas par un autre membre. */
    public function test_member_can_restore_only_what_he_deleted(): void
    {
        $member = $this->users[UserRole::MEMBER->value];

        $this->actingAs($member);
        $author = Author::create(['name' => 'Test']);
        $author->delete();
        $trashed = Author::withTrashed()->find($author->id);

        $this->assertSame($member->id, (int) $trashed->deleted_by);
        $this->assertTrue($member->can('restore', $trashed));
        $this->assertFalse($this->other->can('restore', $trashed));
    }

    /** @return array<class-string, class-string<Model>> policy => modèle */
    private function policies(bool $internal): array
    {
        $result = [];
        foreach (glob(app_path('Policies/*Policy.php')) as $file) {
            $name = basename($file, '.php');
            if (in_array($name, self::INTERNAL_POLICIES, true) !== $internal) {
                continue;
            }
            $class = 'App\\Policies\\' . $name;
            $result[$class] = (new ReflectionMethod($class, 'delete'))->getParameters()[1]->getType()->getName();
        }
        $this->assertNotEmpty($result);
        return $result;
    }

    private function makeRecord(string $modelClass, array $attributes): Model
    {
        $record = new $modelClass();
        $record->forceFill($attributes);
        return $record;
    }
}

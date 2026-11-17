<?php

namespace Tests\Feature\Access;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Str;
use ReflectionMethod;

/**
 * Suppression définitive (forceDelete).
 *
 * Règle : sur toutes les tables, seul le sysadmin peut détruire définitivement des données.
 * Les policies sont lues dynamiquement : toute nouvelle policy est automatiquement couverte.
 */
class ForceDeleteAccessTest extends AccessTestCase
{
    public function test_only_sysadmin_can_force_delete(): void
    {
        $users = [];
        foreach (UserRole::cases() as $role) {
            $users[$role->value] = User::factory()->role($role)->create();
        }

        $failures = [];
        foreach (glob(app_path('Policies/*Policy.php')) as $file) {
            $class = 'App\\Policies\\' . basename($file, '.php');
            if (!method_exists($class, 'forceDelete')) {
                continue;
            }
            $modelClass = (new ReflectionMethod($class, 'forceDelete'))->getParameters()[1]->getType()?->getName()
                ?? 'App\\Models\\' . Str::beforeLast(class_basename($class), 'Policy');
            $policy = new $class();

            foreach ($users as $role => $user) {
                $allowed = (bool) $policy->forceDelete($user, new $modelClass());
                if ($allowed !== ($role === UserRole::SYSADMIN->value)) {
                    $failures[] = sprintf('%s : rôle %s -> %s', class_basename($class), $role, $allowed ? 'autorisé' : 'refusé');
                }
            }
        }

        $this->assertSame([], $failures,
            "forceDelete doit être réservé au sysadmin :\n" . implode("\n", $failures));
    }
}

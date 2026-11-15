<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Base commune des tests d'accès par zone (user / admin / filament).
 *
 * Les routes sont lues dynamiquement depuis le routeur : toute nouvelle route
 * ajoutée sous /user, /admin ou /filament est automatiquement couverte.
 *
 * Une requête est considérée comme REFUSÉE si elle aboutit à :
 *   - une redirection vers une page de connexion (non connecté),
 *   - une redirection vers l'accueil '/' (middleware auth.bdfiadmin),
 *   - un code 401 ou 403 (ex. Filament via canAccessPanel()).
 * Tout le reste est considéré comme un ACCÈS (y compris une erreur de validation,
 * un 404 sur un enregistrement bidon... : la porte est ouverte).
 */
abstract class AccessTestCase extends TestCase
{
    use RefreshDatabase;

    /** Routes des zones protégées qui restent accessibles à tous (non connectés compris) */
    protected const PUBLIC_EXCEPTIONS = [
        'filament/login',
    ];

    /**
     * Routes techniques exclues des tests : hors panneau Filament (middleware 'web' + 'auth' seul),
     * liées aux actions Export/Import de Filament non utilisées par BDFI (pas de tables exports/imports),
     * et protégées par ailleurs : seul le propriétaire d'un export/import peut le télécharger.
     */
    protected const TECHNICAL_EXCLUSIONS = [
        'filament/exports/{export}/download',
        'filament/imports/{import}/failed-rows/download',
    ];

    /** Page d'origine fictive envoyée en Referer */
    private const ORIGIN_URI = '/__origine-test__';

    /** Vérifie que toutes les routes (toutes méthodes) d'une zone sont refusées. */
    protected function assertZoneIsDenied(string $zone): void
    {
        $failures = $this->checkZone($zone, fn (TestResponse $r) => $this->isDenied($r));

        $this->assertSame([], $failures,
            "Zone '$zone' : routes accessibles alors qu'elles devraient être refusées :\n" . implode("\n", $failures));
    }

    /** Vérifie qu'aucune route (toutes méthodes) d'une zone n'est refusée, ni en erreur serveur. */
    protected function assertZoneIsGranted(string $zone): void
    {
        $failures = $this->checkZone($zone,
            fn (TestResponse $r) => !$this->isDenied($r) && $r->getStatusCode() < 500);

        $this->assertSame([], $failures,
            "Zone '$zone' : routes refusées (ou en erreur 5xx) alors qu'elles devraient être accessibles :\n" . implode("\n", $failures));
    }

    /**
     * Appelle toutes les routes d'une zone et renvoie la liste de celles qui ne
     * satisfont pas le critère $isExpected. Les échecs sont cumulés pour avoir
     * la liste complète en une seule exécution.
     */
    private function checkZone(string $zone, callable $isExpected): array
    {
        $routes = $this->routesOfZone($zone);
        $this->assertNotEmpty($routes, "Aucune route trouvée pour la zone '$zone'.");

        $failures = [];
        foreach ($routes as $route) {
            $uri = preg_replace('/\{[^}]+\}/', '1', $route->uri()); // paramètres bidon
            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                // Referer distinct de '/' : un "redirect back" (échec de validation) ne doit pas
                // être confondu avec le refus d'auth.bdfiadmin, qui redirige vers '/'.
                $response = $this->from(self::ORIGIN_URI)->call($method, $uri);
                if (!$isExpected($response)) {
                    $location = $response->headers->get('Location');
                    $failures[] = sprintf('%-6s /%s -> %d%s', $method, $uri,
                        $response->getStatusCode(), $location ? " ($location)" : '');
                }
            }
        }
        return $failures;
    }

    protected function isDenied(TestResponse $response): bool
    {
        if (in_array($response->getStatusCode(), [401, 403], true)) {
            return true;
        }
        $deniedRedirects = [route('login'), url('/filament/login'), url('/')];

        return $response->isRedirect()
            && in_array($response->headers->get('Location'), $deniedRedirects, true);
    }

    /** @return Route[] */
    private function routesOfZone(string $zone): array
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $r) => $r->uri() === $zone || str_starts_with($r->uri(), "$zone/"))
            ->reject(fn (Route $r) => in_array($r->uri(), self::PUBLIC_EXCEPTIONS, true)
                || in_array($r->uri(), self::TECHNICAL_EXCLUSIONS, true))
            ->values()
            ->all();
    }
}

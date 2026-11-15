<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LivewireTraceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
    if (!($request->is('livewire/update') && $request->isMethod('post'))) {
        return $next($request);
    }

    $ua = $request->userAgent() ?? '';
    $isBot = str_contains(strtolower($ua), 'python-requests');

    $components = $request->input('components', []);

    $excludedComponents = [
        'PublicationCarousel',
        'AuthorSearch',
        'CollectionSearch',
        'PublisherSearch',
        'PublicationSearch',
        'TitleSearch',
        'CoverSlide',
        'CoverZoom',
        'CollapsibleBlock',
        'StatsOverview',
        'StatsV1Chart',
        'PublicationsByFormatChart',
        'PublicationsByGenreChart',
        'PublicationsBySupportChart',
        'DynamicSelect',
    ];

    foreach ($components as $component) {

        $snapshot = $component['snapshot'] ?? [];

        if (is_string($snapshot)) {
            $snapshot = json_decode($snapshot, true) ?? [];
        }

        $componentName = $snapshot['memo']['name'] ?? '?';

        $updates = $component['updates'] ?? [];
        $calls   = $component['calls'] ?? [];

        // On ignore les composants bruyants,
        // sauf si la requête vient de python-requests.
        if (!$isBot && in_array($componentName, $excludedComponents, true)) {
            continue;
        }

        $hasUpdates = !empty($updates);
        $hasCalls   = !empty($calls);

        // Requête intéressante :
        // - python-requests
        // - modification de propriété
        // - appel de méthode
        if ($isBot || $hasUpdates || $hasCalls) {

            Log::channel('livewire')->warning('LW TRACE', [
                'ip'        => $request->ip(),
                'ua'        => $ua,
                'component' => $componentName,
                'updates'   => $updates,
                'calls'     => $calls,
            ]);
        }
    }

    return $next($request);
    }
}

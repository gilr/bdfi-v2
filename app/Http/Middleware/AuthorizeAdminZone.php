<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserRole;

class AuthorizeAdminZone
{
    /**
     * Accès à la zone admin.
     *
     *   auth.bdfiadmin        : zone de contribution (accueil, formulaires, proposition, rapports),
     *                           ouverte à tous sauf les USER simples (contributeur et plus)
     *   auth.bdfiadmin:guest  : reste de la zone admin, invité et plus (refusé au contributeur)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ?string $level = null)
    {
        $user = $request->user();

        # Refuser les USER simples
        if (!$user || $user->role->value === UserRole::USER->value) {
            return redirect('/');
        }
        # Zone réservée invité et plus : refuser le contributeur
        if ($level === 'guest' && !$user->hasGuestRole()) {
            abort(403);
        }
        return $next($request);
    }
}

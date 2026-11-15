<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserRole;

class AuthorizeAdminZone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        # Refuser les USER simples (tous les autres, de GUEST à SYSADMIN ont un accès - mais peuvent se voir refuser certaines fonctions)
        if (!$user || $user->role->value === UserRole::USER->value) {
            return redirect('/');
        }
        return $next($request);
    }
}

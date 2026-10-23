<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Staff accounts may use ONLY the POS and the Orders (commissions) modules,
 * plus their own profile and logout. Any other route is blocked - a GET
 * navigation is redirected to the POS, everything else is a 403. This runs
 * regardless of the sidebar, so a Staff user cannot reach a hidden page by
 * typing its URL.
 */
class StaffScope
{
    protected array $allowedPrefixes = ['pos.', 'commissions.', 'profile.'];
    protected array $allowedExact    = ['logout', 'welcome'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->role === 'Staff') {
            $name = optional($request->route())->getName() ?? '';

            $ok = in_array($name, $this->allowedExact, true);
            if (! $ok) {
                foreach ($this->allowedPrefixes as $prefix) {
                    if (str_starts_with($name, $prefix)) { $ok = true; break; }
                }
            }

            if (! $ok) {
                if ($request->isMethod('GET') && RouteFacade::has('pos.index')) {
                    return redirect()->route('pos.index');
                }
                abort(403, 'Staff accounts can only use POS and Orders.');
            }
        }

        return $next($request);
    }
}
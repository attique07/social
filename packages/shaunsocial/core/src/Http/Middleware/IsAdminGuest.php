<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdminGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->isModerator()) {
            return $next($request);
        }

        return redirect()->route('admin.dashboard.index');
    }
}

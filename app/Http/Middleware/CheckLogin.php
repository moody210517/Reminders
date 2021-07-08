<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Auth;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //if ($request->age <= 200) {

        $url = $request->url();
        if (strpos($url, 'office/password') !== false) {
            return $next($request);
        }

        if (strpos($url, 'admin/cron') !== false) {
            return $next($request);
        }

        if (strpos($url, 'welcome') !== false) {
            return $next($request);
        }

        if (strpos($url, 'test') !== false) {
            return $next($request);
        }

        if (strpos($url, 'api') !== false) {
            return $next($request);
        }

        if (Auth::user() == null  || Auth::user() == "") {            
            return redirect('/');
        }
        
        return $next($request);
    }
}

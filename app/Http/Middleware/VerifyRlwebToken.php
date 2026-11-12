<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRlwebToken
{
    /**
     * Solange RLAPP_API_TOKEN leer ist, bleiben die bisherigen Aufrufe von rlweb gültig.
     * Ist es gesetzt, muss es als Header X-RL-Token oder Formularfeld rl_token mitkommen.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.rlweb.token');
        if ($secret === '') {
            return $next($request);
        }

        $header = $request->header('X-RL-Token');
        $given = (string) (($header !== null && $header !== '') ? $header : $request->input('rl_token', ''));
        if (!hash_equals($secret, $given)) {
            abort(403);
        }

        return $next($request);
    }
}

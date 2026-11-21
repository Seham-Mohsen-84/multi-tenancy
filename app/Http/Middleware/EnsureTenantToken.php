<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\JWT;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        abort_unless($token, 401);

        $jwt = app(JWT::class);

        try {
            try {
                $payload = $jwt->setToken($token)->check(true);
            } catch (JWTException) {
                abort(401);
            }

            abort_unless($payload && $payload->get('tenant_id') === tenant()->getTenantKey(), 401);

            return $next($request);
        } finally {
            $jwt->unsetToken();
        }
    }
}

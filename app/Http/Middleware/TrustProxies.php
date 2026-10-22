<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Proxies de confianza desde TRUSTED_PROXIES (config app.trusted_proxies):
     * '*' o una lista separada por comas. Sin la variable, se usa $proxies (null).
     */
    protected function proxies()
    {
        return static::$alwaysTrustProxies ?: (config('app.trusted_proxies') ?? $this->proxies);
    }
}

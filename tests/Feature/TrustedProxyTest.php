<?php

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/_test/proxy', fn (Request $request) => response()->json([
        'secure' => $request->isSecure(),
        'host' => $request->getHost(),
        'ip' => $request->ip(),
        'login' => route('login'),
    ]));
});

afterEach(function () {
    TrustProxies::flushState();
    Request::setTrustedProxies([], -1);
});

test('direct local requests ignore spoofed forwarding headers by default', function () {
    config(['trustedproxy.proxies' => []]);

    $this->withServerVariables(['REMOTE_ADDR' => '10.2.0.4'])
        ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '203.0.113.50'])
        ->get('http://scholarmatch.test/_test/proxy')
        ->assertOk()
        ->assertJsonPath('secure', false)
        ->assertJsonPath('ip', '10.2.0.4')
        ->assertJsonPath('login', 'http://scholarmatch.test/login');
});

test('configured Azure ingress produces HTTPS links without trusting forwarded hosts', function () {
    config(['trustedproxy.proxies' => '*']);

    $this->withServerVariables(['REMOTE_ADDR' => '10.2.0.4'])
        ->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-For' => '203.0.113.50',
            'X-Forwarded-Host' => 'attacker.invalid',
            'X-Forwarded-Prefix' => '/unexpected',
        ])
        ->get('http://scholarmatch.test/_test/proxy')
        ->assertOk()
        ->assertJsonPath('secure', true)
        ->assertJsonPath('host', 'scholarmatch.test')
        ->assertJsonPath('ip', '203.0.113.50')
        ->assertJsonPath('login', 'https://scholarmatch.test/login');
});

test('explicit proxy ranges accept only requests from those proxies', function (string $remote, bool $secure) {
    config(['trustedproxy.proxies' => ['10.2.0.0/24']]);

    $this->withServerVariables(['REMOTE_ADDR' => $remote])
        ->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('http://scholarmatch.test/_test/proxy')
        ->assertOk()
        ->assertJsonPath('secure', $secure);
})->with([
    'trusted proxy' => ['10.2.0.4', true],
    'untrusted client' => ['192.0.2.10', false],
]);

test('Azure guest redirects use HTTPS', function () {
    config(['trustedproxy.proxies' => '*']);

    $this->withServerVariables(['REMOTE_ADDR' => '10.2.0.4'])
        ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Port' => '443'])
        ->get('http://scholarmatch.test/student/dashboard')
        ->assertRedirect('https://scholarmatch.test/login');
});

test('production session cookies remain secure behind Azure ingress', function () {
    config(['trustedproxy.proxies' => '*', 'session.secure' => true]);

    $response = $this->withServerVariables(['REMOTE_ADDR' => '10.2.0.4'])
        ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Port' => '443'])
        ->get('http://scholarmatch.test/login');

    $response->assertOk();
    $cookie = collect($response->headers->getCookies())->first(
        fn ($cookie) => $cookie->getName() === config('session.cookie'),
    );
    expect($cookie)->not->toBeNull();
    expect($cookie->isSecure())->toBeTrue();
});

<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ShadeTheory\GithubClient\Auth\GitHubAppAuthentication;
use ShadeTheory\GithubClient\Exceptions\AuthenticationException;
use ShadeTheory\GithubClient\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;

$key = null;

beforeAll(function () use (&$key) {
    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
});

beforeEach(function () use (&$key) {
    openssl_pkey_export($key, $this->privateKey);
    $this->publicKey = openssl_pkey_get_details($key)['key'];
});

it('exchanges an app JWT for an installation token and reuses it for repository requests', function (bool $base64) {
    $github = Github::withApp('12345', $base64 ? base64_encode($this->privateKey) : $this->privateKey, 67890);
    $repositoryResponse = function (PendingRequest $request) {
        expect($request->getUrl())->toBe('https://api.github.com/repos/owner/repo')
            ->and($request->headers()->get('Authorization'))->toBe('Bearer installation-token');

        return MockResponse::make($this->createMockRepoData('repo', 1, 'owner'));
    };
    $mock = new MockClient([
        function (PendingRequest $request) {
            $header = $request->headers()->get('Authorization');
            $claims = JWT::decode(substr($header, 7), new Key($this->publicKey, 'RS256'));

            expect($request->getUrl())->toBe('https://api.github.com/app/installations/67890/access_tokens')
                ->and($claims->iss)->toBe('12345')
                ->and($claims->exp - $claims->iat)->toBe(600);

            return MockResponse::make([
                'token' => 'installation-token',
                'expires_at' => gmdate('c', time() + 3600),
            ], 201);
        },
        $repositoryResponse,
        $repositoryResponse,
    ]);
    $github->connector()->withMockClient($mock);

    expect($github->getRepo('owner/repo')->full_name)->toBe('owner/repo')
        ->and($github->getRepo('owner/repo')->name)->toBe('repo');
})->with(['PEM key' => false, 'base64 key' => true]);

it('can retry an installation exchange after GitHub rejects it', function () {
    $github = Github::withApp('12345', $this->privateKey, 67890);
    $github->connector()->withMockClient(new MockClient([
        MockResponse::make(['message' => 'Bad credentials'], 401),
        MockResponse::make([
            'token' => 'replacement-token',
            'expires_at' => gmdate('c', time() + 3600),
        ], 201),
        MockResponse::make($this->createMockRepoData('repo', 1, 'owner')),
    ]));

    expect(fn () => $github->getRepo('owner/repo'))->toThrow(AuthenticationException::class)
        ->and($github->getRepo('owner/repo')->name)->toBe('repo');
});

it('refreshes installation tokens before expiry but keeps sufficiently fresh tokens', function (int $seconds, bool $needsRefresh) {
    $auth = new GitHubAppAuthentication('12345', $this->privateKey, '67890');
    $auth->setInstallationToken('cached-token', new DateTimeImmutable("{$seconds} seconds"));

    expect($auth->needsRefresh())->toBe($needsRefresh);
})->with([
    'expired' => [-60, true],
    'inside five-minute buffer' => [240, true],
    'outside five-minute buffer' => [360, false],
]);

it('uses app JWTs to list installations across pages without an installation token', function () {
    $github = Github::withApp('12345', $this->privateKey);
    $github->connector()->withMockClient(new MockClient([
        'https://api.github.com/app/installations*' => function (PendingRequest $request) {
            $claims = JWT::decode(substr($request->headers()->get('Authorization'), 7), new Key($this->publicKey, 'RS256'));
            expect($claims->iss)->toBe('12345');

            $page = $request->query()->get('page');

            return MockResponse::make([
                ['id' => $page, 'account' => ['login' => "account-{$page}", 'type' => 'Organization']],
            ], 200, $page === 1 ? ['Link' => '<https://api.github.com/app/installations?page=2>; rel="next"'] : []);
        },
    ]));

    $installations = $github->installations()->listAll();

    expect(array_column($installations, 'account_login'))->toBe(['account-1', 'account-2']);
});

<?php

use JordanPartridge\GithubClient\Resources\BaseResource;

describe('General', function () {
    it('will not use debugging functions', function () {
        expect(['dd', 'dump', 'ray'])->each->not->toBeUsed();
    });
});
describe('Resources', function () {
    arch('resources extend the base resource', function () {
        expect('JordanPartridge\GithubClient\Response')->toExtend(BaseResource::class);
    });
});

describe('Requests', function () {
    arch('requests extend the base resource', function () {
        expect('JordanPartridge\GithubClient\Requests')->toExtend(\Saloon\Http\Request::class);
    });
});


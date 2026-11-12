<?php

use ShadeTheory\GithubClient\Resources\BaseResource;
use Saloon\Http\Request;

describe('General', function () {
    it('will not use debugging functions', function () {
        expect(['dd', 'dump', 'ray'])->each->not->toBeUsed();
    });
});
describe('Resources', function () {
    arch('resources extend the base resource', function () {
        expect('ShadeTheory\GithubClient\Resources')->toExtend(BaseResource::class);
    });
});

describe('Requests', function () {
    arch('requests extend the base resource', function () {
        expect('ShadeTheory\GithubClient\Requests')->toExtend(Request::class);
    });
});

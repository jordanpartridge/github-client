<?php

use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;

uses(ValidatesRepoName::class);

it('allows valid organization/repository format', function () {
    expect($this->validateRepoName('organization/repo-name'))->not()->toThrow(InvalidArgumentException::class);
    expect($this->validateRepoName('user/my-repo.js'))->not()->toThrow(InvalidArgumentException::class);
    expect($this->validateRepoName('company/project_name'))->not()->toThrow(InvalidArgumentException::class);
});

it('rejects repository paths without organization', function () {
    expect(fn() => $this->validateRepoName('simple-repo'))
        ->toThrow(InvalidArgumentException::class, 'Repository path must be in the format "owner/repository"');
});

it('rejects paths with invalid organization names', function () {
    expect(fn() => $this->validateRepoName('.org/repo-name'))
        ->toThrow(InvalidArgumentException::class);
    expect(fn() => $this->validateRepoName('-org/repo-name'))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects repository names that start with dots', function () {
    expect(fn() => $this->validateRepoName('org/.repo-name'))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects repository names that end with dots', function () {
    expect(fn() => $this->validateRepoName('org/repo-name.'))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects repository names with consecutive dots', function () {
    expect(fn() => $this->validateRepoName('org/repo..name'))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects paths that exceed maximum length', function () {
    $longName = str_repeat('a', 40) . '/repo';
    expect(fn() => $this->validateRepoName($longName))
        ->toThrow(InvalidArgumentException::class);
});

it('accepts paths that are exactly the maximum length', function () {
    $maxLength = str_repeat('a', 39) . '/repo';
    expect($this->validateRepoName($maxLength))->not()->toThrow(InvalidArgumentException::class);
});

it('will not allow repository names that are too long', function () {
    $longName = str_repeat('a', 101);
    expect(fn() => $this->validateRepoName('org/' . $longName))
        ->toThrow(InvalidArgumentException::class);
});

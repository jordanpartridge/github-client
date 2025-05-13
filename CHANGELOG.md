# Changelog

All notable changes to `github-client` will be documented in this file.

## Unreleased

### Laravel 12 Support

* Added Laravel 12 compatibility
* Updated PHP requirements to support PHP 8.2, 8.3, and 8.4
* Updated development dependencies to support the latest testing tools
* Fixed dependency conflicts between Pest 3.0 and Collision

## v0.2.1 - Commit by Sha added - 2024-11-26

### Release v0.2.1: Individual Commit Access

#### New Features

* Added support for retrieving individual commits by SHA

```php
GitHub::commits()->get('sha-hash');

```
#### Details

The new commit endpoint provides authenticated access to detailed commit information including:

- Commit metadata (author, committer, dates)
- Full commit message
- File changes and patches
- Verification information
- Associated repository data
- Parent commit references

#### Usage Example

```php
$commit = GitHub::commits()->get('abc123def456...');
// Access commit data
$author = $commit->author;
$message = $commit->message;
$changes = $commit->files;

```
#### Pull Requests

* [#10](https://github.com/jordanpartridge/github-client/pull/10) - Add individual commit retrieval by @jordanpartridge

**Full Changelog**: https://github.com/jordanpartridge/github-client/compare/v0.2...v0.2.1

## v0.2 Repo Dto - 2024-11-25

### What's Changed

* feature/commits by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/8
* repo uses dto by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/9

**Full Changelog**: https://github.com/jordanpartridge/github-client/compare/v0.1a...v0.2

## 0.1a Repository built out ready for alpha testing - 2024-11-02

### What's Changed

* repos implemented by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/1
* fix phpstan by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/4
* add visability enum by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/5
* Refactor repo by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/7

### New Contributors

* @jordanpartridge made their first contribution in https://github.com/jordanpartridge/github-client/pull/1

**Full Changelog**: https://github.com/jordanpartridge/github-client/commits/0.1a

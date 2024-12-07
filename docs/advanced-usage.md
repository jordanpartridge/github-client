# Advanced Usage

## Custom Configuration

### Publishing Configuration

```bash
php artisan vendor:publish --tag="github-client-config"
```

### Custom Token Handling

```php
config([
    'github-client.token' => resolve(TokenProvider::class)->getToken(),
]);
```

## Rate Limiting

### Automatic Rate Limiting

```php
GitHub::repos()
    ->withRateLimiting()
    ->get('owner/repo');
```

### Custom Rate Limiting

```php
GitHub::repos()
    ->withRateLimiting([
        'max_requests' => 5000,
        'per_seconds' => 3600,
    ])
    ->get('owner/repo');
```

## Error Handling

### Catching Specific Exceptions

```php
try {
    $repo = GitHub::repos()->get('owner/repo');
} catch (NotFoundException $e) {
    // Handle 404
} catch (RateLimitExceededException $e) {
    // Handle rate limit
} catch (GitHubException $e) {
    // Handle other exceptions
}
```

## Testing

### Mocking Responses

```php
use JordanPartridge\GithubClient\Tests\Factories\RepositoryFactory;

$repo = RepositoryFactory::new()->create();

GitHub::fake([
    'repos/owner/repo' => $repo,
]);
```

## Extending

### Custom Resources

```php
class CustomResource extends Resource
{
    public function customMethod(): mixed
    {
        return $this->connector
            ->send(new CustomRequest())
            ->throw()
            ->json();
    }
}
```

### Custom Requests

```php
class CustomRequest extends Request
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/custom/endpoint';
    }
}
```

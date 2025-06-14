# API Reference

## Core Classes

### GitHub Client

```php
use JordanPartridge\GithubClient\Facades\Github;
```

## Available Methods

### Repositories

#### all()
```php
Github::repos()->all(): Collection
```

#### get()
```php
Github::repos()->get(string $repository): Repository
```

### Commits

#### all()
```php
Github::commits()->all(string $repository): Collection
```

#### get()
```php
Github::commits()->get(string $sha): Commit
```

## Response Types

### Repository
```php
class Repository extends Data
{
    public string $name;
    public string $full_name;
    public string $description;
    // ...
}
```

### Commit
```php
class Commit extends Data
{
    public string $sha;
    public string $message;
    public array $parents;
    // ...
}
```

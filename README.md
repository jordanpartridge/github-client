
# Laravel DTO Generator

A Laravel package for generating Data Transfer Objects (DTOs) from JSON schemas.

## Installation

```bash
composer require jordanpartridge/laravel-dto-generator --dev
```

## Usage

1. Prepare your JSON schema file representing the DTO structure.

2. Run the `dto:generate` command with the path to your schema file:

```bash
php artisan dto:generate path/to/schema.json
```

3. The generated DTO class will be output to the console. You can copy and paste it into your project.

## Supported APIs

This package is tailored to generate DTOs for the following APIs:

- GitHub API
- WakaTime API  
- Packagist API

## Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://opensource.org/licenses/MIT)

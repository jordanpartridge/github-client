
<?php

namespace JordanPartridge\DtoGenerator\Tests;

use Illuminate\Support\Facades\File;
use JordanPartridge\DtoGenerator\Facades\DtoGenerator;
use Orchestra\Testbench\TestCase;

class GenerateDtoCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['JordanPartridge\DtoGenerator\DtoGeneratorServiceProvider'];
    }

    public function test_it_generates_dto_from_schema()
    {
        // Create a temporary JSON schema file
        $schema = [
            '$id' => 'https://example.com/user.schema.json',
            'title' => 'User',
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
                'email' => ['type' => 'string'],
            ],
        ];
        File::put($schemaPath = __DIR__.'/user.schema.json', json_encode($schema));

        // Run the dto:generate command
        $this->artisan('dto:generate', ['schema' => $schemaPath])
            ->expectsOutput('DTO generated successfully:')
            ->assertExitCode(0);

        // Assert the generated DTO
        $expectedDto = '&lt;?php

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * This DTO was automatically generated from a JSON schema.
 * @see https://example.com/user.schema.json
 */
class UserDto extends DataTransferObject
{
    public int $id;
    public string $name;
    public string $email;
}
';
        $this->assertEquals($expectedDto, DtoGenerator::generate($schema));

        // Clean up
        File::delete($schemaPath);
    }
}

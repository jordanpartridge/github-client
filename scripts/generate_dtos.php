<?php

$schema = [
    'Repository' => [
        'fields' => [
            'id' => ['type' => 'int'],
            'node_id' => ['type' => 'string', 'nullable' => true],
            'name' => ['type' => 'string'],
            'full_name' => ['type' => 'string'],
            'owner' => ['type' => 'User'],
            'private' => ['type' => 'bool'],
            'description' => ['type' => 'string', 'nullable' => true],
            'fork' => ['type' => 'bool'],
            'language' => ['type' => 'string', 'nullable' => true],
            'default_branch' => ['type' => 'string'],
            'topics' => ['type' => 'array', 'nullable' => true],
        ],
    ],
    'User' => [
        'fields' => [
            'id' => ['type' => 'int'],
            'login' => ['type' => 'string'],
            'node_id' => ['type' => 'string', 'nullable' => true],
            'avatar_url' => ['type' => 'string'],
            'url' => ['type' => 'string'],
            'type' => ['type' => 'string'],
            'site_admin' => ['type' => 'bool'],
        ],
    ],
    'File' => [
        'fields' => [
            'sha' => ['type' => 'string'],
            'filename' => ['type' => 'string'],
            'status' => ['type' => 'string'],
            'additions' => ['type' => 'int'],
            'deletions' => ['type' => 'int'],
            'changes' => ['type' => 'int'],
            'patch' => ['type' => 'string', 'nullable' => true],
        ],
    ],
    'Issue' => [
        'fields' => [
            'id' => ['type' => 'int'],
            'node_id' => ['type' => 'string', 'nullable' => true],
            'number' => ['type' => 'int'],
            'title' => ['type' => 'string'],
            'user' => ['type' => 'User'],
            'state' => ['type' => 'string'],
            'locked' => ['type' => 'bool'],
            'assignee' => ['type' => 'User', 'nullable' => true],
            'assignees' => ['type' => 'array'],
            'comments' => ['type' => 'int'],
            'created_at' => ['type' => 'string'],
            'updated_at' => ['type' => 'string'],
            'closed_at' => ['type' => 'string', 'nullable' => true],
        ],
    ],
];

function generateDTO($name, $schema)
{
    $fields = $schema['fields'];
    $constructor = [];

    foreach ($fields as $field => $info) {
        $type = $info['type'];
        if ($type === 'array') {
            $type = 'array';
        } elseif (in_array($type, ['User'])) {
            $type = '\\'.$type;
        }

        $nullable = $info['nullable'] ?? false;
        $type = $nullable ? "?$type" : $type;

        $defaultValue = $nullable ? ' = null' : '';
        $constructor[] = "        public readonly $type \$$field$defaultValue";
    }

    $constructorStr = implode(",\n", $constructor);

    return <<<PHP
<?php

namespace JordanPartridge\GithubClient\DTO;

use Spatie\LaravelData\Data;

class $name extends Data
{
    public function __construct(
$constructorStr
    ) {}
}
PHP;
}

function generateTest($name, $schema)
{
    $fields = $schema['fields'];
    $assertions = [];

    foreach ($fields as $field => $info) {
        if ($info['nullable'] ?? false) {
            $assertions[] = "        \$this->assertNull(\$dto->$field);";
        } else {
            $value = match ($info['type']) {
                'int' => '1',
                'string' => "'test'",
                'bool' => 'true',
                'array' => '[]',
                default => 'null'
            };
            $assertions[] = "        \$this->assertEquals($value, \$dto->$field);";
        }
    }

    $assertionsStr = implode("\n", $assertions);

    return <<<PHP
<?php

namespace Tests\Unit\DTO;

use Tests\TestCase;
use JordanPartridge\GithubClient\DTO\\$name;

class {$name}Test extends TestCase
{
    /** @test */
    public function it_can_create_{$name}_from_array()
    {
        \$data = [
            // TODO: Add test data
        ];

        \$dto = $name::from(\$data);
        
$assertionsStr
    }
}
PHP;
}

// Generate DTOs
@mkdir(__DIR__.'/../src/DTO', 0777, true);
@mkdir(__DIR__.'/../tests/Unit/DTO', 0777, true);

foreach ($schema as $name => $definition) {
    file_put_contents(
        __DIR__."/../src/DTO/$name.php",
        generateDTO($name, $definition)
    );

    file_put_contents(
        __DIR__."/../tests/Unit/DTO/{$name}Test.php",
        generateTest($name, $definition)
    );
}

echo "Generated DTOs and tests!\n";

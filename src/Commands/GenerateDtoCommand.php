
<?php

namespace JordanPartridge\DtoGenerator\Commands;

use Illuminate\Console\Command;
use JordanPartridge\DtoGenerator\Generators\DtoGenerator;

class GenerateDtoCommand extends Command
{
    protected $signature = 'dto:generate {schema : The path to the JSON schema file}';

    protected $description = 'Generate a DTO class from a JSON schema';

    public function handle(DtoGenerator $generator)
    {
        $schemaPath = $this->argument('schema');

        if (!file_exists($schemaPath)) {
            $this->error('JSON schema file not found: ' . $schemaPath);
            return 1;
        }

        $schema = json_decode(file_get_contents($schemaPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON schema: ' . json_last_error_msg());
            return 1;
        }

        $dto = $generator->generate($schema);

        $this->info('DTO generated successfully:');
        $this->line($dto);

        return 0;
    }
}

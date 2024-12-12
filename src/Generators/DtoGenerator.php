
<?php

namespace JordanPartridge\DtoGenerator\Generators;

use Illuminate\Support\Str;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

class DtoGenerator
{
    protected array $schema;

    public function generate(array $schema): string
    {
        $this->schema = $schema;

        $class = $this->generateClass();
        $this->addProperties($class);

        $phpFile = new PhpFile();
        $phpFile->addUse('Spatie\DataTransferObject\DataTransferObject');
        $phpFile->addClass($class);

        return (new PsrPrinter())->printFile($phpFile);
    }

    protected function generateClass(): ClassType
    {
        $class = new ClassType($this->getDtoClassName());
        $class->setExtends('DataTransferObject');
        $class->addComment('This DTO was automatically generated from a JSON schema.');
        $class->addComment('@see ' . $this->schema['$id']);

        return $class;
    }

    protected function addProperties(ClassType $class): void
    {
        foreach ($this->schema['properties'] as $name => $property) {
            $type = $this->mapType($property['type']);
            $class->addProperty($name)->setType($type);
        }
    }

    protected function getDtoClassName(): string
    {
        return Str::studly($this->schema['title']) . 'Dto';
    }

    protected function mapType(string $type): string
    {
        return match ($type) {
            'integer' => 'int',
            'number' => 'float',
            'boolean' => 'bool',
            default => $type,
        };
    }
}

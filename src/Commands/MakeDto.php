<?php

namespace Saleem\DataTransferObject\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Pluralizer;

class MakeDto extends Command implements PromptsForMissingInput
{
    /**
     * The signature and description of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto {name}';
    protected $description = 'Create a base DTO class';

    protected $files;

    protected const DTO_EXISTS_MESSAGE = 'DTO %sDto already exists!';
    protected const DTO_CREATED_MESSAGE = 'DTO %sDto created successfully. Please edit %s and add your own properties.';

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $path = $this->getSourceFilePath();
        $this->makeDirectory(dirname($path));

        if ($this->files->exists($path)) {
            $this->error(sprintf(self::DTO_EXISTS_MESSAGE, $name));
            return Command::FAILURE;
        }

        $this->createDto($path, $this->getSingularClassName($name), $this->getNameSpace());

        $this->info(sprintf(self::DTO_CREATED_MESSAGE, $name, $path));

        return Command::SUCCESS;
    }

    /**
     * Create the DTO file with the given name and namespace.
     *
     * @param string $filename The file to create.
     * @param string $name The DTO class name.
     * @param string $namespace The namespace for the DTO.
     * @return void
     */
    protected function createDto(string $filename, string $name, string $namespace): void
    {
        $stub = $this->getStub();
        $stub = str_replace(['{{DtoName}}', '{{DtoNamespace}}'], [$name, $namespace], $stub);

        $this->files->put($filename, $stub);
    }

    /**
     * Get the contents of the DTO stub file.
     *
     * @return string The content of the stub file.
     */
    protected function getStub(): string
    {
        $stubPath = __DIR__.'/../Stubs/dto.stub';

        if (!file_exists($stubPath)) {
            $this->error('Stub file does not exist at: ' . $stubPath);
            return '';
        }
    
        return $this->files->get($stubPath);
    }

    /**
     * Get the full path of the generated class file.
     *
     * @return string The full path to the generated class file.
     */
    public function getSourceFilePath(): string
    {
        $className = $this->getSingularClassName($this->argument('name')) . 'Dto';
        $namespace = $this->getNameSpace();
        $namespaceParts = explode('\\', $namespace);
    
        // Remove the initial 'App' segment if present
        if ($namespaceParts[0] === 'App') {
            array_shift($namespaceParts);
        }
    
        $path = app_path(implode('/', $namespaceParts) .'/'. $className . '.php');
        return $path;
    }

    /**
     * Return the singular class name based on a plural name.
     *
     * @param string $name The plural name.
     * @return string The singular class name.
     */
    public function getSingularClassName(string $name): string
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path The directory path.
     * @return string The directory path.
     */
    protected function makeDirectory(string $path): string
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    /**
     * Returns the namespace for the DTO classes.
     *
     * @return string The namespace for the DTO classes.
     */
    protected function getNameSpace(): string
    {
        return config('data-transfer-object.defaultNamespace');
    }
}

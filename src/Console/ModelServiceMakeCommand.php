<?php

namespace Webbundels\Models\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class ModelServiceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'wb:make:model-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Webbundels ModelService class';

    /**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Service';

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name) . 'Service';

        return str_replace('DummyClass', $class, $stub);
    }

    /**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return  __DIR__ . '/stubs/modelService.stub';
    }

    // Get the rootNamespace of ModelServices.
    protected function rootNamespace()
    {
        return 'app\\Services\\ModelServices';
    }
    
    // Get the full path and filename for the ModelService of the given string 'name'.
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/Services/ModelServices/' . str_replace('\\', '/', $name) . 'Service.php';
    }

    // Build the content of the ModelService class by the given string 'name'.
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceRepositoryClass($stub, $name)->replaceClass($stub, $name);
    }

    // Replace the DummyRepositoryClass in the given 'stub' by the given string 'name'.
    protected function replaceRepositoryClass(&$stub, string $name)
    {
        $stub = str_replace(
            'DummyRepositoryClass',
            str_replace(ucFirst($this->getNamespace($name)) . '\\', '', ucFirst($name)) . 'Repository',
            $stub
        );
        
        return $this;
    }
}

<?php

namespace Webbundels\Models\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'wb:make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Webbundels Repository class';

    /**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Repository';

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name) . 'Repository';

        return str_replace('DummyClass', $class, $stub);
    }

    /**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return  __DIR__ . '/stubs/repository.stub';
    }

    // Get the rootNamespace of Repositories.
    protected function rootNamespace()
    {
        return 'app\\Repositories';
    }
    
    // Get the full path and filename for the Repository of the given string 'name'.
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/Repositories/' . str_replace('\\', '/', $name) . 'Repository.php';
    }

    // Build the content of the Repository class by the given string 'name'.
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceModelClass($stub, $name)->replaceClass($stub, $name);
    }

    // Replace the DummyModelClass in the given 'stub' by the given string 'name'.
    protected function replaceModelClass(&$stub, string $name)
    {
        $stub = str_replace(
            'DummyModelClass',
            str_replace(ucFirst($this->getNamespace($name)) . '\\', '', ucFirst($name)),
            $stub
        );
        
        return $this;
    }
}

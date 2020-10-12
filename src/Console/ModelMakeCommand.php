<?php

namespace Webbundels\Models\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class ModelMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'wb:make:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Webbundels Eloquent model class';

    /**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Model';

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);
        return str_replace('DummyClass', $this->argument('name'), $stub);
    }

    /**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return  __DIR__ . '/stubs/model.stub';
    }

    // Get the rootNamespace of models.
    protected function rootNamespace()
    {
        return 'app\\Models\\';
    }
    
    // Get the full path and filename for the Model of the given string 'name'.
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        
        return $this->laravel['path'] . '/Models/' . str_replace('\\', '/', $name) . '.php';
    }
}

<?php

namespace Webbundels\Models\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class ModelServiceJsMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'wb:make:model-service-js';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Webbundels ModelService javascript class';

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
     * Replace the model class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceModelUrl(&$stub, $name)
    {
        $url = Str::lower(Str::kebab($name));
        
        $stub = str_replace('dummy-url', $url, $stub);

        return $this;
    }

    /**
     * Replace the model class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceModelClass(&$stub, $name)
    {
        $class = ucFirst(Str::camel($name));

        $stub = str_replace('DummyModelClass', $class, $stub);

        return $this;
    }

    /**
     * Replace the model class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceModelPath(&$stub, $name)
    {
        $class = Str::lower(Str::kebab($name));

        $stub = str_replace('dummy-model-class', $class, $stub);

        return $this;
    }

    /**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return  __DIR__ . '/stubs/model-service-js.stub';
    }
    
    // Get the full path and filename for the ModelService of the given string 'name'.
    protected function getPath($name)
    {
        $name = Str::lower(Str::kebab($this->getNameInput()));

        return base_path('./resources/js/services/http/' . str_replace('\\', '/', $name) . '-service.js');
    }

    // Build the content of the ModelService class by the given string 'name'.
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $name = $this->getNameInput();
        
        return $this->replaceModelClass($stub, $name)->replaceModelPath($stub, $name)->replaceModelUrl($stub, $name)->replaceClass($stub, $name);
    }
}

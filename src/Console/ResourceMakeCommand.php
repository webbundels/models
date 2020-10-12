<?php

namespace Webbundels\Models\Console;

use Illuminate\Support\Str;
use Illuminate\Foundation\Console\ResourceMakeCommand as LaravelResourceMakeCommand;

class ResourceMakeCommand extends LaravelResourceMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'wb:make:resource';

	protected function getStub()
	{
        return $this->collection()
                    ? __DIR__.'/stubs/resource-collection.stub'
                    : __DIR__.'/stubs/resource.stub';
    }

    // Build the content of the Resource and ResourceCollection class by the given string 'name'.
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceResourceClass($stub, $name)->replaceClass($stub, $name);
    }

    // Replace the DummySingularClass in the given 'stub' by the given string 'name'.
    protected function replaceResourceClass(&$stub, string $name)
    {
        $class = str_replace('Collection', 'Resource', $name);
        $class = str_replace($this->getNamespace($class).'\\', '', $class);
        $class = ucfirst(Str::singular($class));

        $stub = str_replace(
            'DummySingularClass',
            $class,
            $stub
        );
        
        return $this;
    }
}

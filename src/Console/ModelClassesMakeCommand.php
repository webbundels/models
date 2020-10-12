<?php

namespace Webbundels\Models\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class ModelClassesMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'wb:make:model-classes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Webbundels Model classes';

    // Create all Model Classes for the given argument 'name'.
    public function handle() {
        // Set 'name' to the given argument 'name' and uppercase its first character.
        $name = ucfirst($this->argument('name'));

        // Get the singular version of the string 'name'.
        $singular = Str::singular($name);

        // Get the plural version of the string 'name'.
        $plural = Str::plural($name);
        
        // Add text content to the AppServiceProvider 
        // for registering the ModelService to the IOC container.
        $content = file_get_contents(app_path('Providers/AppServiceProvider.php'));
        $content = $this->appendBindingTextToContent($content, $name);
        $content = $this->appendUseTextToContent($content, $name);
        file_put_contents(app_path('Providers/AppServiceProvider.php'), $content);

        // Create the Model file from the string 'singular'.
        $this->call('wb:make:model', [
            'name' => $singular
        ]);

        // Create the ModelService file from the string 'singular'.
        $this->call('wb:make:model-service', [
            'name' => $singular
        ]);

        // Create the Repository file from the string 'singular'.
        $this->call('wb:make:repository', [
            'name' => $singular
        ]);

        // Create the Resource file from the string 'singular'.
        $this->call('wb:make:resource', [
            'name' => $plural . '/' . $singular . 'Resource',
        ]);

        // Create the ResourceCollection file from the string 'singular'.
        $this->call('wb:make:resource', [
            'name' => $plural . '/' . $singular . 'Collection',
            '--collection'
        ]);

        // Create the migration file from the string 'name'.
        $this->createMigration($name);

        return 0;
    }

    // Create the Migration file from the given 'name' by using laravel's
    // default createMigration artisan command.
    protected function createMigration($name)
    {
        $table = Str::snake(Str::pluralStudly(class_basename($name)));

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    // Search the register method in the given 'content',
    // and add the string of php code generated by the  
    // function 'getServiceBinding' to the register method.
    public function appendBindingTextToContent($content, $name)
    {
        $searchFor = 'public function register()
    {';
        return str_replace($searchFor, $searchFor . '
        ' . $this->getServiceBinding($name), $content);
    }

    // Add a string of php code to the given 'content' that imports the ModelService of
    // the given 'name' to the AppServiceProvier by an use statement on the top of the file.
    public function appendUseTextToContent($content, $name)
    {
        $searchFor = 'use Illuminate\Support\ServiceProvider;';
$content = str_replace($searchFor, $searchFor . '
' . 'use App\\Models\\' . $name . ';', $content);
$content = str_replace($searchFor, $searchFor . '
' . 'use App\\Services\\ModelServices\\' . $name . 'Service;', $content);
$content = str_replace($searchFor, $searchFor . '
' . 'use App\\Repositories\\' . $name . 'Repository;', $content);

        return $content;
    }

    // Generate an string of php code that adds the ModelService
    // of the given string 'name' to the IOC container.
    public function getServiceBinding($name)
    {
        return '$this->app->bind("' . $name . 'Service", function ($app) {
            return new ' . $name . 'Service(new ' . $name . 'Repository(new ' . $name . '));
        });';
    }
    
    // Get the trimmed version of the given argument 'name'.
    public function getNameInput()
    {
        return trim($this->argument('name'));
    }

    // getStub is an required method in GeneratorCommand,
    // because this class doesn't generate files itself we return
    // an string so it doesn't return errors.s
    public function getStub()
    {
        return '';
    }
}
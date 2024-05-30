<?php

use Illuminate\Http\File;
use Illuminate\Filesystem\Filesystem;
use function Orchestra\Testbench\artisan;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsoleTest extends \Orchestra\Testbench\TestCase
{

    use RefreshDatabase;
    use WithWorkbench;

    protected function defineDatabaseMigrations()
    {
        artisan($this, 'make:provider AppServiceProvider');

        $this->beforeApplicationDestroyed(function() {
            $file = new Filesystem;
            $file->cleanDirectory(app_path('Providers'));
            $file->cleanDirectory(app_path('Models'));
            $file->cleanDirectory(base_path('database/migrations'));
            $file->deleteDirectory(app_path('Repositories'));
            $file->deleteDirectory(app_path('Services'));
            $file->deleteDirectories(app_path('Http/Resources'));
            //$file->deleteDirectories(base_path('resources/js'));
        });
    }
    
    /** @test */
    public function test_create_model_classes()
    {
        $this->artisan('wb:make:model-classes Permission')->assertExitCode(0);

        $this->assertFileExists(app_path('Models/Permission.php'));
        $this->assertFileExists(app_path('Repositories/PermissionRepository.php'));
        $this->assertFileExists(app_path('Services/ModelServices/PermissionService.php'));
        $this->assertFileExists(app_path('Http/Resources/Permissions/PermissionResource.php'));
        $this->assertFileExists(base_path('resources/js/services/http/permission-service.js'));
        $this->assertFileExists(base_path('resources/js/models/permission.js'));
        
        $content = file_get_contents(app_path('Providers/AppServiceProvider.php'));

        $this->assertStringContainsString('use App\Models\Permission;', $content);
        $this->assertStringContainsString('use App\Repositories\PermissionRepository;', $content);
        $this->assertStringContainsString('use App\Services\ModelServices\PermissionService;', $content);
        $this->assertStringContainsString('"PermissionService", function ($app) {', $content);
        $this->assertStringContainsString("return new PermissionService(new PermissionRepository(new Permission))", $content);
    }
}
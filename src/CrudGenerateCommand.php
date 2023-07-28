<?php

namespace Mitu\CrudGenerator;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CrudGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generate {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD operations for a model';

    public function handle()
    {
        $modelName = $this->argument('model');
        
        // 1. Generate Controller
        $this->generateController($modelName);
        
        // 2. Generate Model
        $this->generateModel($modelName);

        // 3. Generate Views
        $this->generateViews($modelName);
        
        // 4. Generate Request
        // $this->generateRequest($modelName);
        
        // 5. Generate Migration
        $this->generateMigration($modelName);

        // 6. Append Routes
        $this->appendRoutes($modelName);

        $this->info('CRUD operations generated successfully!');
    }
    // Implement methods to generate Controller, Model, Views, Request, Migration, and append Routes.
    protected function generateController($modelName)
    {
        $controllerName = "{$modelName}Controller";
        $path = app_path("Http/Controllers/{$controllerName}.php");

        $content = <<<EOD
            <?php

            namespace App\Http\Controllers;

            use App\Http\Requests\\{$modelName}Request;
            use App\\{$modelName};
            use Illuminate\Http\Request;

            class {$controllerName} extends Controller
            {
                public function index()
                {
                    \$data = {$modelName}::all();
                    return view('{$modelName}.index', compact('data'));
                }

                // Implement other CRUD methods here...
            }
        EOD;

        file_put_contents($path, $content);
    }
    protected function generateModel($modelName)
    {
        $modelPath = app_path("Models/{$modelName}.php");
        $content = <<<EOD
            <?php

            namespace App;

            use Illuminate\Database\Eloquent\Model;

            class {$modelName} extends Model
            {
                // Define model properties and relationships here
            }
        EOD;

        file_put_contents($modelPath, $content);
    }
    protected function generateViews($modelName)
    {
        $viewsPath = resource_path("views/{$modelName}");
        // Create directories for views if they don't exist
        if (!file_exists($viewsPath)) {
            mkdir($viewsPath, 0755, true);
        }
        // Create view files
        $viewFiles = ['index', 'create', 'edit', 'show'];
        foreach ($viewFiles as $viewFile) {
            $viewPath = "{$viewsPath}/{$viewFile}.blade.php";
            file_put_contents($viewPath, "<!-- {$viewFile} view for {$modelName} CRUD -->");
        }
    }
    // protected function generateRequest($modelName)
    // {
    //     $requestName = "{$modelName}Request";
    //     $requestPath = app_path("Http/Requests/{$requestName}.php");

    //     $content = <<<EOD
    //         <?php

    //         namespace App\Http\Requests;

    //         use Illuminate\Foundation\Http\FormRequest;

    //         class {$requestName} extends FormRequest
    //         {
    //             public function authorize()
    //             {
    //                 return true;
    //             }

    //             public function rules()
    //             {
    //                 return [
    //                     // Define validation rules here
    //                 ];
    //             }
    //         }
    //     EOD;
    //     file_put_contents($requestPath, $content);
    // }
    protected function generateMigration($modelName)
    {
        $table='$table';
        $tableName = strtolower(Str::plural($modelName));
        $migrationName = "create_{$tableName}_table";
        $migrationPath = database_path("migrations/" . date('Y_m_d_His') . "_{$migrationName}.php");

        $content = <<<EOD
            <?php

            use Illuminate\Database\Migrations\Migration;
            use Illuminate\Database\Schema\Blueprint;
            use Illuminate\Support\Facades\Schema;

            class {$migrationName} extends Migration
            {
                public function up()
                {
                    Schema::create('{$tableName}', function (Blueprint $table) {
                        \$table->id();
                        // Define table columns here
                        \$table->timestamps();
                    });
                }

                public function down()
                {
                    Schema::dropIfExists('{$tableName}');
                }
            }
        EOD;

        file_put_contents($migrationPath, $content);
    }
    protected function appendRoutes($modelName)
    {
        $routePath = base_path('routes/web.php');
        $routeContent = <<<EOD
                Route::resource('{$modelName}', {$modelName}Controller::class);
            EOD;
        // Append the route to the web.php file
        file_put_contents($routePath, $routeContent, FILE_APPEND);
    }
}
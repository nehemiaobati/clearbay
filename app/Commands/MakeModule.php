<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class MakeModule
 *
 * Custom Spark command to scaffold a new feature module following the MVC-S structure.
 *
 * @package App\Commands
 */
class MakeModule extends BaseCommand
{
    /**
     * @var string Command group
     */
    protected $group = 'Generators';

    /**
     * @var string Command name
     */
    protected $name = 'make:module';

    /**
     * @var string Command description
     */
    protected $description = 'Generates a new Module with the standard MVC-S structure.';

    /**
     * @var string Command usage
     */
    protected $usage = 'make:module [name]';

    /**
     * @var array Arguments description
     */
    protected $arguments = [
        'name' => 'The name of the module to create (PascalCase).',
    ];

    // ==========================================
    // // --- Helper Methods ---
    // ==========================================

    /**
     * Creates the module Routes configuration file.
     *
     * @param string $path File path for module root
     * @param string $name Module name
     * @return void
     */
    private function _createRoutesFile(string $path, string $name): void
    {
        $lower_name = strtolower($name);
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Modules\\" . $name . "\\Config;\n\nuse CodeIgniter\\Router\\RouteCollection;\n\n/**\n * @var RouteCollection \$routes\n */\n\$routes->group('" . $lower_name . "', ['namespace' => 'App\\Modules\\" . $name . "\\Controllers'], static function (\$routes) {\n    \$routes->get('/', '" . $name . "Controller::index', ['as' => '" . $lower_name . ".index']);\n});\n";
        file_put_contents($path . '/Config/Routes.php', $content);
    }

    /**
     * Creates the boilerplate Controller file.
     *
     * @param string $path File path for module root
     * @param string $name Module name
     * @return void
     */
    private function _createControllerFile(string $path, string $name): void
    {
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Modules\\" . $name . "\\Controllers;\n\nuse App\Controllers\BaseController;\n\n/**\n * Class " . $name . "Controller\n */\nclass " . $name . "Controller extends BaseController\n{\n    /**\n     * Index action.\n     *\n     * @return string\n     */\n    public function index(): string\n    {\n        return view('App\\Modules\\" . $name . "\\Views\\index', ['page_title' => '" . $name . " Module']);\n    }\n}\n";
        file_put_contents($path . '/Controllers/' . $name . 'Controller.php', $content);
    }

    /**
     * Creates the boilerplate Entity file.
     *
     * @param string $path File path for module root
     * @param string $name Module name
     * @return void
     */
    private function _createEntityFile(string $path, string $name): void
    {
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Modules\\" . $name . "\\Entities;\n\nuse CodeIgniter\\Entity\\Entity;\n\n/**\n * Class " . $name . "\n */\nclass " . $name . " extends Entity\n{\n    protected \$datamap = [];\n    protected \$dates = ['created_at', 'updated_at', 'deleted_at'];\n    protected \$casts = [];\n}\n";
        file_put_contents($path . '/Entities/' . $name . '.php', $content);
    }

    /**
     * Creates the boilerplate Service file.
     *
     * @param string $path File path for module root
     * @param string $name Module name
     * @return void
     */
    private function _createServiceFile(string $path, string $name): void
    {
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Modules\\" . $name . "\\Libraries;\n\n/**\n * Class " . $name . "Service\n */\nclass " . $name . "Service\n{\n    /**\n     * " . $name . "Service constructor.\n     */\n    public function __construct()\n    {\n        // Initialize dependencies\n    }\n}\n";
        file_put_contents($path . '/Libraries/' . $name . 'Service.php', $content);
    }

    /**
     * Creates the boilerplate Model file.
     *
     * @param string $path File path for module root
     * @param string $name Module name
     * @return void
     */
    private function _createModelFile(string $path, string $name): void
    {
        $lower_name = strtolower($name);
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Modules\\" . $name . "\\Models;\n\nuse CodeIgniter\\Model;\nuse App\Modules\\" . $name . "\\Entities\\" . $name . ";\n\n/**\n * Class " . $name . "Model\n */\nclass " . $name . "Model extends Model\n{\n    protected \$table = '" . $lower_name . "';\n    protected \$returnType = " . $name . "::class;\n    protected \$useTimestamps = true;\n    protected \$allowedFields = [];\n}\n";
        file_put_contents($path . '/Models/' . $name . 'Model.php', $content);
    }

    /**
     * Creates the boilerplate View file.
     *
     * @param string $path File path for module root
     * @param string $name Module name
     * @return void
     */
    private function _createViewFile(string $path, string $name): void
    {
        $content = "<?= \$this->extend('layouts/default') ?>\n\n<?= \$this->section('content') ?>\n<div class=\"container my-5\">\n    <h1>" . $name . " Module</h1>\n</div>\n<?= \$this->endSection() ?>\n";
        file_put_contents($path . '/Views/index.php', $content);
    }

    /**
     * Updates Config/Autoload.php with the module namespace path.
     *
     * @param string $name Module name
     * @return void
     */
    private function _updateAutoload(string $name): void
    {
        $file_path = APPPATH . 'Config/Autoload.php';
        if (!file_exists($file_path)) {
            return;
        }

        $content = file_get_contents($file_path);
        $line_to_add = "        'App\\\\Modules\\\\" . $name . "' => APPPATH . 'Modules/" . $name . "',";

        if (strpos($content, $line_to_add) === false) {
            $pattern = '/(\$psr4\s*=\s*\[)(.*?)(\];)/s';
            if (preg_match($pattern, $content)) {
                $new_content = preg_replace($pattern, "$1$2$line_to_add\n        $3", $content);
                if ($new_content !== null) {
                    file_put_contents($file_path, $new_content);
                }
            }
        }
    }

    // ==========================================
    // Public Command Execution Entry
    // ==========================================

    /**
     * Runs the Spark make:module command.
     *
     * @param array $params Command arguments
     * @return void
     */
    public function run(array $params): void
    {
        $module_name = array_shift($params);
        if (empty($module_name)) {
            $module_name = CLI::prompt('Module Name (PascalCase)', null, 'required');
        }
        
        $module_name = ucfirst((string) $module_name);
        $module_path = APPPATH . 'Modules/' . $module_name;

        if (is_dir($module_path)) {
            CLI::error("Module '" . $module_name . "' already exists.");
            return;
        }

        CLI::write("Creating module: " . $module_name, 'yellow');

        $directories = [
            'Config',
            'Controllers',
            'Database/Migrations',
            'Database/Seeds',
            'Entities',
            'Libraries',
            'Models',
            'Views',
        ];

        foreach ($directories as $dir) {
            $path = $module_path . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Invoke helper methods defined before run()
        $this->_createRoutesFile($module_path, $module_name);
        $this->_createControllerFile($module_path, $module_name);
        $this->_createEntityFile($module_path, $module_name);
        $this->_createServiceFile($module_path, $module_name);
        $this->_createModelFile($module_path, $module_name);
        $this->_createViewFile($module_path, $module_name);
        $this->_updateAutoload($module_name);

        CLI::write("Module '" . $module_name . "' created successfully!", 'green');
    }
}

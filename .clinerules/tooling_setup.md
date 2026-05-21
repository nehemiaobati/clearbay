# Standard Tooling Setup

The "Simple over Easy" workflow relies on three custom Spark commands that are not native to CodeIgniter 4. Implementation code is provided below.

## 1. Generator: `make:module`

**Path**: `app/Commands/MakeModule.php`

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MakeModule extends BaseCommand
{
    protected $group = 'Generators';
    protected $name = 'make:module';
    protected $description = 'Generates a new Module with the standard MVC-S structure.';
    protected $usage = 'make:module [name]';
    protected $arguments = ['name' => 'The name of the module to create (PascalCase).'];

    public function run(array $params)
    {
        $moduleName = array_shift($params);
        if (empty($moduleName)) $moduleName = CLI::prompt('Module Name (PascalCase)', null, 'required');
        $moduleName = ucfirst($moduleName);
        $modulePath = APPPATH . 'Modules/' . $moduleName;

        if (is_dir($modulePath)) {
            CLI::error("Module '$moduleName' already exists.");
            return;
        }

        CLI::write("Creating module: $moduleName", 'yellow');

        $directories = [
            'Config', 'Controllers', 'Database/Migrations', 'Database/Seeds',
            'Entities', 'Libraries', 'Models', 'Views',
        ];

        foreach ($directories as $dir) {
            $path = $modulePath . '/' . $dir;
            if (!is_dir($path)) mkdir($path, 0755, true);
        }

        $this->_createRoutesFile($modulePath, $moduleName);
        $this->_createControllerFile($modulePath, $moduleName);
        $this->_createEntityFile($modulePath, $moduleName);
        $this->_createServiceFile($modulePath, $moduleName);
        $this->_createModelFile($modulePath, $moduleName);
        $this->_createViewFile($modulePath, $moduleName);
        $this->_updateAutoload($moduleName);

        CLI::write("Module '$moduleName' created successfully!", 'green');
    }

    private function _createRoutesFile($path, $name)
    {
        $lower = strtolower($name);
        $content = "<?php\n\nnamespace App\Modules\\$name\Config;\n\n\$routes->group('$lower', ['namespace' => 'App\Modules\\$name\Controllers'], static function (\$routes) {\n    \$routes->get('/', '{$name}Controller::index', ['as' => '$lower.index']);\n});\n";
        file_put_contents($path . '/Config/Routes.php', $content);
    }

    private function _createControllerFile($path, $name)
    {
        $content = "<?php\n\nnamespace App\Modules\\$name\Controllers;\n\nuse App\Controllers\BaseController;\n\nclass {$name}Controller extends BaseController\n{\n    public function index()\n    {\n        return view('App\Modules\\$name\Views\index', ['pageTitle' => '$name Module']);\n    }\n}\n";
        file_put_contents($path . '/Controllers/' . $name . 'Controller.php', $content);
    }

    private function _createEntityFile($path, $name)
    {
        $content = "<?php\n\nnamespace App\Modules\\$name\Entities;\n\nuse CodeIgniter\Entity\Entity;\n\nclass $name extends Entity\n{\n    protected \$datamap = [];\n    protected \$dates = ['created_at', 'updated_at', 'deleted_at'];\n}\n";
        file_put_contents($path . '/Entities/' . $name . '.php', $content);
    }

    private function _createServiceFile($path, $name)
    {
        $content = "<?php\n\nnamespace App\Modules\\$name\Libraries;\n\nclass {$name}Service\n{\n    public function __construct()\n    {\n        // Initialize dependencies\n    }\n}\n";
        file_put_contents($path . '/Libraries/' . $name . 'Service.php', $content);
    }

    private function _createModelFile($path, $name)
    {
        $lower = strtolower($name);
        $content = "<?php\n\nnamespace App\Modules\\$name\Models;\n\nuse CodeIgniter\Model;\nuse App\Modules\\$name\Entities\\$name;\n\nclass {$name}Model extends Model\n{\n    protected \$table = '{$lower}_table';\n    protected \$returnType = $name::class;\n    protected \$useTimestamps = true;\n    protected \$allowedFields = [];\n}\n";
        file_put_contents($path . '/Models/' . $name . 'Model.php', $content);
    }

    private function _createViewFile($path, $name)
    {
        $content = "<?= \$this->extend('layouts/default') ?>\n\n<?= \$this->section('content') ?>\n<div class=\"container my-5\">\n    <h1>$name Module</h1>\n</div>\n<?= \$this->endSection() ?>\n";
        file_put_contents($path . '/Views/index.php', $content);
    }

    private function _updateAutoload($name)
    {
        $file = APPPATH . 'Config/Autoload.php';
        $content = file_get_contents($file);
        $line = "    'App\\\\Modules\\\\$name' => APPPATH . 'Modules/$name',";

        if (strpos($content, $line) === false) {
            $pattern = '/(\$psr4\s*=\s*\[)(.*?)(\];)/s';
            if (preg_match($pattern, $content)) {
                $newContent = preg_replace($pattern, "$1$2$line\n        $3", $content);
                file_put_contents($file, $newContent);
            }
        }
    }
}
```

## 2. Integration: `db:backup`

**Path**: `app/Commands/Database/Backup.php`

```php
<?php

namespace App\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class Backup extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'db:backup';
    protected $description = 'Backups the database using mysqldump.';
    protected $usage = 'db:backup [filename]';
    protected $arguments = ['filename' => 'The filename of the backup file.'];

    public function run(array $params)
    {
        $filename = array_shift($params) ?: 'backup-' . date('Y-m-d_H-i-s') . '.sql';
        if (!str_ends_with($filename, '.sql')) $filename .= '.sql';

        $path = WRITEPATH . 'backups/' . $filename;
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);

        $db = Database::connect();
        $passPart = $db->password ? "-p'{$db->password}'" : '';
        $portPart = ($db->port && $db->port !== 3306) ? "--port={$db->port}" : '';

        // mysqldump must be in PATH
        $cmd = "mysqldump --single-transaction --set-gtid-purged=OFF -h {$db->hostname} -u {$db->username} {$passPart} {$portPart} {$db->database} > {$path}";

        CLI::write("Backing up to: $path", 'yellow');
        exec($cmd, $o, $r);

        if ($r === 0) CLI::write('Success.', 'green');
        else CLI::error('Failure.');
    }
}
```

## 3. Integration: `db:restore`

**Path**: `app/Commands/Database/Restore.php`

```php
<?php

namespace App\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class Restore extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'db:restore';
    protected $description = 'Restores database from backup.';
    protected $usage = 'db:restore [filename]';

    public function run(array $params)
    {
        $filename = array_shift($params);
        $dir = WRITEPATH . 'backups/';

        if (empty($filename)) {
            $files = glob($dir . '*.sql');
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
            $opts = array_map('basename', $files);
            if (empty($opts)) return CLI::error('No backups found.');
            $filename = $opts[CLI::promptByKey('Select backup:', $opts)];
        }

        $path = $dir . $filename;
        if (!file_exists($path)) $path .= '.sql';

        CLI::write("Restoring from: $path", 'yellow');

        $db = Database::connect();
        $passPart = $db->password ? "-p'{$db->password}'" : '';
        $portPart = ($db->port && $db->port !== 3306) ? "--port={$db->port}" : '';

        $cmd = "mysql -h {$db->hostname} -u {$db->username} {$passPart} {$portPart} {$db->database} < {$path}";
        exec($cmd, $o, $r);

        if ($r === 0) CLI::write('Success.', 'green');
        else CLI::error('Restoration failed.');
    }
}
```

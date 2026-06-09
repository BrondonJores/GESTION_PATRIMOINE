<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => ':memory:']);

\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

try {
    $tables = \Illuminate\Support\Facades\Schema::getTables();
    foreach ($tables as $table) {
        // Skip migrations tables
        if (in_array($table['name'], ['migrations', 'sqlite_sequence', 'jobs', 'cache', 'cache_locks', 'failed_jobs', 'job_batches'])) continue;
        
        echo "TABLE: {$table['name']}\n";
        foreach (\Illuminate\Support\Facades\Schema::getColumns($table['name']) as $col) {
            $nullable = $col['nullable'] ? 'NULL' : 'NOT NULL';
            echo "  - {$col['name']} [{$col['type_name']}] {$nullable}\n";
        }
        echo "FOREIGN KEYS:\n";
        foreach (\Illuminate\Support\Facades\Schema::getForeignKeys($table['name']) as $fk) {
            $cols = implode(',', $fk['columns']);
            $fcols = implode(',', $fk['foreign_columns']);
            echo "  - {$cols} -> {$fk['foreign_table']}({$fcols})\n";
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

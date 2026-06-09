<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $tables = \Illuminate\Support\Facades\Schema::getTables();
    foreach ($tables as $table) {
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

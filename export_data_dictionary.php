
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = \Illuminate\Support\Facades\DB::class;

$tables = $db::select("
    SELECT name
    FROM sqlite_master
    WHERE type = 'table'
      AND name NOT LIKE 'sqlite_%'
    ORDER BY name
");

$directory = base_path('docs/database');

if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$path = $directory . DIRECTORY_SEPARATOR . 'data_dictionary.csv';
$file = fopen($path, 'w');

if (!$file) {
    exit("Error: Cannot create CSV file.\n");
}

fputcsv($file, [
    'Table Name',
    'Column Name',
    'Data Type',
    'Key',
    'Referenced Column',
    'Nullable',
    'Default Value',
    'Indexes',
    'Description'
], ',', '"', '\\');

$total = 0;

foreach ($tables as $tableRow) {
    $table = $tableRow->name;
    $quoted = '"' . str_replace('"', '""', $table) . '"';

    $columns = $db::select("PRAGMA table_info($quoted)");
    $foreignKeys = $db::select("PRAGMA foreign_key_list($quoted)");
    $indexes = $db::select("PRAGMA index_list($quoted)");

    $fkMap = [];
    foreach ($foreignKeys as $fk) {
        $fkMap[$fk->from][] = $fk->table . '.' . $fk->to;
    }

    $indexMap = [];
    foreach ($indexes as $index) {
        $indexName = '"' . str_replace('"', '""', $index->name) . '"';
        $indexColumns = $db::select("PRAGMA index_info($indexName)");

        foreach ($indexColumns as $ic) {
            if ($ic->name !== null) {
                $label = ((int) $index->unique === 1)
                    ? 'UNIQUE'
                    : 'INDEX';

                $indexMap[$ic->name][] = $index->name . ' [' . $label . ']';
            }
        }
    }

    foreach ($columns as $column) {
        $keys = [];

        if ((int) $column->pk > 0) {
            $keys[] = 'PK';
        }

        if (isset($fkMap[$column->name])) {
            $keys[] = 'FK';
        }

        $nullable = (
            (int) $column->pk > 0 ||
            (int) $column->notnull === 1
        ) ? 'No' : 'Yes';

        fputcsv($file, [
            $table,
            $column->name,
            $column->type ?: 'Not specified',
            implode(', ', $keys),
            implode('; ', $fkMap[$column->name] ?? []),
            $nullable,
            $column->dflt_value ?? 'None',
            implode('; ', $indexMap[$column->name] ?? []),
            ''
        ], ',', '"', '\\');

        $total++;
    }
}

fclose($file);

echo "Data Dictionary created successfully!\n";
echo "Tables: " . count($tables) . "\n";
echo "Columns: " . $total . "\n";
echo "File: " . $path . "\n";

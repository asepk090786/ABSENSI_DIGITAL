<?php

$source = $argv[1] ?? 'simadis.sql';
$target = $argv[2] ?? 'database/database.sqlite';

$sql = file_get_contents($source);
if ($sql === false) {
    throw new RuntimeException("Cannot read {$source}");
}

function statements(string $sql): array
{
    $result = [];
    $buffer = '';
    $quoted = false;
    $length = strlen($sql);

    for ($index = 0; $index < $length; $index++) {
        $character = $sql[$index];
        $next = $sql[$index + 1] ?? '';

        if ($character === "'" && $next === "'") {
            $buffer .= "''";
            $index++;
            continue;
        }
        if ($character === "'" && ($index === 0 || $sql[$index - 1] !== '\\')) {
            $quoted = !$quoted;
        }
        $buffer .= $character;

        if ($character === ';' && !$quoted) {
            $result[] = trim(substr($buffer, 0, -1));
            $buffer = '';
        }
    }

    return $result;
}

function normalizeInsert(string $statement): string
{
    $output = '';
    $quoted = false;
    $length = strlen($statement);

    for ($index = 0; $index < $length; $index++) {
        $character = $statement[$index];
        $next = $statement[$index + 1] ?? '';

        if ($character === '\\' && $quoted) {
            if ($next === "'") {
                $output .= "''";
                $index++;
                continue;
            }
            if ($next === '\\') {
                $output .= '\\';
                $index++;
                continue;
            }
            if ($next === 'n') {
                $output .= "\n";
                $index++;
                continue;
            }
            if ($next === 'r') {
                $output .= "\r";
                $index++;
                continue;
            }
        }
        if ($character === "'" && ($index === 0 || $statement[$index - 1] !== '\\')) {
            $quoted = !$quoted;
        }
        $output .= $character;
    }

    return $output;
}

function normalizeCreate(string $statement): ?string
{
    if (!preg_match('/^CREATE TABLE/i', $statement)) {
        return null;
    }

    $lines = preg_split('/\R/', $statement);
    $kept = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^(KEY|UNIQUE KEY|CONSTRAINT|FULLTEXT KEY|SPATIAL KEY)/i', $trimmed)) {
            continue;
        }
        if (preg_match('/^\)\s+ENGINE=/i', $trimmed)) {
            $line = preg_replace('/^\)(.*)$/', ');', $line);
        }
        $kept[] = $line;
    }

    $statement = implode("\n", $kept);
    $statement = preg_replace('/\benum\s*\([^)]*\)/i', 'TEXT', $statement);
    $statement = preg_replace('/\b(set|tinyint|smallint|mediumint|int|bigint)\s*\([^)]*\)/i', 'INTEGER', $statement);
    $statement = preg_replace('/\bunsigned\b/i', '', $statement);
    $statement = preg_replace('/\bAUTO_INCREMENT\b/i', '', $statement);
    $statement = preg_replace('/\b(longtext|mediumtext|tinytext|json)\b/i', 'TEXT', $statement);
    $statement = preg_replace('/\b(double|float)\b/i', 'REAL', $statement);
    $statement = preg_replace('/\bdecimal\s*\([^)]*\)/i', 'NUMERIC', $statement);
    $statement = preg_replace('/\s+CHARACTER\s+SET\s+\w+/i', '', $statement);
    $statement = preg_replace('/\s+CHECK\s*\(\s*json_valid\([^)]*\)\s*\)/i', '', $statement);
    $statement = preg_replace('/\bCURRENT_TIMESTAMP\(\)/i', 'CURRENT_TIMESTAMP', $statement);
    $statement = preg_replace('/\s+ON UPDATE\s+CURRENT_TIMESTAMP/i', '', $statement);
    $statement = preg_replace('/\s+COMMENT\s+\'[^\']*\'/i', '', $statement);
    $statement = preg_replace('/\s+COLLATE\s+\w+/i', '', $statement);
    $statement = preg_replace('/,\s*\);?$/s', "\n)", $statement);

    return trim($statement);
}

if (is_file($target)) {
    unlink($target);
}

$directory = dirname($target);
if (!is_dir($directory)) {
    mkdir($directory, 0775, true);
}

$database = new SQLite3($target);
$database->exec('PRAGMA foreign_keys = OFF');
$database->exec('BEGIN TRANSACTION');
$tables = 0;
$rows = 0;

try {
    foreach (statements($sql) as $statement) {
        $statement = trim($statement);
        if ($statement === '' || str_starts_with($statement, '--') || str_starts_with($statement, '/*')) {
            continue;
        }
        if (preg_match('/^(SET|LOCK TABLES|UNLOCK TABLES|START TRANSACTION|COMMIT|DELIMITER|USE)\b/i', $statement)) {
            continue;
        }

        if (preg_match('/^CREATE TABLE/i', $statement)) {
            $statement = normalizeCreate($statement);
            $tables++;
        } elseif (preg_match('/^INSERT INTO/i', $statement)) {
            $statement = normalizeInsert($statement);
            $rows += substr_count(strtoupper($statement), '), (') + 1;
        } else {
            continue;
        }

        if (!$database->exec($statement)) {
            throw new RuntimeException($database->lastErrorMsg() . "\n{$statement}");
        }
    }
    $database->exec('COMMIT');
} catch (Throwable $exception) {
    $database->exec('ROLLBACK');
    $database->close();
    @unlink($target);
    throw $exception;
}

$database->close();
printf("Imported %d tables and %d insert batches into %s\n", $tables, $rows, $target);
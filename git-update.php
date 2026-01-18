#!/usr/bin/php
<?php
/**
 * Interactive Git Update Script
 * Memberikan pilihan kepada user sebelum push ke GitHub
 */

const COLOR_GREEN = "\033[32m";
const COLOR_RED = "\033[31m";
const COLOR_YELLOW = "\033[33m";
const COLOR_BLUE = "\033[34m";
const COLOR_RESET = "\033[0m";
const COLOR_BOLD = "\033[1m";

function colorize($text, $color) {
    return $color . $text . COLOR_RESET;
}

function printHeader($text) {
    echo "\n" . colorize("═══════════════════════════════════════", COLOR_BLUE) . "\n";
    echo colorize("  $text", COLOR_BOLD . COLOR_BLUE) . "\n";
    echo colorize("═══════════════════════════════════════", COLOR_BLUE) . "\n\n";
}

function printSuccess($text) {
    echo colorize("✓ $text", COLOR_GREEN) . "\n";
}

function printError($text) {
    echo colorize("✗ $text", COLOR_RED) . "\n";
}

function printWarning($text) {
    echo colorize("⚠ $text", COLOR_YELLOW) . "\n";
}

function printInfo($text) {
    echo colorize("ℹ $text", COLOR_BLUE) . "\n";
}

function getUserInput($prompt, $default = 'y') {
    echo colorize($prompt, COLOR_YELLOW);
    echo " [" . ($default === 'y' ? "Y" : "y") . "/" . ($default === 'n' ? "N" : "n") . "]: ";
    
    $input = trim(fgets(STDIN));
    
    if (empty($input)) {
        return $default;
    }
    
    return strtolower($input[0]);
}

function runCommand($command, $silent = false) {
    if (!$silent) {
        echo colorize("→ $command", COLOR_BLUE) . "\n";
    }
    
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);
    
    return [
        'status' => $returnVar,
        'output' => $output,
        'success' => $returnVar === 0
    ];
}

// Main script
printHeader("SISTEM UPDATE GIT INTERAKTIF");

// 1. Check current branch
printInfo("Mengecek branch dan status...");
$branchResult = runCommand("git branch --show-current", true);
$branch = trim(implode("\n", $branchResult['output']));

if (!$branchResult['success'] || empty($branch)) {
    printError("Gagal mendapatkan informasi branch!");
    exit(1);
}

printSuccess("Branch saat ini: " . colorize($branch, COLOR_BOLD));

// 2. Check for uncommitted changes
$statusResult = runCommand("git status --porcelain", true);
$hasChanges = count($statusResult['output']) > 0;

if ($hasChanges) {
    echo "\n" . colorize("Perubahan yang ditemukan:", COLOR_YELLOW) . "\n";
    foreach ($statusResult['output'] as $line) {
        echo "  " . colorize($line, COLOR_YELLOW) . "\n";
    }
    echo "\n";
} else {
    printInfo("Tidak ada perubahan yang belum di-commit.");
}

// 3. Check for unpushed commits
$unpushedResult = runCommand("git rev-list --count " . $branch . "..origin/" . $branch, true);
$unpushedCount = (int)trim(implode("\n", $unpushedResult['output']));

if ($unpushedCount > 0) {
    printWarning("Ada $unpushedCount commit yang belum di-push.");
}

echo "\n";

// 4. Process uncommitted changes
if ($hasChanges) {
    $commitChoice = getUserInput("Apakah ingin commit perubahan ini?");
    
    if ($commitChoice === 'y') {
        echo "\n";
        $message = "Update dari fix_logo dan git-update script";
        
        // Stage changes
        $stageResult = runCommand("git add -A");
        if (!$stageResult['success']) {
            printError("Gagal stage files!");
            exit(1);
        }
        printSuccess("Files di-stage");
        
        // Commit
        $commitResult = runCommand("git commit -m \"$message\"");
        if (!$commitResult['success']) {
            printError("Gagal melakukan commit!");
            exit(1);
        }
        printSuccess("Commit berhasil!");
        
        $unpushedCount++;
        echo "\n";
    } else {
        printWarning("Commit dibatalkan.\n");
    }
}

// 5. Process push
if ($unpushedCount > 0) {
    $pushChoice = getUserInput("Apakah ingin push ke GitHub?");
    
    if ($pushChoice === 'y') {
        echo "\n";
        printInfo("Melakukan push ke branch " . colorize($branch, COLOR_BOLD) . "...\n");
        
        $pushResult = runCommand("git push origin $branch");
        
        if ($pushResult['success']) {
            printSuccess("Push berhasil!");
            echo "\n";
        } else {
            printError("Push gagal!");
            exit(1);
        }
    } else {
        printWarning("Push dibatalkan.\n");
    }
}

// 6. Check for incoming changes from GitHub
printInfo("Mengecek update dari GitHub...");
$fetchResult = runCommand("git fetch origin", true);

if ($fetchResult['success']) {
    $incomingResult = runCommand("git rev-list --count " . $branch . "..origin/" . $branch, true);
    $incomingCount = (int)trim(implode("\n", $incomingResult['output']));
    
    if ($incomingCount > 0) {
        printWarning("Ada $incomingCount update dari GitHub");
        echo "\n";
        
        $pullChoice = getUserInput("Apakah ingin pull update dari GitHub?");
        
        if ($pullChoice === 'y') {
            echo "\n";
            printInfo("Melakukan pull dari GitHub...\n");
            
            $pullResult = runCommand("git pull origin $branch");
            
            if ($pullResult['success']) {
                printSuccess("Pull berhasil!");
                echo "\n";
            } else {
                printError("Pull gagal!");
                exit(1);
            }
        } else {
            printWarning("Pull dibatalkan.\n");
        }
    } else {
        printSuccess("Repository sudah up-to-date dengan GitHub");
        echo "\n";
    }
}

printHeader("SELESAI");
printSuccess("Proses update selesai!");
echo "\n";
?>

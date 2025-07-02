<?php
// Clean up php.ini by removing duplicate extension lines

$phpIniPath = 'C:\php\php.ini';
$backupPath = 'C:\php\php.ini.backup';

echo "Cleaning php.ini file...\n";
echo "========================\n\n";

// Create backup
if (copy($phpIniPath, $backupPath)) {
    echo "✓ Backup created: $backupPath\n";
} else {
    echo "✗ Failed to create backup\n";
    exit(1);
}

// Read the file
$lines = file($phpIniPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    echo "✗ Failed to read php.ini\n";
    exit(1);
}

echo "Original file has " . count($lines) . " lines\n";

// Track seen extensions
$seenExtensions = [];
$cleanedLines = [];
$removedCount = 0;

foreach ($lines as $line) {
    $line = trim($line);
    
    // Check if this is an extension line
    if (preg_match('/^extension\s*=\s*(.+)$/i', $line, $matches)) {
        $extension = trim($matches[1]);
        
        // Remove quotes if present
        $extension = trim($extension, '"\'');
        
        if (in_array($extension, $seenExtensions)) {
            echo "Removing duplicate: $extension\n";
            $removedCount++;
            continue;
        } else {
            $seenExtensions[] = $extension;
        }
    }
    
    $cleanedLines[] = $line;
}

// Write the cleaned file
if (file_put_contents($phpIniPath, implode("\n", $cleanedLines))) {
    echo "\n✓ Cleaned php.ini successfully!\n";
    echo "Removed $removedCount duplicate extension lines\n";
    echo "Final file has " . count($cleanedLines) . " lines\n";
    
    echo "\nUnique extensions found:\n";
    foreach ($seenExtensions as $ext) {
        echo "  - $ext\n";
    }
} else {
    echo "✗ Failed to write cleaned php.ini\n";
    exit(1);
}

echo "\nDone! Your php.ini is now clean.\n"; 
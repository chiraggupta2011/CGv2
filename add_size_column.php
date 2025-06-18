<?php
// Temporary script to add size column to order_items table
require_once 'includes/db.php';

try {
    // Add size column to order_items table
    $sql = "ALTER TABLE order_items ADD COLUMN size VARCHAR(20) DEFAULT NULL AFTER price";
    $pdo->exec($sql);
    echo "✅ Successfully added 'size' column to order_items table!<br>";
    
    // Also add sizes column to products table if it doesn't exist
    $sql2 = "ALTER TABLE products ADD COLUMN sizes VARCHAR(100) DEFAULT NULL AFTER stock_quantity";
    $pdo->exec($sql2);
    echo "✅ Successfully added 'sizes' column to products table!<br>";
    
    echo "<br>🎉 Database updated successfully! You can now delete this file.";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ Column already exists. No changes needed.";
    } else {
        echo "❌ Error: " . $e->getMessage();
    }
}

// This script updates all products to have sizes '6,7,8,9,10,11'
require_once __DIR__ . '/includes/db.php';

try {
    $sizes = '6,7,8,9,10,11';
    $stmt = $pdo->prepare("UPDATE products SET sizes = ?");
    $stmt->execute([$sizes]);
    echo "All products updated with sizes: $sizes";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
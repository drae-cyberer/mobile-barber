<?php
/**
 * Database Setup Script
 * 
 * This script automatically creates the database tables from the schema.sql file.
 * Run this script once to set up your database structure.
 */

// Include database configuration
require_once 'config/database.php';

echo "<h1>Mobile Barber Platform - Database Setup</h1>";

// Read the schema file
$schema_file = 'database/schema.sql';

if (!file_exists($schema_file)) {
    die("<p style='color: red;'>Error: Schema file not found at {$schema_file}</p>");
}

$sql = file_get_contents($schema_file);

// Split the SQL file at semicolons to get individual queries
$queries = explode(';', $sql);

// Initialize counters
$total_queries = 0;
$successful_queries = 0;

echo "<p>Starting database setup...</p>";
echo "<ul>";

// Execute each query
foreach ($queries as $query) {
    // Skip empty queries
    $query = trim($query);
    if (empty($query)) {
        continue;
    }
    
    $total_queries++;
    
    // Execute the query
    if ($conn->query($query)) {
        echo "<li style='color: green;'>Success: " . substr(htmlspecialchars($query), 0, 100) . "...</li>";
        $successful_queries++;
    } else {
        echo "<li style='color: red;'>Error: " . $conn->error . "<br>Query: " . htmlspecialchars($query) . "</li>";
    }
}

echo "</ul>";

// Display summary
echo "<p>Database setup completed. {$successful_queries} of {$total_queries} queries executed successfully.</p>";

if ($successful_queries == $total_queries) {
    echo "<p style='color: green; font-weight: bold;'>All tables created successfully! Your database is now ready to use.</p>";
    echo "<p><a href='index.php'>Go to homepage</a></p>";
} else {
    echo "<p style='color: orange; font-weight: bold;'>Some queries failed. Please check the errors above.</p>";
}

// Close the connection
$conn->close();
?>
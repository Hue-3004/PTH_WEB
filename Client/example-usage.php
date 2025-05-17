<?php
require_once 'config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Example query using PDO
try {
    $query = "SELECT * FROM your_table";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    // Fetch all results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Display results
    foreach($results as $row) {
        print_r($row);
    }
} catch(PDOException $e) {
    echo "Query error: " . $e->getMessage();
}
?> 
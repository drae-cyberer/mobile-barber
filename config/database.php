<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection settings for the Mobile Barber Platform.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change in production
define('DB_PASS', ''); // Change in production
define('DB_NAME', 'mobile_barber');

// Establish database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // For initial setup, create the database if it doesn't exist
    $temp_conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if (!$temp_conn->connect_error) {
        $temp_conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $temp_conn->close();
        
        // Try connecting again
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    } else {
        die("Server connection failed: " . $temp_conn->connect_error);
    }
}

/**
 * Helper function to execute database queries
 * 
 * @param string $sql SQL query to execute
 * @param array $params Parameters to bind to the query
 * @return mysqli_stmt|bool Statement object or false on failure
 */
function db_query($sql, $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Determine parameter types
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }
        
        // Prepend $types to $bindParams array
        array_unshift($bindParams, $types);
        
        // Create references for bind_param
        $bindParamsRefs = [];
        foreach ($bindParams as $key => $value) {
            $bindParamsRefs[$key] = &$bindParams[$key];
        }
        
        // Call bind_param with references
        call_user_func_array([$stmt, 'bind_param'], $bindParamsRefs);
    }
    
    $stmt->execute();
    return $stmt;
}
?>
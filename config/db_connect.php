<?php
/**
 * VISION WORLD: Database Connection
 * Single unified MySQLi connection file
 * Procedural Core PHP with native drivers
 */

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'vision_world');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// Establish MySQLi Connection
$conn = @mysqli_connect(
    DB_HOST,
    DB_USER,
    DB_PASSWORD,
    DB_NAME,
    DB_PORT
);

// Connection Error Handling
if (!$conn) {
    error_log('Database Connection Failed: ' . mysqli_connect_error());
    die('Database Connection Error. Please contact administrator.');
}

// Set Character Set
mysqli_set_charset($conn, 'utf8mb4');

// Enable Error Reporting in Development
if (getenv('APP_ENV') === 'development') {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

// Function to Execute Prepared Statements Safely
function executeQuery($conn, $query, $params = [], $types = '') {
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        error_log('Query Preparation Error: ' . mysqli_error($conn));
        return false;
    }
    
    if (!empty($params) && !empty($types)) {
        if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
            error_log('Parameter Binding Error: ' . mysqli_stmt_error($stmt));
            return false;
        }
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Query Execution Error: ' . mysqli_stmt_error($stmt));
        return false;
    }
    
    return $stmt;
}

// Function to Fetch Results
function fetchResults($stmt) {
    $result = mysqli_stmt_get_result($stmt);
    $data = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $data;
}

// Function to Fetch Single Result
function fetchSingle($stmt) {
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

// Function for Insert/Update/Delete Operations
function executeModify($stmt) {
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected > 0;
}

?>
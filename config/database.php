<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'esports_system');
define('DB_PORT', 3306); 


function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
   
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    
    $conn->set_charset("utf8mb4");
    
    return $conn;
}


function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}


function sanitize($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}


function executeQuery($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) {
        error_log("Query Error: " . $conn->error);
        return false;
    }
    return $result;
}


function getSingleRow($conn, $sql) {
    $result = executeQuery($conn, $sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getAllRows($conn, $sql) {
    $result = executeQuery($conn, $sql);
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}
?>

<?php

$conn = new mysqli(
    "sql206.infinityfree.com",
    "if0_42865001",
    "mw0CYtAagY",
    "if0_42865001_northvault"
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>
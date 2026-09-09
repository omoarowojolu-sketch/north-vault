<?php

session_start();

// Remove session variables
$_SESSION = [];

// Destroy session
session_destroy();

// Return to login
header("Location: login.php");

exit;

?>
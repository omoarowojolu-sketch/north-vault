<?php

session_start();


// User must be logged in
if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");

    exit;
}


// User must be an admin
if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {

    http_response_code(403);

    die("
        <h1>403 - Access Denied</h1>
        <p>Administrators only.</p>
        <a href='../index.html'>Return to North Vault</a>
    ");

}

?>
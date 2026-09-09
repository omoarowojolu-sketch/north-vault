<?php

session_start();


// Check if logged in
if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");

    exit;
}


// Admin should not use user dashboard
if ($_SESSION["role"] === "admin") {

    header("Location: ../admin/dashboard.php");

    exit;
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>North Vault | My Account</title>

    <style>

        body {
            margin: 0;

            font-family: Arial, sans-serif;

            background: #f5f5f5;
        }

        header {

            background: #111;

            color: white;

            padding: 20px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }

        header a {

            color: white;

            text-decoration: none;
        }

        .container {

            padding: 40px;
        }

        .card {

            background: white;

            padding: 25px;

            border-radius: 10px;

            max-width: 600px;
        }

        .buttons {

            margin-top: 25px;
        }

        .buttons a {

            display: inline-block;

            padding: 12px 18px;

            background: #111;

            color: white;

            text-decoration: none;

            border-radius: 7px;

            margin-right: 10px;
        }

    </style>

</head>

<body>

<header>

    <strong>
        NORTH VAULT
    </strong>

    <a href="../auth/logout.php">
        Logout
    </a>

</header>

<div class="container">

    <div class="card">

        <h1>
            Welcome,
            <?= htmlspecialchars($_SESSION["full_name"]) ?>
        </h1>

        <p>
            You are logged in as a customer.
        </p>

        <p>
            Email:
            <?= htmlspecialchars($_SESSION["email"]) ?>
        </p>

        <div class="buttons">

            <a href="../index.html">
                Continue Shopping
            </a>

            <a href="../cart.html">
                My Cart
            </a>

        </div>

    </div>

</div>

</body>

</html>
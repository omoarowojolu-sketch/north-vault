<?php

session_start();

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, full_name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Verify password
            if (
                password_verify(
                    $password,
                    $user["password"]
                )
            ) {

                // Create new session ID
                session_regenerate_id(true);

                $_SESSION["user_id"] =
                    $user["id"];

                $_SESSION["full_name"] =
                    $user["full_name"];

                $_SESSION["email"] =
                    $user["email"];

                $_SESSION["role"] =
                    $user["role"];


                // ADMIN
                if ($user["role"] === "admin") {

                    header(
                        "Location: ../admin/dashboard.php"
                    );

                }

                // USER
                else {

                    header(
                        "Location: ../user/dashboard.php"
                    );

                }

                exit;

            }

            else {

                $message =
                    "Incorrect email or password.";

            }

        }

        else {

            $message =
                "Incorrect email or password.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>North Vault | Login</title>

    <style>

        body {
            font-family: Arial, sans-serif;

            background: #f5f5f5;

            margin: 0;

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;
        }

        .login-box {

            width: 380px;

            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 10px 30px rgba(0,0,0,0.1);
        }

        .logo {

            text-align: center;

            font-size: 27px;

            font-weight: bold;

            margin-bottom: 25px;
        }

        .logo span {
            color: #777;
        }

        label {

            display: block;

            font-weight: bold;

            margin-top: 15px;

            margin-bottom: 6px;
        }

        input {

            width: 100%;

            box-sizing: border-box;

            padding: 13px;

            border: 1px solid #ddd;

            border-radius: 7px;

            font-size: 15px;
        }

        button {

            width: 100%;

            padding: 14px;

            margin-top: 25px;

            border: none;

            border-radius: 7px;

            background: #111;

            color: white;

            font-size: 16px;

            cursor: pointer;
        }

        button:hover {
            background: #333;
        }

        .message {

            background: #ffecec;

            color: #c00;

            padding: 12px;

            border-radius: 7px;

            margin-bottom: 15px;
        }

        .register {

            text-align: center;

            margin-top: 20px;
        }

        a {

            color: #111;

            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        NORTH <span>VAULT</span>
    </div>

    <h2>Login</h2>

    <?php if (!empty($message)): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

    <div class="register">

        Don't have an account?

        <a href="register.php">
            Create Account
        </a>

    </div>

</div>

</body>

</html>
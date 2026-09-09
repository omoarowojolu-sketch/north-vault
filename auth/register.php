<?php

session_start();

require_once "../databases.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check empty fields
    if (
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $message = "Please fill in all fields.";

    }

    // Check passwords
    elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    }

    // Check password length
    elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";

    }

    else {

        // Check if email already exists
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "This email is already registered.";

        }

        else {

            // Secure password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Every new account is a normal user
            $role = "user";

            $stmt = $conn->prepare(
                "INSERT INTO users
                (full_name, email, password, role)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $hashed_password,
                $role
            );

            if ($stmt->execute()) {

                header("Location: login.php?registered=1");
                exit;

            } else {

                $message = "Registration failed.";

            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>North Vault | Register</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
        }

        .register-box {
            width: 400px;
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

        h2 {
            margin-bottom: 20px;
        }

        label {
            display: block;

            margin-top: 15px;
            margin-bottom: 6px;

            font-weight: bold;
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

            margin-top: 25px;

            padding: 14px;

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

        .login {
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

<div class="register-box">

    <div class="logo">
        NORTH <span>VAULT</span>
    </div>

    <h2>Create Account</h2>

    <?php if (!empty($message)): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Full Name</label>

        <input
            type="text"
            name="full_name"
            placeholder="Enter your full name"
            required
        >

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
            placeholder="Create a password"
            required
        >

        <label>Confirm Password</label>

        <input
            type="password"
            name="confirm_password"
            placeholder="Confirm your password"
            required
        >

        <button type="submit">
            Create Account
        </button>

    </form>

    <div class="login">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </div>

</div>

</body>

</html>
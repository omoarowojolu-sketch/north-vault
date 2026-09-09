<?php

require_once "../auth/check_admin.php";
require_once "../config/database.php";


// Get all users
$query = $conn->query("
    SELECT id, full_name, email, role, created_at
    FROM users
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>North Vault | Manage Users</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f8;
            color: #222;
        }

        .header {
            background: #111;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .logo span {
            color: #999;
        }

        .back {
            color: white;
            text-decoration: none;
        }

        .container {
            padding: 30px;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #111;
            color: white;
        }

        tr:hover {
            background: #f8f8f8;
        }

        .admin {
            color: #fff;
            background: #111;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .customer {
            color: #333;
            background: #eee;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }

    </style>

</head>


<body>


<header class="header">

    <div class="logo">
        NORTH <span>VAULT</span>
    </div>

    <a
        class="back"
        href="dashboard.php"
    >
        ← Dashboard
    </a>

</header>


<main class="container">

    <h1>Manage Users</h1>

    <p class="subtitle">
        View all registered North Vault customers and administrators.
    </p>


    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Email</th>

                    <th>Role</th>

                    <th>Registered</th>

                </tr>

            </thead>


            <tbody>

                <?php if ($query->num_rows > 0): ?>

                    <?php while ($user = $query->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo $user["id"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["full_name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["email"]); ?>
                            </td>

                            <td>

                                <?php if ($user["role"] === "admin"): ?>

                                    <span class="admin">
                                        ADMIN
                                    </span>

                                <?php else: ?>

                                    <span class="customer">
                                        CUSTOMER
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?php echo $user["created_at"]; ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="5"
                            class="empty"
                        >
                            No users registered yet.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>


</body>

</html>

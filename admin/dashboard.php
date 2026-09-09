<?php

require_once "../auth/check_admin.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| NORTH VAULT ADMIN DASHBOARD
|--------------------------------------------------------------------------
| Uses your existing authentication and database connection.
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminName = $_SESSION["full_name"] ?? "Administrator";

/*
|--------------------------------------------------------------------------
| HELPER FUNCTIONS
|--------------------------------------------------------------------------
*/

function tableExists($conn, $table)
{
    $table = $conn->real_escape_string($table);

    $result = $conn->query("SHOW TABLES LIKE '$table'");

    return $result && $result->num_rows > 0;
}

function columnExists($conn, $table, $column)
{
    if (!tableExists($conn, $table)) {
        return false;
    }

    $table = str_replace("`", "", $table);
    $column = $conn->real_escape_string($column);

    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");

    return $result && $result->num_rows > 0;
}

function getCount($conn, $table)
{
    if (!tableExists($conn, $table)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_assoc();

    return (int)($row["total"] ?? 0);
}

/*
|--------------------------------------------------------------------------
| TOTAL USERS
|--------------------------------------------------------------------------
*/

$totalUsers = getCount($conn, "users");

/*
|--------------------------------------------------------------------------
| TOTAL PRODUCTS
|--------------------------------------------------------------------------
*/

$totalProducts = getCount($conn, "products");

/*
|--------------------------------------------------------------------------
| TOTAL ORDERS
|--------------------------------------------------------------------------
*/

$totalOrders = 0;

if (tableExists($conn, "orders")) {
    $totalOrders = getCount($conn, "orders");
}

/*
|--------------------------------------------------------------------------
| REVENUE
|--------------------------------------------------------------------------
*/

$totalRevenue = 0;

/*
| First try payments table
*/

if (tableExists($conn, "payments")) {

    $amountColumn = null;

    $possibleAmountColumns = [
        "amount",
        "total_amount",
        "paid_amount",
        "amount_paid"
    ];

    foreach ($possibleAmountColumns as $column) {
        if (columnExists($conn, "payments", $column)) {
            $amountColumn = $column;
            break;
        }
    }

    if ($amountColumn) {

        $sql = "SELECT SUM(`$amountColumn`) AS revenue FROM payments";

        /*
        | If payment status exists, only count successful payments
        */

        if (columnExists($conn, "payments", "status")) {
            $sql .= " WHERE status IN ('successful', 'success', 'completed', 'paid')";
        }

        $result = $conn->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            $totalRevenue = (float)($row["revenue"] ?? 0);
        }
    }
}

/*
|--------------------------------------------------------------------------
| If there is no payments table, try orders table
|--------------------------------------------------------------------------
*/

if ($totalRevenue == 0 && tableExists($conn, "orders")) {

    $amountColumn = null;

    $possibleAmountColumns = [
        "total_amount",
        "total",
        "amount",
        "grand_total"
    ];

    foreach ($possibleAmountColumns as $column) {
        if (columnExists($conn, "orders", $column)) {
            $amountColumn = $column;
            break;
        }
    }

    if ($amountColumn) {

        $sql = "SELECT SUM(`$amountColumn`) AS revenue FROM orders";

        if (columnExists($conn, "orders", "payment_status")) {
            $sql .= " WHERE payment_status IN ('successful', 'success', 'completed', 'paid')";
        } elseif (columnExists($conn, "orders", "status")) {
            $sql .= " WHERE status IN ('paid', 'completed', 'successful', 'success')";
        }

        $result = $conn->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            $totalRevenue = (float)($row["revenue"] ?? 0);
        }
    }
}

/*
|--------------------------------------------------------------------------
| RECENT PAYMENTS
|--------------------------------------------------------------------------
*/

$recentPayments = [];

if (tableExists($conn, "payments")) {

    $idColumn = columnExists($conn, "payments", "id") ? "id" : null;

    $amountColumn = null;

    foreach (
        ["amount", "total_amount", "paid_amount", "amount_paid"]
        as $column
    ) {
        if (columnExists($conn, "payments", $column)) {
            $amountColumn = $column;
            break;
        }
    }

    $statusColumn = columnExists($conn, "payments", "status")
        ? "status"
        : null;

    $referenceColumn = null;

    foreach (
        ["transaction_id", "tx_ref", "reference", "payment_reference"]
        as $column
    ) {
        if (columnExists($conn, "payments", $column)) {
            $referenceColumn = $column;
            break;
        }
    }

    $dateColumn = null;

    foreach (
        ["created_at", "paid_at", "created"]
        as $column
    ) {
        if (columnExists($conn, "payments", $column)) {
            $dateColumn = $column;
            break;
        }
    }

    if ($amountColumn) {

        $select = [];

        $select[] = "`$amountColumn` AS amount";

        if ($idColumn) {
            $select[] = "`$idColumn` AS id";
        } else {
            $select[] = "0 AS id";
        }

        if ($statusColumn) {
            $select[] = "`$statusColumn` AS status";
        } else {
            $select[] = "'successful' AS status";
        }

        if ($referenceColumn) {
            $select[] = "`$referenceColumn` AS reference";
        } else {
            $select[] = "'' AS reference";
        }

        if ($dateColumn) {
            $select[] = "`$dateColumn` AS created_at";
        } else {
            $select[] = "NULL AS created_at";
        }

        $sql = "SELECT " . implode(", ", $select) . " FROM payments";

        if ($dateColumn) {
            $sql .= " ORDER BY `$dateColumn` DESC";
        } elseif ($idColumn) {
            $sql .= " ORDER BY `$idColumn` DESC";
        }

        $sql .= " LIMIT 8";

        $result = $conn->query($sql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recentPayments[] = $row;
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| TODAY'S REVENUE
|--------------------------------------------------------------------------
*/

$todayRevenue = 0;

if (tableExists($conn, "payments")) {

    $amountColumn = null;

    foreach (
        ["amount", "total_amount", "paid_amount", "amount_paid"]
        as $column
    ) {
        if (columnExists($conn, "payments", $column)) {
            $amountColumn = $column;
            break;
        }
    }

    $dateColumn = null;

    foreach (
        ["created_at", "paid_at", "created"]
        as $column
    ) {
        if (columnExists($conn, "payments", $column)) {
            $dateColumn = $column;
            break;
        }
    }

    if ($amountColumn && $dateColumn) {

        $sql = "
            SELECT SUM(`$amountColumn`) AS total
            FROM payments
            WHERE DATE(`$dateColumn`) = CURDATE()
        ";

        if (columnExists($conn, "payments", "status")) {
            $sql .= "
                AND status IN
                ('successful', 'success', 'completed', 'paid')
            ";
        }

        $result = $conn->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            $todayRevenue = (float)($row["total"] ?? 0);
        }
    }
}

/*
|--------------------------------------------------------------------------
| UNREAD / RECENT PAYMENT COUNT
|--------------------------------------------------------------------------
*/

$notificationCount = 0;

if (tableExists($conn, "payments")) {

    if (columnExists($conn, "payments", "status")) {

        $result = $conn->query("
            SELECT COUNT(*) AS total
            FROM payments
            WHERE status IN ('successful', 'success', 'completed', 'paid')
        ");

        if ($result) {
            $row = $result->fetch_assoc();
            $notificationCount = (int)($row["total"] ?? 0);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>North Vault | Admin Dashboard</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        :root {
            --dark: #111827;
            --dark2: #1f2937;
            --gold: #c8a951;
            --gold-light: #f5edcf;
            --white: #ffffff;
            --background: #f4f6f9;
            --text: #172033;
            --muted: #70798a;
            --border: #e6e9ef;
            --green: #16a34a;
            --red: #dc2626;
            --blue: #2563eb;
            --orange: #ea580c;
        }

        body {
            background: var(--background);
            color: var(--text);
        }

        a {
            text-decoration: none;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: var(--dark);
            color: white;
            padding: 25px 15px;
            z-index: 1000;
        }

        .brand {
            padding: 5px 15px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .brand h2 {
            font-size: 24px;
            letter-spacing: 1px;
        }

        .brand span {
            color: var(--gold);
        }

        .brand small {
            display: block;
            color: #9ca3af;
            margin-top: 5px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .menu-title {
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            margin: 20px 15px 10px;
            letter-spacing: 1px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 13px;
            color: #d1d5db;
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            font-size: 14px;
            transition: 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #252d3a;
            color: white;
        }

        .sidebar a.active {
            border-left: 3px solid var(--gold);
        }

        .logout-link {
            margin-top: 30px;
            color: #f87171 !important;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 250px;
            min-height: 100vh;
        }

        /* =========================
           TOP BAR
        ========================= */

        .topbar {
            height: 75px;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 35px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .page-title h1 {
            font-size: 20px;
        }

        .page-title p {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            font-size: 18px;
        }

        .notification:hover {
            background: var(--gold-light);
        }

        .notification-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            background: var(--red);
            color: white;
            font-size: 9px;
            min-width: 17px;
            height: 17px;
            padding: 2px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gold);
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .admin-profile strong {
            font-size: 13px;
        }

        .admin-profile small {
            display: block;
            color: var(--muted);
            font-size: 11px;
            margin-top: 2px;
        }

        /* =========================
           CONTENT
        ========================= */

        .content {
            padding: 30px 35px;
        }

        .welcome {
            margin-bottom: 30px;
        }

        .welcome h2 {
            font-size: 25px;
            margin-bottom: 7px;
        }

        .welcome p {
            color: var(--muted);
            font-size: 14px;
        }

        /* =========================
           STAT CARDS
        ========================= */

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            transition: 0.25s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.07);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .icon-users {
            background: #e0e7ff;
        }

        .icon-products {
            background: #dcfce7;
        }

        .icon-orders {
            background: #ffedd5;
        }

        .icon-money {
            background: #fef3c7;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
        }

        .stat-number {
            font-size: 27px;
            font-weight: bold;
        }

        .stat-footer {
            margin-top: 10px;
            font-size: 11px;
            color: var(--muted);
        }

        /* =========================
           SECOND ROW
        ========================= */

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .panel-header {
            padding: 20px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h3 {
            font-size: 16px;
        }

        .view-all {
            color: var(--gold-dark, #a88932);
            font-size: 12px;
            font-weight: bold;
        }

        /* =========================
           PAYMENT TABLE
        ========================= */

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #f0f1f4;
            font-size: 12px;
            white-space: nowrap;
        }

        th {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            background: #fafafa;
        }

        td {
            color: #374151;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status-success {
            background: #dcfce7;
            color: #15803d;
        }

        .status-pending {
            background: #fef3c7;
            color: #a16207;
        }

        .status-failed {
            background: #fee2e2;
            color: #b91c1c;
        }

        .empty {
            padding: 45px 20px;
            text-align: center;
            color: var(--muted);
        }

        .empty-icon {
            font-size: 35px;
            margin-bottom: 10px;
        }

        /* =========================
           QUICK SUMMARY
        ========================= */

        .summary-list {
            padding: 5px 20px 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 17px 0;
            border-bottom: 1px solid #f0f1f4;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .summary-icon {
            width: 36px;
            height: 36px;
            background: #f4f5f7;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .summary-text strong {
            display: block;
            font-size: 12px;
        }

        .summary-text small {
            color: var(--muted);
            font-size: 10px;
        }

        .summary-value {
            font-weight: bold;
            font-size: 13px;
        }

        /* =========================
           QUICK ACTIONS
        ========================= */

        .actions-panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
        }

        .actions-panel h3 {
            margin-bottom: 18px;
            font-size: 16px;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
        }

        .action {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px 10px;
            text-align: center;
            color: var(--text);
            background: #fff;
            transition: 0.2s;
        }

        .action:hover {
            border-color: var(--gold);
            background: #fffdf5;
            transform: translateY(-2px);
        }

        .action-icon {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .action span {
            display: block;
            font-size: 11px;
            font-weight: bold;
        }

        /* =========================
           REVENUE BANNER
        ========================= */

        .revenue-banner {
            margin-top: 25px;
            background: var(--dark);
            color: white;
            border-radius: 14px;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .revenue-banner p {
            color: #9ca3af;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .revenue-banner h2 {
            font-size: 25px;
        }

        .revenue-right {
            text-align: right;
        }

        .revenue-right strong {
            color: var(--gold);
            font-size: 18px;
        }

        /* =========================
           MOBILE
        ========================= */

        .mobile-menu {
            display: none;
        }

        @media (max-width: 1100px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .actions {
                grid-template-columns: repeat(3, 1fr);
            }

        }

        @media (max-width: 850px) {

            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 650px) {

            .sidebar {
                display: none;
            }

            .main {
                margin-left: 0;
            }

            .topbar {
                padding: 0 15px;
            }

            .content {
                padding: 20px 15px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .admin-profile div {
                display: none;
            }

            .revenue-banner {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .revenue-right {
                text-align: left;
            }

        }

    </style>

</head>

<body>

<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">

    <div class="brand">

        <h2>
            NORTH <span>VAULT</span>
        </h2>

        <small>
            Administration
        </small>

    </div>

    <div class="menu-title">
        Main
    </div>

    <a href="dashboard.php" class="active">
        📊
        Dashboard
    </a>

    <a href="products.php">
        📦
        Products
    </a>

    <a href="orders.php">
        🛒
        Orders
    </a>

    <a href="payments.php">
        💳
        Payments
    </a>

    <div class="menu-title">
        Management
    </div>

    <a href="user.php">
        👥
        Users
    </a>

    <a href="reports.php">
        📈
        Reports
    </a>

    <a href="settings.php">
        ⚙️
        Settings
    </a>

    <a
        href="../auth/logout.php"
        class="logout-link"
    >
        🚪
        Logout
    </a>

</aside>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="main">


    <!-- TOP BAR -->

    <header class="topbar">

        <div class="page-title">

            <h1>
                Dashboard
            </h1>

            <p>
                North Vault administration panel
            </p>

        </div>


        <div class="top-actions">

            <a
                href="payments.php"
                class="notification"
                title="Payment notifications"
            >

                🔔

                <?php if ($notificationCount > 0): ?>

                    <span class="notification-badge">
                        <?php echo $notificationCount > 99
                            ? "99+"
                            : $notificationCount; ?>
                    </span>

                <?php endif; ?>

            </a>


            <div class="admin-profile">

                <div class="avatar">

                    <?php
                    echo strtoupper(
                        substr($adminName, 0, 1)
                    );
                    ?>

                </div>

                <div>

                    <strong>
                        <?php
                        echo htmlspecialchars($adminName);
                        ?>
                    </strong>

                    <small>
                        Administrator
                    </small>

                </div>

            </div>

        </div>

    </header>


    <!-- CONTENT -->

    <main class="content">


        <!-- WELCOME -->

        <section class="welcome">

            <h2>
                Welcome back,
                <?php
                echo htmlspecialchars($adminName);
                ?> 👋
            </h2>

            <p>
                Here's what's happening with your North Vault store today.
            </p>

        </section>


        <!-- =====================================================
             STAT CARDS
        ====================================================== -->

        <section class="stats">


            <div class="stat-card">

                <div class="stat-top">

                    <div>
                        <div class="stat-label">
                            Total Users
                        </div>

                        <div class="stat-number">
                            <?php echo number_format($totalUsers); ?>
                        </div>
                    </div>

                    <div class="stat-icon icon-users">
                        👥
                    </div>

                </div>

                <div class="stat-footer">
                    Registered customers
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <div>
                        <div class="stat-label">
                            Total Products
                        </div>

                        <div class="stat-number">
                            <?php echo number_format($totalProducts); ?>
                        </div>
                    </div>

                    <div class="stat-icon icon-products">
                        📦
                    </div>

                </div>

                <div class="stat-footer">
                    Products in your store
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <div>
                        <div class="stat-label">
                            Total Orders
                        </div>

                        <div class="stat-number">
                            <?php echo number_format($totalOrders); ?>
                        </div>
                    </div>

                    <div class="stat-icon icon-orders">
                        🛒
                    </div>

                </div>

                <div class="stat-footer">
                    Orders received
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <div>
                        <div class="stat-label">
                            Total Revenue
                        </div>

                        <div class="stat-number">
                            ₦<?php echo number_format(
                                $totalRevenue,
                                2
                            ); ?>
                        </div>
                    </div>

                    <div class="stat-icon icon-money">
                        💰
                    </div>

                </div>

                <div class="stat-footer">
                    Verified store revenue
                </div>

            </div>


        </section>


        <!-- =====================================================
             PAYMENT + SUMMARY
        ====================================================== -->

        <section class="dashboard-grid">


            <!-- RECENT PAYMENTS -->

            <div class="panel">

                <div class="panel-header">

                    <h3>
                        Recent Payments
                    </h3>

                    <a
                        href="payments.php"
                        class="view-all"
                    >
                        View All →
                    </a>

                </div>


                <?php if (count($recentPayments) > 0): ?>

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>
                                        Reference
                                    </th>

                                    <th>
                                        Amount
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php foreach ($recentPayments as $payment): ?>

                                <?php

                                $status =
                                    strtolower(
                                        $payment["status"] ?? "successful"
                                    );

                                $statusClass = "status-pending";

                                if (
                                    in_array(
                                        $status,
                                        [
                                            "successful",
                                            "success",
                                            "completed",
                                            "paid"
                                        ]
                                    )
                                ) {
                                    $statusClass = "status-success";
                                }

                                if (
                                    in_array(
                                        $status,
                                        [
                                            "failed",
                                            "cancelled",
                                            "canceled"
                                        ]
                                    )
                                ) {
                                    $statusClass = "status-failed";
                                }

                                ?>

                                <tr>

                                    <td>

                                        <?php

                                        $reference =
                                            $payment["reference"] ?? "";

                                        echo htmlspecialchars(
                                            $reference !== ""
                                                ? $reference
                                                : "Payment #"
                                                    . $payment["id"]
                                        );

                                        ?>

                                    </td>

                                    <td>

                                        <strong>
                                            ₦<?php
                                            echo number_format(
                                                (float)$payment["amount"],
                                                2
                                            );
                                            ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <span
                                            class="status <?php
                                            echo $statusClass;
                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $status
                                            );
                                            ?>

                                        </span>

                                    </td>

                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $payment["created_at"]
                                            )
                                        ) {

                                            echo date(
                                                "d M Y, h:i A",
                                                strtotime(
                                                    $payment["created_at"]
                                                )
                                            );

                                        } else {

                                            echo "—";

                                        }

                                        ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty">

                        <div class="empty-icon">
                            💳
                        </div>

                        <strong>
                            No payments yet
                        </strong>

                        <p style="margin-top: 5px;">
                            Flutterwave payments will appear here after
                            they are saved in your database.
                        </p>

                    </div>

                <?php endif; ?>

            </div>


            <!-- STORE SUMMARY -->

            <div class="panel">

                <div class="panel-header">

                    <h3>
                        Store Summary
                    </h3>

                </div>


                <div class="summary-list">


                    <div class="summary-item">

                        <div class="summary-left">

                            <div class="summary-icon">
                                👥
                            </div>

                            <div class="summary-text">

                                <strong>
                                    Customers
                                </strong>

                                <small>
                                    Registered users
                                </small>

                            </div>

                        </div>

                        <div class="summary-value">
                            <?php echo number_format($totalUsers); ?>
                        </div>

                    </div>


                    <div class="summary-item">

                        <div class="summary-left">

                            <div class="summary-icon">
                                📦
                            </div>

                            <div class="summary-text">

                                <strong>
                                    Products
                                </strong>

                                <small>
                                    Active inventory
                                </small>

                            </div>

                        </div>

                        <div class="summary-value">
                            <?php echo number_format($totalProducts); ?>
                        </div>

                    </div>


                    <div class="summary-item">

                        <div class="summary-left">

                            <div class="summary-icon">
                                🛒
                            </div>

                            <div class="summary-text">

                                <strong>
                                    Orders
                                </strong>

                                <small>
                                    Store orders
                                </small>

                            </div>

                        </div>

                        <div class="summary-value">
                            <?php echo number_format($totalOrders); ?>
                        </div>

                    </div>


                    <div class="summary-item">

                        <div class="summary-left">

                            <div class="summary-icon">
                                💰
                            </div>

                            <div class="summary-text">

                                <strong>
                                    Today's Revenue
                                </strong>

                                <small>
                                    Successful payments
                                </small>

                            </div>

                        </div>

                        <div class="summary-value">
                            ₦<?php echo number_format(
                                $todayRevenue,
                                2
                            ); ?>
                        </div>

                    </div>


                </div>

            </div>


        </section>


        <!-- =====================================================
             QUICK ACTIONS
        ====================================================== -->

        <section class="actions-panel">

            <h3>
                Quick Management
            </h3>

            <div class="actions">


                <a
                    href="products.php"
                    class="action"
                >

                    <div class="action-icon">
                        📦
                    </div>

                    <span>
                        Products
                    </span>

                </a>


                <a
                    href="orders.php"
                    class="action"
                >

                    <div class="action-icon">
                        🛒
                    </div>

                    <span>
                        Orders
                    </span>

                </a>


                <a
                    href="payments.php"
                    class="action"
                >

                    <div class="action-icon">
                        💳
                    </div>

                    <span>
                        Payments
                    </span>

                </a>


                <a
                    href="users.php"
                    class="action"
                >

                    <div class="action-icon">
                        👥
                    </div>

                    <span>
                        Users
                    </span>

                </a>


                <a
                    href="reports.php"
                    class="action"
                >

                    <div class="action-icon">
                        📈
                    </div>

                    <span>
                        Reports
                    </span>

                </a>


                <a
                    href="settings.php"
                    class="action"
                >

                    <div class="action-icon">
                        ⚙️
                    </div>

                    <span>
                        Settings
                    </span>

                </a>


            </div>

        </section>


        <!-- =====================================================
             REVENUE BANNER
        ====================================================== -->

        <section class="revenue-banner">

            <div>

                <p>
                    TOTAL VERIFIED REVENUE
                </p>

                <h2>
                    ₦<?php echo number_format(
                        $totalRevenue,
                        2
                    ); ?>
                </h2>

            </div>


            <div class="revenue-right">

                <p>
                    TODAY'S SALES
                </p>

                <strong>
                    ₦<?php echo number_format(
                        $todayRevenue,
                        2
                    ); ?>
                </strong>

            </div>

        </section>


    </main>

</div>


<script>

/*
|--------------------------------------------------------------------------
| AUTO REFRESH PAYMENT NOTIFICATION
|--------------------------------------------------------------------------
| Refreshes the dashboard every 60 seconds so new payment information
| can appear without manually refreshing the page.
|--------------------------------------------------------------------------
*/

setTimeout(function () {
    window.location.reload();
}, 60000);


/*
|--------------------------------------------------------------------------
| NOTIFICATION BUTTON
|--------------------------------------------------------------------------
*/

const notification =
    document.querySelector(".notification");

if (notification) {

    notification.addEventListener("click", function () {

        // Allow normal navigation to payments.php

    });

}

</script>


</body>
</html>
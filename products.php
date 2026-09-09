<?php

require_once "../auth/check_admin.php";
require_once "../config/database.php";


// Get all products
$query = $conn->query("
    SELECT id, name, description, price, image, category, stock, created_at
    FROM products
    ORDER BY id DESC
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>North Vault | Manage Products</title>


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


        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 25px;
        }


        h1 {
            margin-bottom: 7px;
        }


        .subtitle {
            color: #777;
        }


        .add-btn {
            background: #111;
            color: white;

            padding: 12px 18px;

            border-radius: 7px;

            text-decoration: none;
            font-weight: bold;
        }


        .add-btn:hover {
            background: #333;
        }


        .table-container {
            background: white;

            padding: 20px;

            border-radius: 12px;

            box-shadow:
                0 3px 12px rgba(0,0,0,0.08);

            overflow-x: auto;
        }


        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 900px;
        }


        th,
        td {
            padding: 14px;

            text-align: left;

            border-bottom: 1px solid #eee;
        }


        th {
            background: #111;
            color: white;
        }


        tr:hover {
            background: #fafafa;
        }


        .product-image {
            width: 70px;
            height: 70px;

            object-fit: cover;

            border-radius: 8px;

            border: 1px solid #ddd;
        }


        .no-image {
            width: 70px;
            height: 70px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eee;

            border-radius: 8px;

            font-size: 12px;
            color: #777;

            text-align: center;
        }


        .price {
            font-weight: bold;
        }


        .stock {
            font-weight: bold;
        }


        .empty {
            text-align: center;

            padding: 40px;

            color: #777;
        }


        @media (max-width: 700px) {

            .container {
                padding: 15px;
            }

            .top {
                align-items: flex-start;
                gap: 15px;
                flex-direction: column;
            }

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


    <div class="top">


        <div>

            <h1>
                Manage Products
            </h1>

            <p class="subtitle">
                View and manage products in your store.
            </p>

        </div>


        <a
            class="add-btn"
            href="add-product.php"
        >

            + Add Product

        </a>


    </div>



    <div class="table-container">


        <table>


            <thead>

                <tr>

                    <th>
                        Picture
                    </th>

                    <th>
                        Product
                    </th>

                    <th>
                        Category
                    </th>

                    <th>
                        Price
                    </th>

                    <th>
                        Stock
                    </th>

                    <th>
                        Date Added
                    </th>

                    <th>
                        Action
                    </th>     
                </tr>

            </thead>



            <tbody>


                <?php if ($query->num_rows > 0): ?>


                    <?php while ($product = $query->fetch_assoc()): ?>


                        <tr>


                            <td>


                                <?php if (!empty($product["image"])): ?>

                                    <img
                                        class="product-image"
                                        src="../images/products/<?php echo htmlspecialchars($product["image"]); ?>"
                                        alt="<?php echo htmlspecialchars($product["name"]); ?>"
                                    >

                                <?php else: ?>

                                    <div class="no-image">
                                        No Image
                                    </div>

                                <?php endif; ?>


                            </td>



                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["name"]
                                    );
                                    ?>

                                </strong>

                            </td>



                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $product["category"]
                                );
                                ?>

                            </td>



                            <td class="price">

                                ₦<?php
                                echo number_format(
                                    $product["price"],
                                    2
                                );
                                ?>

                            </td>



                            <td class="stock">

                                <?php
                                echo $product["stock"];
                                ?>

                            </td>



                            <td>

                                <?php
                                echo $product["created_at"];
                                ?>

                            </td>
                            <td>

    <a
        href="edit-product.php?id=<?php echo $product["id"]; ?>"
        style="
            color: #111;
            font-weight: bold;
            text-decoration: none;
            margin-right: 15px;
        "
    >
        Edit
    </a>


    <a
        href="delete-product.php?id=<?php echo $product["id"]; ?>"
        onclick="return confirm('Are you sure you want to delete this product?');"
        style="
            color: #b00000;
            font-weight: bold;
            text-decoration: none;
        "
    >
        Delete
    </a>

</td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="7"
                            class="empty"
                        >

                            No products have been added yet.

                        </td>

                    </tr>


                <?php endif; ?>


            </tbody>


        </table>


    </div>


</main>


</body>

</html>

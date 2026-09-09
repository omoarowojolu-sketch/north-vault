<?php

require_once "../auth/check_admin.php";
require_once "../config/database.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = $_POST["price"];
    $category = trim($_POST["category"]);
    $stock = $_POST["stock"];

    // --------------------------------
    // BASIC VALIDATION
    // --------------------------------

    if (
        empty($name) ||
        empty($price) ||
        empty($category)
    ) {

        $message = "Please fill in all required fields.";
        $messageType = "error";

    } else {

        $imageName = null;

        // --------------------------------
        // CHECK IF IMAGE WAS UPLOADED
        // --------------------------------

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {

            $image = $_FILES["image"];

            // Maximum file size: 5MB
            if ($image["size"] > 5 * 1024 * 1024) {

                $message = "Image must not be larger than 5MB.";
                $messageType = "error";

            } else {

                // Allowed image types
                $allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                $fileType = mime_content_type($image["tmp_name"]);

                if (!in_array($fileType, $allowedTypes)) {

                    $message = "Only JPG, PNG and WEBP images are allowed.";
                    $messageType = "error";

                } else {

                    // --------------------------------
                    // CREATE UNIQUE IMAGE NAME
                    // --------------------------------

                    $extension = strtolower(
                        pathinfo(
                            $image["name"],
                            PATHINFO_EXTENSION
                        )
                    );

                    $imageName =
                        uniqid("product_", true)
                        . "."
                        . $extension;

                    $uploadDirectory =
                        "../images/products/";

                    $uploadPath =
                        $uploadDirectory . $imageName;


                    // --------------------------------
                    // MOVE IMAGE
                    // --------------------------------

                    if (
                        move_uploaded_file(
                            $image["tmp_name"],
                            $uploadPath
                        )
                    ) {

                        // --------------------------------
                        // SAVE PRODUCT TO DATABASE
                        // --------------------------------

                        $stmt = $conn->prepare("
                            INSERT INTO products
                            (
                                name,
                                description,
                                price,
                                image,
                                category,
                                stock
                            )
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");

                        $stmt->bind_param(
                            "ssdssi",
                            $name,
                            $description,
                            $price,
                            $imageName,
                            $category,
                            $stock
                        );


                        if ($stmt->execute()) {

                            $message =
                                "Product and picture added successfully!";

                            $messageType = "success";

                            $stmt->close();

                        } else {

                            // Delete uploaded image
                            // if database insertion fails
                            if (file_exists($uploadPath)) {
                                unlink($uploadPath);
                            }

                            $message =
                                "Product could not be added.";

                            $messageType = "error";

                            $stmt->close();
                        }

                    } else {

                        $message =
                            "Failed to upload the product picture.";

                        $messageType = "error";

                    }

                }

            }

        } else {

            // --------------------------------
            // PRODUCT WITHOUT IMAGE
            // --------------------------------

            $stmt = $conn->prepare("
                INSERT INTO products
                (
                    name,
                    description,
                    price,
                    image,
                    category,
                    stock
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssdssi",
                $name,
                $description,
                $price,
                $imageName,
                $category,
                $stock
            );


            if ($stmt->execute()) {

                $message =
                    "Product added successfully!";

                $messageType = "success";

            } else {

                $message =
                    "Product could not be added.";

                $messageType = "error";

            }

            $stmt->close();

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

    <title>North Vault | Add Product</title>

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
            max-width: 750px;
            margin: 40px auto;
            padding: 20px;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 12px;

            box-shadow:
                0 3px 15px rgba(0,0,0,0.08);
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #777;
            margin-bottom: 25px;
        }

        .message {
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .success {
            background: #e8f7e8;
            color: #176b17;
        }

        .error {
            background: #ffe9e9;
            color: #a00000;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 13px;

            border: 1px solid #ddd;
            border-radius: 7px;

            margin-bottom: 20px;

            font-size: 15px;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        input[type="file"] {
            background: #fafafa;
            cursor: pointer;
        }

        .image-note {
            font-size: 13px;
            color: #777;
            margin-top: -14px;
            margin-bottom: 20px;
        }

        button {
            width: 100%;
            padding: 15px;

            background: #111;
            color: white;

            border: none;
            border-radius: 7px;

            font-size: 16px;
            font-weight: bold;

            cursor: pointer;
        }

        button:hover {
            background: #333;
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


<div class="container">

    <div class="form-box">

        <h1>Add Product</h1>

        <p class="subtitle">
            Add a new product to your North Vault store.
        </p>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $messageType; ?>">

                <?php
                    echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- PRODUCT NAME -->

            <label>
                Product Name
            </label>

            <input
                type="text"
                name="name"
                placeholder="Example: Nike Air Max"
                required
            >


            <!-- DESCRIPTION -->

            <label>
                Description
            </label>

            <textarea
                name="description"
                placeholder="Describe the product..."
            ></textarea>


            <!-- PRICE -->

            <label>
                Price (₦)
            </label>

            <input
                type="number"
                name="price"
                placeholder="85000"
                min="0"
                step="0.01"
                required
            >


            <!-- CATEGORY -->

            <label>
                Category
            </label>

            <select
                name="category"
                required
            >

                <option value="">
                    Select category
                </option>

                <option value="Fashion">
                    Fashion
                </option>

                <option value="Electronics">
                    Electronics
                </option>

                <option value="Beauty">
                    Beauty
                </option>

                <option value="Home">
                    Home
                </option>

                <option value="Shoes">
                    Shoes
                </option>

                <option value="Accessories">
                    Accessories
                </option>

                <option value="Other">
                    Other
                </option>

            </select>


            <!-- STOCK -->

            <label>
                Stock
            </label>

            <input
                type="number"
                name="stock"
                placeholder="20"
                min="0"
                required
            >


            <!-- IMAGE -->

            <label>
                Product Picture
            </label>

            <input
                type="file"
                name="image"
                accept=".jpg,.jpeg,.png,.webp"
            >

            <p class="image-note">
                JPG, PNG or WEBP. Maximum size: 5MB.
            </p>


            <!-- SUBMIT -->

            <button type="submit">
                Add Product
            </button>


        </form>

    </div>

</div>


</body>

</html>
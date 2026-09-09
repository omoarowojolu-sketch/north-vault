<?php

require_once "../auth/check_admin.php";
require_once "../config/database.php";


// ----------------------------------------
// CHECK PRODUCT ID
// ----------------------------------------

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid product ID.");
}

$productId = (int) $_GET["id"];


// ----------------------------------------
// GET PRODUCT
// ----------------------------------------

$stmt = $conn->prepare("
    SELECT *
    FROM products
    WHERE id = ?
");

$stmt->bind_param("i", $productId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("Product not found.");
}

$product = $result->fetch_assoc();

$stmt->close();


// ----------------------------------------
// UPDATE PRODUCT
// ----------------------------------------

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = $_POST["price"];
    $category = trim($_POST["category"]);
    $stock = $_POST["stock"];

    if (
        empty($name) ||
        empty($price) ||
        empty($category)
    ) {

        $message = "Please fill in all required fields.";
        $messageType = "error";

    } else {

        $imageName = $product["image"];

        // ----------------------------------------
        // CHECK FOR NEW IMAGE
        // ----------------------------------------

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {

            $image = $_FILES["image"];

            if ($image["size"] > 5 * 1024 * 1024) {

                $message = "Image must not be larger than 5MB.";
                $messageType = "error";

            } else {

                $allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                $fileType = mime_content_type(
                    $image["tmp_name"]
                );

                if (!in_array($fileType, $allowedTypes)) {

                    $message =
                        "Only JPG, PNG and WEBP images are allowed.";

                    $messageType = "error";

                } else {

                    $extension = strtolower(
                        pathinfo(
                            $image["name"],
                            PATHINFO_EXTENSION
                        )
                    );

                    $newImageName =
                        uniqid("product_", true)
                        . "."
                        . $extension;

                    $uploadPath =
                        "../images/products/"
                        . $newImageName;


                    if (
                        move_uploaded_file(
                            $image["tmp_name"],
                            $uploadPath
                        )
                    ) {

                        // Delete old image
                        if (!empty($product["image"])) {

                            $oldImage =
                                "../images/products/"
                                . $product["image"];

                            if (file_exists($oldImage)) {
                                unlink($oldImage);
                            }

                        }

                        $imageName = $newImageName;

                    } else {

                        $message =
                            "Failed to upload the new picture.";

                        $messageType = "error";

                    }

                }

            }

        }


        // ----------------------------------------
        // SAVE CHANGES
        // ----------------------------------------

        if (empty($message)) {

            $stmt = $conn->prepare("
                UPDATE products
                SET
                    name = ?,
                    description = ?,
                    price = ?,
                    image = ?,
                    category = ?,
                    stock = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "ssdssii",
                $name,
                $description,
                $price,
                $imageName,
                $category,
                $stock,
                $productId
            );


            if ($stmt->execute()) {

                $message =
                    "Product updated successfully!";

                $messageType = "success";


                // Update displayed product information
                $product["name"] = $name;
                $product["description"] = $description;
                $product["price"] = $price;
                $product["image"] = $imageName;
                $product["category"] = $category;
                $product["stock"] = $stock;

            } else {

                $message =
                    "Failed to update product.";

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

    <title>
        North Vault | Edit Product
    </title>


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
        }


        .current-image {
            width: 120px;
            height: 120px;

            object-fit: cover;

            border-radius: 10px;

            border: 1px solid #ddd;

            margin-bottom: 20px;
        }


        .no-image {
            width: 120px;
            height: 120px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eee;

            border-radius: 10px;

            color: #777;

            margin-bottom: 20px;
        }


        .note {
            color: #777;
            font-size: 13px;
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
        href="products.php"
    >

        ← Products

    </a>


</header>



<div class="container">


    <div class="form-box">


        <h1>
            Edit Product
        </h1>


        <p class="subtitle">
            Update your North Vault product.
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


            <label>
                Product Name
            </label>

            <input
                type="text"
                name="name"
                value="<?php echo htmlspecialchars($product["name"]); ?>"
                required
            >



            <label>
                Description
            </label>

            <textarea
                name="description"
            ><?php
                echo htmlspecialchars(
                    $product["description"]
                );
            ?></textarea>



            <label>
                Price (₦)
            </label>

            <input
                type="number"
                name="price"
                value="<?php echo htmlspecialchars($product["price"]); ?>"
                min="0"
                step="0.01"
                required
            >



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

                <?php

                $categories = [
                    "Fashion",
                    "Electronics",
                    "Beauty",
                    "Home",
                    "Shoes",
                    "Accessories",
                    "Other"
                ];

                foreach ($categories as $category):

                ?>

                    <option
                        value="<?php echo $category; ?>"
                        <?php
                        if ($product["category"] === $category) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php echo $category; ?>

                    </option>

                <?php endforeach; ?>

            </select>



            <label>
                Stock
            </label>

            <input
                type="number"
                name="stock"
                value="<?php echo htmlspecialchars($product["stock"]); ?>"
                min="0"
                required
            >



            <label>
                Current Picture
            </label>


            <?php if (!empty($product["image"])): ?>

                <img
                    class="current-image"
                    src="../images/products/<?php echo htmlspecialchars($product["image"]); ?>"
                    alt="Current product picture"
                >

            <?php else: ?>

                <div class="no-image">
                    No Image
                </div>

            <?php endif; ?>



            <label>
                Replace Picture
            </label>

            <input
                type="file"
                name="image"
                accept=".jpg,.jpeg,.png,.webp"
            >

            <p class="note">
                Leave this empty if you want to keep the current picture.
                Maximum 5MB.
            </p>



            <button type="submit">
                Save Changes
            </button>


        </form>


    </div>


</div>


</body>

</html>

<?php

header("Content-Type: application/json");

require_once "../config/database.php";

$sql = "
    SELECT
        id,
        name,
        description,
        price,
        image,
        category,
        stock
    FROM products
    ORDER BY id DESC
";

$result = $conn->query($sql);

$products = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $products[] = [
            "id" => (int) $row["id"],
            "name" => $row["name"],
            "description" => $row["description"],
            "price" => (float) $row["price"],
            "image" => $row["image"],
            "category" => $row["category"],
            "stock" => (int) $row["stock"]
        ];

    }

}

echo json_encode($products);

?>
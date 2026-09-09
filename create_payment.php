<?php

require_once "config.php";

/*
|--------------------------------------------------------------------------
| START SESSION
|--------------------------------------------------------------------------
*/

session_start();


/*
|--------------------------------------------------------------------------
| ONLY ALLOW POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}


/*
|--------------------------------------------------------------------------
| GET CUSTOMER INFORMATION
|--------------------------------------------------------------------------
*/

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$city = trim($_POST["city"] ?? "");
$amount = floatval($_POST["amount"] ?? 0);


/*
|--------------------------------------------------------------------------
| VALIDATE CUSTOMER INFORMATION
|--------------------------------------------------------------------------
*/

if (
    $name === "" ||
    $email === "" ||
    $phone === ""
) {
    die("Please fill in all customer details.");
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address.");
}


if ($amount <= 0) {
    die("Invalid payment amount.");
}


/*
|--------------------------------------------------------------------------
| CREATE UNIQUE TRANSACTION REFERENCE
|--------------------------------------------------------------------------
*/

$tx_ref =
    "NORTHVAULT-" .
    time() .
    "-" .
    rand(1000, 9999);


/*
|--------------------------------------------------------------------------
| FLUTTERWAVE REDIRECT URL
|--------------------------------------------------------------------------
|
| Your XAMPP folder is:
| C:\xampp\htdocs\North Vault
|
| %20 represents the space between North and Vault.
|
*/

$redirect_url =
    "http://localhost/North%20Vault/payment_callback.php";


/*
|--------------------------------------------------------------------------
| PAYMENT DATA
|--------------------------------------------------------------------------
*/

$data = [

    "tx_ref" => $tx_ref,

    "amount" => $amount,

    "currency" => "NGN",

    "redirect_url" => $redirect_url,

    "customer" => [

        "email" => $email,

        "name" => $name,

        "phonenumber" => $phone

    ],

    "meta" => [

        "customer_name" => $name,

        "customer_email" => $email,

        "customer_phone" => $phone,

        "delivery_address" => $address,

        "delivery_city" => $city,

        "order_amount" => $amount

    ],

    "customizations" => [

        "title" => "North Vault",

        "description" =>
            "Payment for North Vault order"

    ]

];


/*
|--------------------------------------------------------------------------
| SEND REQUEST TO FLUTTERWAVE
|--------------------------------------------------------------------------
*/

$ch = curl_init(
    "https://api.flutterwave.com/v3/payments"
);


curl_setopt(
    $ch,
    CURLOPT_RETURNTRANSFER,
    true
);


curl_setopt(
    $ch,
    CURLOPT_POST,
    true
);


curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    [

        "Authorization: Bearer " .
        $flutterwave_secret_key,

        "Content-Type: application/json"

    ]
);


curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode($data)
);


/*
|--------------------------------------------------------------------------
| GET FLUTTERWAVE RESPONSE
|--------------------------------------------------------------------------
*/

$response = curl_exec($ch);


if ($response === false) {

    die(
        "Connection error: " .
        curl_error($ch)
    );

}


$http_code = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


curl_close($ch);


/*
|--------------------------------------------------------------------------
| CONVERT RESPONSE TO ARRAY
|--------------------------------------------------------------------------
*/

$result = json_decode(
    $response,
    true
);


/*
|--------------------------------------------------------------------------
| CHECK PAYMENT CREATION
|--------------------------------------------------------------------------
*/

if (
    $http_code >= 200 &&
    $http_code < 300 &&
    isset($result["status"]) &&
    $result["status"] === "success" &&
    isset($result["data"]["link"])
) {

    /*
    |--------------------------------------------------------------------------
    | SAVE PENDING ORDER IN SESSION
    |--------------------------------------------------------------------------
    */

    $_SESSION["northVaultPendingOrder"] = [

        "tx_ref" => $tx_ref,

        "name" => $name,

        "email" => $email,

        "phone" => $phone,

        "address" => $address,

        "city" => $city,

        "amount" => $amount,

        "created_at" =>
            date("Y-m-d H:i:s")

    ];


    /*
    |--------------------------------------------------------------------------
    | SEND CUSTOMER TO FLUTTERWAVE CHECKOUT
    |--------------------------------------------------------------------------
    */

    header(
        "Location: " .
        $result["data"]["link"]
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| PAYMENT COULD NOT BE CREATED
|--------------------------------------------------------------------------
*/

echo "<h2>Payment could not be created.</h2>";

echo "<p>Flutterwave response:</p>";

echo "<pre>";

print_r($result);

echo "</pre>";

?>
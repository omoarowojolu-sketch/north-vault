<?php

require_once "config.php";

/*
|--------------------------------------------------------------------------
| GET TRANSACTION ID
|--------------------------------------------------------------------------
*/

$transaction_id = $_GET['transaction_id'] ?? '';

if (empty($transaction_id)) {

    die("Transaction ID is missing.");

}


/*
|--------------------------------------------------------------------------
| FLUTTERWAVE API URL
|--------------------------------------------------------------------------
*/

$url = "https://api.flutterwave.com/v3/transactions/"
     . urlencode($transaction_id)
     . "/verify";


/*
|--------------------------------------------------------------------------
| SEND REQUEST TO FLUTTERWAVE
|--------------------------------------------------------------------------
*/

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [

    "Authorization: Bearer " . $flutterwave_secret_key,

    "Content-Type: application/json"

]);


$response = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);


/*
|--------------------------------------------------------------------------
| CHECK RESPONSE
|--------------------------------------------------------------------------
*/

if ($response === false) {

    die("Unable to connect to Flutterwave.");

}


$data = json_decode($response, true);


/*
|--------------------------------------------------------------------------
| CHECK PAYMENT STATUS
|--------------------------------------------------------------------------
*/

if (
    $http_code === 200 &&
    isset($data['status']) &&
    $data['status'] === 'success' &&
    isset($data['data']['status']) &&
    $data['data']['status'] === 'successful'
) {

    /*
    |--------------------------------------------------------------------------
    | PAYMENT SUCCESSFUL
    |--------------------------------------------------------------------------
    */

    $amount = $data['data']['amount'];

    $currency = $data['data']['currency'];

    $tx_ref = $data['data']['tx_ref'];


    echo "<!DOCTYPE html>";

    echo "<html>";

    echo "<head>";

    echo "<meta charset='UTF-8'>";

    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";

    echo "<title>North Vault | Payment Successful</title>";

    echo "</head>";

    echo "<body style='font-family: Arial; text-align:center; padding:60px;'>";

    echo "<h1>Payment Successful!</h1>";

    echo "<p>Thank you for shopping with North Vault.</p>";

    echo "<p><strong>Transaction Reference:</strong> "
        . htmlspecialchars($tx_ref)
        . "</p>";

    echo "<p><strong>Amount Paid:</strong> ₦"
        . number_format($amount, 2)
        . "</p>";

    echo "<br>";

    echo "<a href='index.html'>Continue Shopping</a>";

    echo "</body>";

    echo "</html>";

} else {

    /*
    |--------------------------------------------------------------------------
    | PAYMENT FAILED
    |--------------------------------------------------------------------------
    */

    echo "<!DOCTYPE html>";

    echo "<html>";

    echo "<head>";

    echo "<meta charset='UTF-8'>";

    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";

    echo "<title>North Vault | Payment Failed</title>";

    echo "</head>";

    echo "<body style='font-family: Arial; text-align:center; padding:60px;'>";

    echo "<h1>Payment Not Successful</h1>";

    echo "<p>We could not confirm your payment.</p>";

    echo "<p>Please try again.</p>";

    echo "<br>";

    echo "<a href='checkout.html'>Return to Checkout</a>";

    echo "</body>";

    echo "</html>";

}
?>
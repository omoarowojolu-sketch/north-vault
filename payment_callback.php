<?php

require_once "config.php";

$transaction_id = $_GET["transaction_id"] ?? "";

if (!$transaction_id) {
    exit("Invalid payment request.");
}


/* VERIFY FLUTTERWAVE PAYMENT */

$verify_url =
    "https://api.flutterwave.com/v3/transactions/" .
    urlencode($transaction_id) .
    "/verify";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $verify_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $flutterwave_secret_key,
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($curl);

curl_close($curl);

$data = json_decode($response, true);


/* CHECK PAYMENT */

$payment_ok =
    isset($data["status"]) &&
    $data["status"] === "success" &&
    isset($data["data"]["status"]) &&
    $data["data"]["status"] === "successful";


if (!$payment_ok) {

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>North Vault - Payment</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f8f8f8;

    color: #222;

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

}

.box {

    width: 100%;

    max-width: 420px;

    background: white;

    padding: 40px 25px;

    border-radius: 16px;

    text-align: center;

    box-shadow:
        0 8px 35px
        rgba(0,0,0,.08);

}

.logo {

    font-size: 22px;

    font-weight: 900;

    letter-spacing: 1px;

    margin-bottom: 35px;

}

.logo span {

    color: #777;

}

.error-icon {

    width: 70px;

    height: 70px;

    margin: auto;

    margin-bottom: 20px;

    border-radius: 50%;

    background: #fff1e8;

    color: #ff6a00;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

    font-weight: bold;

}

h1 {

    font-size: 24px;

    margin-bottom: 12px;

}

p {

    color: #777;

    line-height: 1.6;

    margin-bottom: 25px;

}

.button {

    display: block;

    width: 100%;

    padding: 15px;

    background: #ff6a00;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

}

</style>

</head>

<body>

<div class="box">

    <div class="logo">

        NORTH <span>VAULT</span>

    </div>

    <div class="error-icon">

        !

    </div>

    <h1>

        Payment unsuccessful

    </h1>

    <p>

        We couldn't confirm your payment.

        If money was deducted from your account,

        please contact North Vault.

    </p>

    <a
        href="checkout.html"
        class="button"
    >

        Return to checkout

    </a>

</div>

</body>

</html>

<?php

exit;

}


/* GET VERIFIED PAYMENT DATA */

$payment = $data["data"];


/* CUSTOMER */

$customer =
    $payment["customer"]["name"]
    ?? "Customer";


/* EMAIL */

$email =
    $payment["customer"]["email"]
    ?? "";


/* ACTUAL CHARGED AMOUNT */

$amount =
    isset($payment["charged_amount"])
    ? (float)$payment["charged_amount"]
    : (float)$payment["amount"];


/* CURRENCY */

$currency =
    $payment["currency"]
    ?? "NGN";


/* TRANSACTION REFERENCE */

$reference =
    $payment["tx_ref"]
    ?? "";


/* ORDER NUMBER */

$order =
    "NV-" .
    strtoupper(
        substr(
            md5($transaction_id),
            0,
            8
        )
    );

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

North Vault - Order Confirmed

</title>


<style>

/* RESET */

* {

    box-sizing: border-box;

    margin: 0;

    padding: 0;

}


/* BODY */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f6f6f6;

    color: #222;

}


/* HEADER */

.header {

    height: 62px;

    background: #ffffff;

    border-bottom: 1px solid #eeeeee;

    display: flex;

    align-items: center;

    padding: 0 6%;

}


/* LOGO */

.logo {

    font-size: 22px;

    font-weight: 900;

    letter-spacing: 1px;

    color: #111;

}


.logo span {

    color: #777777;

}


/* MAIN */

.main {

    width: 100%;

    max-width: 760px;

    margin: auto;

    padding: 45px 18px 60px;

}


/* SUCCESS */

.success {

    text-align: center;

    background: white;

    border-radius: 14px;

    padding: 35px 20px;

    box-shadow:
        0 4px 25px
        rgba(0,0,0,.05);

}


.tick {

    width: 76px;

    height: 76px;

    border-radius: 50%;

    background: #fff1e8;

    color: #ff6a00;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: auto;

    margin-bottom: 20px;

    font-size: 38px;

    font-weight: bold;

}


.success h1 {

    font-size: 28px;

    margin-bottom: 10px;

}


.success p {

    color: #777;

    font-size: 15px;

    line-height: 1.5;

}


.name {

    color: #222;

    font-weight: bold;

}


/* ORDER CARD */

.card {

    margin-top: 18px;

    background: white;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 4px 25px
        rgba(0,0,0,.05);

}


/* CARD TITLE */

.card-title {

    padding: 18px 20px;

    border-bottom: 1px solid #eeeeee;

    font-size: 16px;

    font-weight: bold;

    display: flex;

    justify-content: space-between;

}


.order-id {

    color: #888;

    font-size: 13px;

    font-weight: normal;

}


/* DETAILS */

.details {

    padding: 10px 20px;

}


.detail {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 17px 0;

    border-bottom: 1px solid #f1f1f1;

}


.detail:last-child {

    border-bottom: none;

}


.label {

    color: #888;

    font-size: 14px;

}


.value {

    font-size: 14px;

    font-weight: bold;

    text-align: right;

    max-width: 60%;

    word-break: break-word;

}


/* PAYMENT STATUS */

.paid {

    color: #16834a;

}


/* TOTAL */

.total {

    background: #fafafa;

    padding: 22px 20px;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.total-label {

    font-size: 16px;

    font-weight: bold;

}


.total-price {

    color: #ff6a00;

    font-size: 25px;

    font-weight: 900;

}


/* NOTE */

.note {

    text-align: center;

    margin-top: 18px;

    color: #777;

    font-size: 13px;

    line-height: 1.6;

}


/* BUTTONS */

.buttons {

    display: flex;

    gap: 12px;

    margin-top: 22px;

}


.button {

    flex: 1;

    text-align: center;

    text-decoration: none;

    padding: 15px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: bold;

}


.primary {

    background: #ff6a00;

    color: white;

}


.secondary {

    background: white;

    color: #222;

    border: 1px solid #ddd;

}


.primary:hover {

    background: #e85d00;

}


/* FOOTER */

.footer {

    text-align: center;

    margin-top: 30px;

    color: #999;

    font-size: 12px;

}


/* MOBILE */

@media (max-width: 600px) {

    .main {

        padding: 25px 12px 40px;

    }


    .success {

        padding: 30px 16px;

    }


    .success h1 {

        font-size: 24px;

    }


    .card-title {

        font-size: 15px;

    }


    .detail {

        align-items: flex-start;

    }


    .value {

        max-width: 55%;

    }


    .total-price {

        font-size: 21px;

    }


    .buttons {

        flex-direction: column;

    }

}

</style>

</head>


<body>


<!-- HEADER -->

<header class="header">

    <div class="logo">

        NORTH <span>VAULT</span>

    </div>

</header>


<!-- MAIN -->

<main class="main">


    <!-- SUCCESS MESSAGE -->

    <section class="success">


        <div class="tick">

            ✓

        </div>


        <h1>

            Payment successful!

        </h1>


        <p>

            Thank you for your order,

            <span class="name">

                <?= htmlspecialchars($customer) ?>

            </span>

        </p>


    </section>



    <!-- ORDER -->

    <section class="card">


        <div class="card-title">

            <span>

                Order details

            </span>


            <span class="order-id">

                <?= htmlspecialchars($order) ?>

            </span>

        </div>


        <div class="details">


            <!-- CUSTOMER -->

            <div class="detail">

                <span class="label">

                    Customer

                </span>


                <span class="value">

                    <?= htmlspecialchars($customer) ?>

                </span>

            </div>


            <!-- EMAIL -->

            <?php if ($email): ?>

            <div class="detail">

                <span class="label">

                    Email

                </span>


                <span class="value">

                    <?= htmlspecialchars($email) ?>

                </span>

            </div>

            <?php endif; ?>


            <!-- PAYMENT -->

            <div class="detail">

                <span class="label">

                    Payment status

                </span>


                <span class="value paid">

                    ✓ Paid

                </span>

            </div>


            <!-- REFERENCE -->

            <div class="detail">

                <span class="label">

                    Payment reference

                </span>


                <span class="value">

                    <?= htmlspecialchars($reference) ?>

                </span>

            </div>


        </div>


        <!-- TOTAL -->

        <div class="total">

            <span class="total-label">

                Total paid

            </span>


            <span class="total-price">

                <?= htmlspecialchars($currency) ?>

                <?= number_format($amount, 2) ?>

            </span>

        </div>


    </section>



    <!-- MESSAGE -->

    <div class="note">

        Your payment has been confirmed and

        your order is now being processed.

    </div>



    <!-- BUTTONS -->

    <div class="buttons">


        <a
            href="index.html"
            class="button primary"
            onclick="clearCart()"
        >

            Continue Shopping

        </a>


        <a
            href="index.html"
            class="button secondary"
            onclick="clearCart()"
        >

            Back to Home

        </a>


    </div>



    <div class="footer">

        North Vault · Secure Shopping

    </div>


</main>


<script>

function clearCart() {

    localStorage.removeItem(
        "northVaultCart"
    );

}

</script>


</body>

</html>
```

<?php
include 'db_connect.php';

$orderID = ''; // order ID from the DB to be paid/ has recurring payment set with card token
try {
    $stmt = $pdo->prepare('SELECT token, mask FROM subscriptions WHERE order_id = ?');
    $stmt->execute([$orderID]);
    $subscriptionData = $stmt->fetch();

    // Debugging: Check the subscription data
    if ($subscriptionData) {
        echo "Subscription data found: " . json_encode($subscriptionData) . "\n";

        $token = $subscriptionData['token'];
        $hashKey = 'demo'; // update to your API Key
        $vid = 'demo'; // update to your vid

        $fields = [
            'vid' => $vid,
            'amount' => '', // enter amount
            'callback' => 'your-server-url/recurring_payment_callback.php', // update to you callback url
            'currency' => 'KES',
            'email' => '', // enter email
            'phone' => '', // enter phone
            'orderid' => $orderID,
            'firstname' => '', // enter customer first name, can be picked from the DB
            'lastname' => '',  // enter customer last name, can be picked from the DB
            'country' => 'KE',
            'city' => 'Nairobi',
            'token' => $token
        ];

        ksort($fields);
        $dataString = http_build_query($fields);
        $hash = hash_hmac('sha256', $dataString, $hashKey);
        $fields['hash'] = $hash;

        $ch = curl_init('https://apis.ipayafrica.com/payments/v2/transact/card/recurring/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            $responseData = json_decode($response, true);
            if (isset($responseData['status']) && $responseData['status'] === 200) {
                $lastPaymentDate = date('Y-m-d H:i:s');
                $updateStmt = $pdo->prepare('UPDATE subscriptions SET last_payment_date = ?, payment_status = ? WHERE order_id = ?');
                $updateStmt->execute([$lastPaymentDate, 'successful', $orderID]);
                echo 'Payment successful. Updated last payment date.';
            } else {
                echo 'Payment failed. Response: ' . json_encode($responseData);
            }
        }

        curl_close($ch);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Subscription data not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

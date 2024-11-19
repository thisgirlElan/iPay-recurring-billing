<?php
// Retrieve subscription data
$orderID = 'your_order_id'; // Replace with dynamic order ID if needed

$dsn = 'mysql:host=localhost;dbname=subscriptions;charset=utf8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Fetch token and mask based on order ID
    $stmt = $pdo->prepare('SELECT token, mask FROM subscriptions WHERE order_id = ?');
    $stmt->execute([$orderID]);
    $subscriptionData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subscriptionData) {
        $token = $subscriptionData['token'];
        $mask = $subscriptionData['mask'];

        // Now you can use the token and mask in a recurring billing request
        echo json_encode(['status' => 'success', 'token' => $token, 'mask' => $mask]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Subscription data not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

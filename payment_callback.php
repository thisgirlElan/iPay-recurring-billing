<?php
// Database configuration
$host = 'localhost';
$dbname = ''; // Replace with your database name
$username = 'root'; // Default XAMPP MySQL username
$password = ''; // Default password is empty in XAMPP

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Capture callback data
$queryParams = $_GET;

// Prepare an SQL insert statement
$sql = "INSERT INTO subscriptions (order_id, username, phone, token, mask) VALUES (:order_id, :username, :phone, :token, :mask)";

// Prepare the statement
$stmt = $pdo->prepare($sql);

// Bind parameters from the callback data
$order_id = $queryParams['id'];
$username = $queryParams['msisdn_id'];
$phone = $queryParams['msisdn_idnum'];
$token = $queryParams['token'];
$mask = $queryParams['mask'];

$stmt->bindParam(':order_id', $order_id);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':token', $token);
$stmt->bindParam(':mask', $mask);

// Execute the insert statement
if ($stmt->execute()) {
    echo json_encode(["message" => "Data saved successfully", "callback_data" => $queryParams]);
    // echo json_encode(["callback_data" => $queryParams]); 
} else {
    echo json_encode(["message" => "Failed to save data"]);
}
?>
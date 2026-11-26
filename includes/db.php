<?php
// Connects to MongoDB and starts the session. Included by every page.
session_start();
require __DIR__ . '/../vendor/autoload.php';

// Uses MONGODB_URI when deployed (MongoDB Atlas), otherwise local MongoDB
$uri = getenv('MONGODB_URI') ?: "mongodb://127.0.0.1:27017";
$client = new MongoDB\Client($uri);
$db = $client->selectDatabase('pantrypal');   // created automatically

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

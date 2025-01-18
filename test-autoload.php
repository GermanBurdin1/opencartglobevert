<?php
require_once __DIR__ . '/vendor/autoload.php';

use Stripe\Stripe;

try {
    Stripe::setApiKey('pk_test_51Qb3cWGaUr31i20XRiurDRW2WZzxuaFCQWTHQGzPbFqUOzha4GBz3jIHTLHChC9o7E3aflhABxcRLWYSswDLzQrq00QqZAFkCO');
    echo "Stripe is working correctly.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

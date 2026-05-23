<?php
/**
 * BETELITE - REST API Payments Hooks (Flutterwave / Paystack)
 */
header("Content-Type: application/json");

echo json_encode(["status" => "success", "message" => "Webhooks trigger listener loaded."]);

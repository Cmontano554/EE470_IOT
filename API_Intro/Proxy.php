<?php
// The Apps Script endpoint URL (yours)
$google_url = "https://script.google.com/macros/s/AKfycbxtIO4qGuns5_xn7JGd-KfCNRn_j7Sfq_RX-w6igTG3NZph2fstqR7eZ_l8pg3yuVgiLw/exec";

// Append query string from ESP (status=ON, rgb=120, etc.)
if (!empty($_SERVER["QUERY_STRING"])) {
    $google_url .= "?" . $_SERVER["QUERY_STRING"];
}

// Use cURL instead of file_get_contents for better reliability
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $google_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // fine for demo use

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

http_response_code($httpCode);
echo $response;
?>

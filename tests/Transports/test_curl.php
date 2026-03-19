<?php

$c = curl_init();
curl_setopt($c, CURLOPT_URL, "https://api.telegram.org/bot8238296185:AAHIuPPB6GgHcRjWJhkRmh6aX5SCmeLyeTM/getMe");
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_TIMEOUT, 5);
$r = curl_exec($c);
echo curl_error($c) ?: "OK: " . substr($r, 0, 100);
curl_close($c);

<?php

$mh = curl_multi_init();

$c = curl_init();
curl_setopt($c, CURLOPT_URL, "https://api.telegram.org/bot8238296185:AAHIuPPB6GgHcRjWJhkRmh6aX5SCmeLyeTM/getMe");
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_TIMEOUT, 10);
curl_multi_add_handle($mh, $c);

echo "Added handle\n";

$completed = false;
$iterations = 0;
while (!$completed && $iterations < 50) {
    $iterations++;
    $active = 0;
    $status = curl_multi_exec($mh, $active);
    echo "Iter $iterations: status=$status active=$active\n";

    if ($active > 0) {
        $ret = curl_multi_select($mh, 1.0);
        echo "  select=$ret\n";
    }

    $info = curl_multi_info_read($mh);
    if ($info !== false) {
        echo "Completed! response: " . substr(curl_multi_getcontent($info['handle']), 0, 80) . "\n";
        $completed = true;
    }

    if ($status === CURLM_OK && $active === 0) {
        echo "No more activity\n";
        break;
    }
}

if (!$completed) {
    echo "Timed out after $iterations iterations\n";
}

curl_multi_remove_handle($mh, $c);
curl_close($c);
curl_multi_close($mh);

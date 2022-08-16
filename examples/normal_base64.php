<?php

set_time_limit(130);

require(__DIR__ . '/../src/autoloader.php');

$image = __DIR__ . '/images/normal.jpg';
$base64 = base64_encode(file_get_contents($image));

/** @var \TwoCaptcha\Classes\Resolvers\Normal $solver */
$solver = app(\TwoCaptcha\Classes\Resolvers\Normal::class);

try {
    $result = $solver->imageBase64(['base64' => $base64])->resolve();
} catch (\Exception $e) {
    die($e->getMessage());
}

die('Captcha solved: ' . $result->code);

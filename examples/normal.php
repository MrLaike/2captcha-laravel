<?php

set_time_limit(130);

require(__DIR__ . '/../src/autoloader.php');

//$solver = new \TwoCaptcha\Classes\TwoCaptcha('YOUR_API_KEY');
/** @var \TwoCaptcha\Classes\Resolvers\Normal $solver */
$solver = app(\TwoCaptcha\Classes\Resolvers\Normal::class);

try {
    $result = $solver->imagesPath(__DIR__ . '/images/normal.jpg')
        ->resolve()
    ;
} catch (\Exception $e) {
    die($e->getMessage());
}

die('Captcha solved: ' . $result->code);

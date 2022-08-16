<?php

set_time_limit(130);

require(__DIR__ . '/../src/autoloader.php');

$solver = app(\TwoCaptcha\Classes\Resolvers\Text::class);

try {
    $result = $solver->text('If tomorrow is Saturday, what day is today?')
        ->resolve()
    ;
} catch (\Exception $e) {
    die($e->getMessage());
}

die('Captcha solved: ' . $result->code);

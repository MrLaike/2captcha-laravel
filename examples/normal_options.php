<?php

set_time_limit(130);

require(__DIR__ . '/../src/autoloader.php');

/** @var \TwoCaptcha\Classes\Resolvers\Normal $solver */
$solver = app(\TwoCaptcha\Classes\Resolvers\Normal::class);

try {
    $result = $solver->imagesPath(__DIR__ . '/images/normal_2.jpg')
        ->numeric(4)
        ->minLen(4)
        ->maxLen(20)
        ->phrase(1)
        ->caseSensitive()
        ->resolve()
        ;
    $result = $solver->normal([
        'file'          => __DIR__ . '/images/normal_2.jpg',
        'numeric'       => 4,
        'minLen'        => 4,
        'maxLen'        => 20,
        'phrase'        => 1,
        'caseSensitive' => 1,
        'calc'          => 0,
        'lang'          => 'en',
     // 'hintImg'       => __DIR__ . '/images/normal_hint.jpg',
     // 'hintText'      => 'Type red symbols only',
    ]);
} catch (\Exception $e) {
    die($e->getMessage());
}

die('Captcha solved: ' . $result->code);

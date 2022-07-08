<?php

namespace TwoCaptcha;

use Illuminate\Support\ServiceProvider;
use TwoCaptcha\Classes\ApiHttpClient;
use TwoCaptcha\Classes\Contracts\ApiClient as ApiClientInterface;
use TwoCaptcha\Classes\TwoCaptcha;

class TwoCaptchaProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('2captcha', TwoCaptcha::class);
    }
    public function boot()
    {
        $serverUrl = config('captcha.server');
        $this->app->bind(ApiClientInterface::class, new ApiHttpClient($serverUrl));
    }

}
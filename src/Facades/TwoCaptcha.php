<?php

namespace TwoCaptcha\Facades;

use Illuminate\Support\Facades\Facade;

class TwoCaptcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return '2captcha';
    }
}
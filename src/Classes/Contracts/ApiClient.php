<?php

namespace TwoCaptcha\Classes\Contracts;

interface ApiClient
{
    /** Sends captcha to /in.php */
    public function in(array $captcha, array $files = []);

    /** Does request to /res.php */
    public function res(array|string|null $query);
}
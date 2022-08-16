<?php

declare(strict_types=1);


namespace TwoCaptcha\Classes\Resolvers;

use TwoCaptcha\Classes\TwoCaptcha;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

class FunCaptcha extends TwoCaptcha
{
    /**
     * Wrapper for solving FunCaptcha
     *
     * @return \stdClass
     * @throws ApiException
     * @throws NetworkException
     * @throws TimeoutException
     * @throws ValidationException
     */
    public function resolve()
    {
        $this->options['method'] = 'funcaptcha';

        return $this->solve($this->options);
    }
}
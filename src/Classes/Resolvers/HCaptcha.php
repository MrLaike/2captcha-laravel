<?php

declare(strict_types=1);


namespace TwoCaptcha\Classes\Resolvers;

use TwoCaptcha\Classes\TwoCaptcha;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

class HCaptcha extends TwoCaptcha
{
    /**
     * Wrapper for solving hCaptcha
     *
     * @return \stdClass
     * @throws ApiException
     * @throws NetworkException
     * @throws TimeoutException
     * @throws ValidationException
     */
    public function resolve()
    {
        $this->options['method'] = 'hcaptcha';

        return $this->solve($this->options);
    }

}
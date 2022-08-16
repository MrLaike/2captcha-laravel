<?php

declare(strict_types=1);

namespace TwoCaptcha\Classes\Resolvers;

use TwoCaptcha\Classes\TwoCaptcha;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

class Text extends TwoCaptcha
{
    /**
     * Wrapper for solving text captcha
     *
     * @return \stdClass
     * @throws ApiException
     * @throws NetworkException
     * @throws TimeoutException
     * @throws ValidationException
     */
    public function resolve()
    {
        $this->options['method'] = 'post';

        return $this->solve($this->options);
    }

    public function text(string $value): self
    {
        $this->options['textcaptcha'] = $value;
        return $this;
    }

}
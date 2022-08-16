<?php

declare(strict_types=1);

namespace TwoCaptcha\Classes\Resolvers;

use TwoCaptcha\Classes\TwoCaptcha;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

class Coordinates extends TwoCaptcha
{

    /**
     * Wrapper for solving coordinates captcha
     *
     * @return \stdClass
     * @throws ApiException
     * @throws NetworkException
     * @throws TimeoutException
     * @throws ValidationException
     */
    public function resolve()
    {
        $this->requireFileOrBase64($this->options);

        if (!isset($this->options['method'])) {
            $this->methodPost();
        }
        $this->options['coordinatescaptcha'] = 1;

        return $this->solve($this->options);
    }

    public function method(string $value): self
    {
        $this->options['method'] = $value;
        return $this;
    }

    public function methodPost()
    {
        $this->method('post');
    }

    public function methodBase64()
    {
        $this->method('base64');
    }


}
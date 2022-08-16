<?php

declare(strict_types=1);

namespace TwoCaptcha\Classes\Resolvers;

use TwoCaptcha\Classes\TwoCaptcha;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

class Canvas extends TwoCaptcha
{

    /**
     * Wrapper for solving canvas captcha
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

        $this->recaptchaOnCanvas();
        
        if ( empty($this->options['hintText']) && empty($this->options['hintImg']) ) {
            throw new ValidationException('At least one of parameters: hintText or hintImg required!');
        }

        return $this->solve($this->options);
    }

    public function recaptcha(): self
    {
        $this->options['recaptcha'] = 1;
        return $this;
    }

    public function canvas(): self
    {
        $this->options['canvas'] = 1;
        return $this;
    }

    public function recaptchaOnCanvas(): self
    {
        $this->recaptcha()->canvas();
        return $this;
    }

    public function file($value): self
    {
        $this->options['file'] = $value;
        return $this;
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
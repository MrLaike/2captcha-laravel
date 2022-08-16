<?php

declare(strict_types=1);


namespace TwoCaptcha\Classes\Resolvers;

use TwoCaptcha\Classes\TwoCaptcha;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

class ReCaptcha extends TwoCaptcha
{
    private int $timeout = 600;


    /**
     * Wrapper for solving ReCaptcha
     *
     * @param $captcha
     * @return \stdClass
     * @throws ApiException
     * @throws NetworkException
     * @throws TimeoutException
     * @throws ValidationException
     */
    public function resolve($captcha)
    {
        $captcha['method'] = 'userrecaptcha';

        return $this->solve($captcha, ['timeout' => $this->timeout]);
    }

    public function url(string $value): self
    {
        $this->options['url'] = $value;
        return $this;
    }

    public function sitekey(string $value): self
    {
        $this->options['sitekey'] = $value;
        return $this;
    }

    public function version(string $value): self
    {
        $this->options['version'] = $value;
        return $this;
    }

    public function v3(): self
    {
        return $this->version('v3');
    }

    public function action(string $value): self
    {
        $this->options['action'] = $value;
        return $this;
    }

    public function invisible(): self
    {
        $this->options['invisible'] = 1;
        return $this;
    }

    /**
     * ['type' => 'HTTPS', 'uri'  => 'login:password@IP_address:PORT']
     * @param  array $options
     * @return $this
     */
    public function proxy(array $options): self
    {
        $this->options['proxy'] = $options;
        return $this;
    }

}
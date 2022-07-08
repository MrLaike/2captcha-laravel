<?php

declare(strict_types=1);

namespace TwoCaptcha\Classes\Resolvers;

use TwoCaptcha\Classes\TwoCaptcha;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

class Normal extends TwoCaptcha
{
    private string|array $imagesPath;
    private array $options;
    private bool $caseSensitive;
    private bool $calculative;

    /**
     * Wrapper for solving normal captcha (image)
     *
     * @throws ApiException
     * @throws NetworkException
     * @throws TimeoutException
     * @throws ValidationException
     */
    public function resolve()
    {
        if ($this->imagesPath) {
            $captcha = [
                'file' => $this->imagesPath,
            ];
        }

        $this->requireFileOrBase64($captcha);

        $this->options['method'] = empty($captcha['base64']) ? 'post' : 'base64';

        return $this->solve($captcha);
    }

    public function apiKey(string $value): self
    {
        $this->options['apiKey'] = $value;
        return $this;
    }

    public function body(string $value): self
    {
        $this->options['body'] = $value;
        return $this;
    }

    /**
     * post - defines that you're sending an image with multipart form
     * base64 - defines that you're sending a base64 encoded image
     */
    public function method(string $value): self
    {
        $this->options['method'] = $value;
        return $this;
    }

    public function caseSensitive(): self
    {
        $this->caseSensitive = true;
        return $this;
    }

    /**
     * 0 - not specified
     * 1 - captcha contains only numbers
     * 2 - captcha contains only letters
     * 3 - captcha contains only numbers OR only letters
     * 4 - captcha MUST contain both numbers AND letters
     */
    public function numeric($value): self
    {
        $this->options['numeric'] = $value;
        return $this;
    }

    /**
     * tells the server to send the response as JSON
     * default server will send the response as plain text
     */
    public function json()
    {
        $this->options['json'] = 1;
    }
    /**
     * minimum number of symbols in captcha
     */
    public function minLen(int $value): self
    {
        $this->options['min_len'] = $value;
        return $this;
    }

    /**
     * maximal number of symbols in captcha
     */
    public function maxLen(int $value): self
    {
        $this->options['max_len'] = $value;
        return $this;
    }


    /**
     * captcha requires calculation (e.g. type the result 4 + 8 = )
     */
    public function calculative(): self
    {
        $this->options['calc'] = true;
        return $this;
    }

    /**
     * 0 - captcha contains one word
     * 1 - captcha contains two or more words
     */
    public function phrase($value = 0): self
    {
        $this->options['phrase'] = $value;
        return $this;
    }

    public function imagesPath(string|array $imagesPath): self
    {
        $this->imagesPath = $imagesPath;
        return $this;
    }

    public function imageBase64()
    {
        
    }
}
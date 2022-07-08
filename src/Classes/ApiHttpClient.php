<?php

namespace TwoCaptcha\Classes;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Prophecy\Promise\PromiseInterface;
use TwoCaptcha\Classes\Contracts\ApiClient as ApiClientInterface;

class ApiHttpClient implements ApiClientInterface
{
    /** API server (exp. http://2captcha.com) */
    private string $server;
    private PendingRequest $client;

    public function __construct(string $server)
    {
        $this->server = $server;
    }

    public function async(): self
    {
    $this->client->async();
        return $this;
    }

    public function in(array $captcha, array $files = []): PromiseInterface|Response
    {
        foreach ($files as $key => $file) {
            $captcha[$key] = $this->curlPrepareFile($file);
        }

        return $this->client->post(
            url: $this->server . '/in.php',
            data: $captcha,
        );
    }

    public function res(array|string|null $query): PromiseInterface|Response
    {
        return $this->client->get(
            url: $this->server . 'res.php',
            query: $query
        );
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }

    /**
     * Different php versions have different approaches of sending files via CURL
     */
    private function curlPrepareFile(string|array $file): \CURLFile|string
    {
        if (function_exists('curl_file_create')) { // php 5.5+
            return curl_file_create($file, mime_content_type($file), 'file');
        } else {
            return '@' . realpath($file);
        }
    }
}
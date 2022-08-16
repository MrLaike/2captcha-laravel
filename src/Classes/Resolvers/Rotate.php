<?php

declare(strict_types=1);

namespace TwoCaptcha\Classes\Resolvers;

use TwoCaptcha\Classes\TwoCaptcha;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

class Rotate extends TwoCaptcha
{

    /**
     * Wrapper for solving RotateCaptcha
     *
     * @return \stdClass
     * @throws ApiException
     * @throws NetworkException
     * @throws TimeoutException
     * @throws ValidationException
     */
    public function resolve()
    {
        if (isset($this->options['file'])) {
            $this->options['files'] = [$this->options['file']];
            unset($this->options['file']);
        }

        $this->prepareFilesList($this->options);

        $this->options['method'] = 'rotatecaptcha';

        return $this->solve($this->options);
    }

}
<?php

namespace TwoCaptcha\Classes;

use Exception;
use Illuminate\Support\Collection;
use TwoCaptcha\Classes\Contracts\ApiClient as ApiClientInterface;
use TwoCaptcha\Exception\ApiException;
use TwoCaptcha\Exception\NetworkException;
use TwoCaptcha\Exception\TimeoutException;
use TwoCaptcha\Exception\ValidationException;

use function mb_strpos;
use function mb_substr;

/**
 * Class TwoCaptcha
 * @package TwoCaptcha
 */
abstract class TwoCaptcha
{
    /**
     * API KEY
     */
    private string $apiKey;

    protected array $options;

    /**
     * ID of software developer. Developers who integrated their software
     * with our service get reward: 10% of spendings of their software users.
     */
    private int $softId;

    /**
     * URL to which the result will be sent
     */
    private string $callback;

    /**
     * How long should wait for captcha result (in seconds)
     */
    private int $defaultTimeout = 120;

    /**
     * How long should wait for recaptcha result (in seconds)
     */
    private int $recaptchaTimeout = 600;

    /**
     * How often do requests to `/res.php` should be made
     * in order to check if a result is ready (in seconds)
     */
    private int $pollingInterval = 10;

    /**
     * Helps to understand if there is need of waiting
     * for result or not (because callback was used)
     */
    private int $lastCaptchaHasCallback;

    public function __construct(
        private ApiClientInterface $apiClient
    )
    {
        $options['apiKey'] = config('captcha.apiKey');

        if (!empty($options['softId'])) $this->softId = $options['softId'];
        if (!empty($options['callback'])) $this->callback = $options['callback'];

    }

    public function pollingInterval(int $pollingInterval): self
    {
        $this->pollingInterval = $pollingInterval;
        return $this;
    }

    public function recaptchaTimeout(int $timeout): self
    {
        $this->recaptchaTimeout = $timeout;
        return $this;
    }

    public function defaultTimeout(int $timeout): self
    {
        $this->defaultTimeout = $timeout;
        return $this;
    }

    public function apiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function apiClient(ApiClientInterface $apiClient): self
    {
        $this->apiClient = $apiClient;
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
     * Language code. See here https://2captcha.com/2captcha-api#language
     */
    public function lang(string $value): self
    {
        $this->options['lang'] = $value;
        return $this;
    }

    public abstract function resolve();












    /**
     * Sends captcha to `/in.php` and waits for it's result.
     * This helper can be used insted of manual using of `send` and `getResult` functions.
     *
     * @return \stdClass
     * @throws ApiException
     * @throws NetworkException
     * @throws TimeoutException
     * @throws ValidationException
     */
    public function solve(array $captcha, array $waitOptions = [])
    {
        $result = new \stdClass();

        $result->captchaId = $this->send($captcha);

        if ($this->lastCaptchaHasCallback) return $result;

        $result->code = $this->waitForResult($result->captchaId, $waitOptions);

        return $result;
    }

    /**
     * This helper waits for captcha result, and when result is ready, returns it
     *
     * @param $id
     * @param array $waitOptions
     * @return string|null
     * @throws TimeoutException
     */
    public function waitForResult($id, $waitOptions = [])
    {
        $startedAt = time();

        $timeout = empty($waitOptions['timeout']) ? $this->defaultTimeout : $waitOptions['timeout'];
        $pollingInterval = empty($waitOptions['pollingInterval']) ? $this->pollingInterval : $waitOptions['pollingInterval'];

        while (true) {
            if (time() - $startedAt < $timeout) {
                sleep($pollingInterval);
            } else {
                break;
            }

            try {
                $code = $this->getResult($id);
                if ($code) return $code;
            } catch (NetworkException $e) {
                // ignore network errors
            } catch (Exception $e) {
                throw $e;
            }
        }

        throw new TimeoutException('Timeout ' . $timeout . ' seconds reached');
    }

    /**
     * Sends captcha to '/in.php', and returns its `id`
     *
     * @return string
     * @throws ApiException
     * @throws NetworkException
     * @throws ValidationException
     */
    public function send(array $captcha)
    {
        $this->sendAttachDefaultParams($captcha);

        $files = $this->extractFiles($captcha);

        $this->mapParams($captcha, $captcha['method']);
        $this->mapParams($files, $captcha['method']);

        $response = $this->apiClient->in($captcha, $files);

        if (mb_strpos($response, 'OK|') !== 0) {
            throw new ApiException('Cannot recognise api response (' . $response . ')');
        }

        return mb_substr($response, 3);
    }

    /**
     * Returns result of captcha if it was solved or `null`, if result is not ready
     *
     * @param $id
     * @return string|null
     * @throws ApiException
     * @throws NetworkException
     */
    public function getResult(string|int $id)
    {
        $response = $this->res([
            'action' => 'get',
            'id'     => $id,
        ]);

        if ($response == 'CAPCHA_NOT_READY') {
            return null;
        }

        if (mb_strpos($response, 'OK|') !== 0) {
            throw new ApiException('Cannot recognise api response (' . $response . ')');
        }

        return mb_substr($response, 3);
    }

    /**
     * Gets account's balance
     *
     * @throws ApiException
     * @throws NetworkException
     */
    public function balance(): float
    {
        $response = $this->res('getbalance');

        return floatval($response);
    }

    /**
     * Reports if captcha was solved correctly (sends `reportbad` or `reportgood` to `/res.php`)
     *
     * @param $id
     * @param $correct
     * @throws ApiException
     * @throws NetworkException
     */
    public function report($id, $correct)
    {
        if ($correct) {
            $this->res(['id' => $id, 'action' => 'reportgood']);
        } else {
            $this->res(['id' => $id, 'action' => 'reportbad']);
        }
    }

    /**
     * Makes request to `/res.php`
     *
     * @param $query
     * @return bool|string
     * @throws ApiException
     * @throws NetworkException
     */
    private function res($query)
    {
        if (is_string($query)) {
            $query = ['action' => $query];
        }

        $query['key'] = $this->apiKey;

        return $this->apiClient->res($query);
    }

    /**
     * Attaches default parameters (passed in constructor) to request
     */
    private function sendAttachDefaultParams(array &$captcha)
    {
        $captcha['key'] = $this->apiKey;

        if ($this->callback) {
            if (!isset($captcha['callback'])) {
                $captcha['callback'] = $this->callback;
            } else if (!$captcha['callback']) {
                unset($captcha['callback']);
            }
        }

        $this->lastCaptchaHasCallback = !empty($captcha['callback']);

        if ($this->softId and !isset($captcha['softId'])) {
            $captcha['softId'] = $this->softId;
        }
    }

    /**
     * Validates if files parameters are correct
     *
     * @param $captcha
     * @param string $key
     * @throws ValidationException
     */
    protected function requireFileOrBase64($captcha, $key = 'file')
    {
        if (!empty($captcha['base64'])) return;

        if (empty($captcha[$key])) {
            throw new ValidationException('File required');
        }

        if (!file_exists($captcha[$key])) {
            throw new ValidationException('File not found (' . $captcha[$key] . ')');
        }
    }

    /**
     * Turns `files` parameter into `file_1`, `file_2`, `file_n` parameters
     *
     * @param $captcha
     * @throws ValidationException
     */
    protected function prepareFilesList(&$captcha)
    {
        $filesLimit = 9;
        $i = 0;

        foreach ($captcha['files'] as $file) {
            if (++$i > $filesLimit) {
                throw new ValidationException('Too many files (max: ' . $filesLimit . ')');
            }

            if (!file_exists($file)) {
                throw new ValidationException('File not found (' . $file . ')');
            }

            $captcha['file_' . $i] = $file;
        }

        unset($captcha['files']);
    }

    /**
     * Extracts files into separate array
     *
     * @param $captcha
     * @return array
     */
    private function extractFiles(&$captcha)
    {
        $files = [];

        $fileKeys = ['file', 'hintImg'];

        for ($i = 1; $i < 10; $i++) {
            $fileKeys[] = 'file_' . $i;
        }

        foreach ($fileKeys as $key) {
            if (!empty($captcha[$key]) and is_file($captcha[$key])) {
                $files[$key] = $captcha[$key];
                unset($captcha[$key]);
            }
        }

        return $files;
    }

    /**
     * Turns passed parameters names into API-specific names
     *
     * @param $params
     */
    private function mapParams(&$params, $method)
    {
        $map = $this->getParamsMap($method);

        foreach ($map as $new => $old) {
            if (isset($params[$new])) {
                $params[$old] = $params[$new];
                unset($params[$new]);
            }
        }

        if (isset($params['proxy'])) {
            $proxy = $params['proxy'];
            $params['proxy'] = $proxy['uri'];
            $params['proxytype'] = $proxy['type'];
        }
    }

    /**
     * Contains rules for `mapParams` method
     *
     * @param $method
     * @return array
     */
    private function getParamsMap($method)
    {
        $commonMap = [
            'base64'        => 'body',
            'caseSensitive' => 'regsense',
            'minLen'        => 'min_len',
            'maxLen'        => 'max_len',
            'hintText'      => 'textinstructions',
            'hintImg'       => 'imginstructions',
            'url'           => 'pageurl',
            'score'         => 'min_score',
            'text'          => 'textcaptcha',
            'rows'          => 'recaptcharows',
            'cols'          => 'recaptchacols',
            'previousId'    => 'previousID',
            'canSkip'       => 'can_no_answer',
            'apiServer'     => 'api_server',
            'softId'        => 'soft_id',
            'callback'      => 'pingback',
        ];

        $methodMap = [
            'userrecaptcha' => [
                'sitekey' => 'googlekey',
            ],
            'funcaptcha' => [
                'sitekey' => 'publickey',
            ],
            'capy' => [
                'sitekey' => 'captchakey',
            ],
        ];

        if (isset($methodMap[$method])) {
            return array_merge($commonMap, $methodMap[$method]);
        }

        return $commonMap;
    }

    /**
     * Helper to determine if array is associative or not
     *
     * @param $arr
     * @return bool
     */
    private function isArrayAssoc($arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

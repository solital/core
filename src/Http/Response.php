<?php

namespace Solital\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Solital\Core\Http\Traits\MessageTrait;
use Solital\Core\Exceptions\InvalidArgumentHttpException;

class Response implements ResponseInterface
{
    use MessageTrait;

    /**
     * Reason phrase
     *
     * @var mixed
     */
    private $statusCode;

    /**
     * Reason phrase
     *
     * @var string
     */
    private ?string $header;

    /**
     * Reason phrase
     *
     * @var string
     */
    protected string $reasonPhrase = '';

    /**
     * Reason phrase
     *
     * @var Request
     */
    protected Request $request;

    /**
     * Status codes and reason phrases
     *
     * @var array
     */
    protected array $statusMessageList = [
        //Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        //Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        //Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        //Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        //Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * @param Request $request
     * @param mixed $body
     * @param int $code
     * @param array $headers
     */
    public function __construct(Request $request, mixed $body = null, int $code = 200, array $headers = [])
    {
        $this->request = $request;

        foreach ($_SERVER as $key => $value) {
            $this->headers[strtolower($key)] = $value;
            $this->headers[strtolower(str_replace('_', '-', $key))] = $value;
        }

        $this->statusCode = $this->sanitizeStatus($code);
        $this->setStreamInstance($body);
        $this->setHeaders($headers);
    }

    /**
     * Set the http status code
     *
     * @param int $code
     * 
     * @return Response
     */
    public function httpCode(int $code): Response
    {
        http_response_code($code);

        return $this;
    }

    /**
     * Redirect the response
     *
     * @param string $url
     * @param null|int $httpCode
     */
    public function redirect(string $url, ?int $httpCode = null): void
    {
        if ($httpCode !== null) {
            $this->httpCode($httpCode);
        }

        $this->header('location: ' . $url);
        exit(0);
    }

    /**
     * @return void
     */
    public function refresh(): void
    {
        $this->redirect($this->request->getUri()->getOriginalUrl());
    }

    /**
     * Add header to response
     * @param string $value
     * @return static
     */
    public function header(string $value): self
    {
        header($value);

        return $this;
    }

    /**
     * Add multiple headers to response
     * @param array $headers
     * @return static
     */
    public function headers(array $headers): self
    {
        foreach ($headers as $header) {
            $this->header($header);
        }

        return $this;
    }

    /**
     * @return int Status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentHttpException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        if ($reasonPhrase == "") {
            $reasonPhrase = $this->statusMessageList[$code];
        }
        $code = $this->sanitizeStatus($code);

        if (!is_string($reasonPhrase)) {
            throw new \InvalidArgumentException("HTTP reason phrase must be a 'string', received '" . (is_object($reasonPhrase) ? get_class($reasonPhrase) : gettype($reasonPhrase)) . "'", 400);
        }

        $clone = clone $this;
        $clone->statusCode = $code;

        if ($reasonPhrase === '' && isset($this->messages[$code])) {
            $reasonPhrase = $this->messages[$code];
        }

        if ($reasonPhrase === '') {
            throw new \InvalidArgumentException("The HTTP reason phrase must be supplied for this code", 417);
        }

        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase(): string
    {
        if ($this->reasonPhrase) {
            return $this->reasonPhrase;
        }

        if (isset($this->messages[$this->statusCode])) {
            return $this->messages[$this->statusCode];
        }
    }

    /**
     * @param mixed $code
     * @return int
     * @throws \InvalidArgumentHttpException For invalid status code arguments.
     */
    private function sanitizeStatus($code): int
    {
        if (!is_numeric($code) || is_float($code) || $code < 100 || $code > 599) {
            throw new \InvalidArgumentException("Invalid HTTP status code. Must be numeric and between 100 and 599", 400);
        }

        return (int) $code;
    }
}

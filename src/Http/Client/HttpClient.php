<?php

namespace Solital\Core\Http\Client;

class HttpClient extends CurlHandle
{
	/**
	 * @var string
	 */
	private static string $url;

	/**
	 * @var string
	 */
	private static string $request;

	/**
	 * @var mixed
	 */
	private static mixed $data = null;

	/**
	 * Create a request with GET method
	 *
	 * @param string $url
	 * 
	 * @return HttpClient
	 */
	public static function get(string $url): HttpClient
	{
		self::$url = $url;
		self::$request = 'GET';
		return new static;
	}

	/**
	 * Create a request with OPTIONS method
	 *
	 * @param string $url
	 * 
	 * @return HttpClient
	 */
	public static function options(string $url): HttpClient
	{
		self::$url = $url;
		self::$request = 'OPTIONS';
		return new static;
	}

	/**
	 * Create a request with DELETE method
	 *
	 * @param string $url
	 * 
	 * @return HttpClient
	 */
	public static function delete(string $url): HttpClient
	{
		self::$url = $url;
		self::$request = 'DELETE';
		return new static;
	}

	/**
	 * Create a request with POST method
	 *
	 * @param string $url
	 * @param mixed $data
	 * 
	 * @return HttpClient
	 */
	public static function post(string $url, mixed $data): HttpClient
	{
		self::$url = $url;
		self::$request = 'POST';
		self::$data = $data;
		return new static;
	}

	/**
	 * Create a request with PUT method
	 *
	 * @param string $url
	 * @param mixed $data
	 * 
	 * @return HttpClient
	 */
	public static function put(string $url, mixed $data): HttpClient
	{
		self::$url = $url;
		self::$request = 'PUT';
		self::$data = $data;
		return new static;
	}

	/**
	 * Create a request with PATCH method
	 *
	 * @param string $url
	 * @param mixed $data
	 * 
	 * @return HttpClient
	 */
	public static function patch(string $url, mixed $data): HttpClient
	{
		self::$url = $url;
		self::$request = 'PATCH';
		self::$data = $data;
		return new static;
	}

	/**
	 * Add current request to async queue request
	 *
	 * @return void
	 */
	public function async(): void
	{
		self::$async_vars[] = [
			'url' => self::$url,
			'request' => self::$request,
			'data' => self::$data
		];
	}

	/**
	 * Return the last response code
	 *
	 * @return int
	 */
	public function getHttpCode(): int
	{
		$this->http_code = true;
		return $this->requestHandle(self::$url, self::$request, self::$data);
	}

	/**
	 * Return response in JSON
	 *
	 * @return string
	 */
	public function responseJson(): string
	{
		if (!empty(self::$async_result)) {
			return json_encode(self::$async_result);
		}

		return $this->requestHandle(self::$url, self::$request, self::$data);
	}

	/**
	 * Return response in array
	 *
	 * @return array
	 */
	public function responseArray(): array
	{
		if (!empty(self::$async_result)) {
			return self::$async_result;
		}

		$response = $this->requestHandle(self::$url, self::$request, self::$data);
		return json_decode($response, true);
	}

	/**
	 * Return response in object
	 *
	 * @return object
	 */
	public function responseObject(): object
	{
		if (!empty(self::$async_result)) {
			return (object)self::$async_result;
		}

		$response = $this->requestHandle(self::$url, self::$request, self::$data);
		return json_decode($response);
	}
}

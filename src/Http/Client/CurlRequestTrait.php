<?php

namespace Solital\Core\Http\Client;

trait CurlRequestTrait
{
	/**
	 * @var array
	 */
	protected array $options = [];

	/**
	 * @var null|string
	 */
	protected ?string $auth_token = null;

	/**
	 * @var null|string
	 */
	protected ?string $auth_type = null;

	/**
	 * @var bool
	 */
	protected bool $http_code = false;

	/**
	 * @var int|float
	 */
	protected int|float $http_version = 1.1;

	/**
	 * @var bool
	 */
	private static bool $ssl_disable = false;

	/**
	 * @var bool
	 */
	private static bool $verbose = false;

	/**
	 * @var array
	 */
	protected static array $async_vars = [];

	/**
	 * @var array
	 */
	protected static array $async_result = [];

	/**
	 * @var array
	 */
	protected static array $headers = [
		'Content-Type: application/json',
		'Accept: application/json'
	];

	/**
	 * Send async request with Curl multi
	 *
	 * @param \Closure $callback
	 * 
	 * @return HttpClient
	 */
	public static function sendAsyncRequest(\Closure $callback): HttpClient
	{
		self::curlIsEnabled();

		call_user_func($callback);

		if (empty(self::$async_vars)) {
			throw new \Exception("You MUST define async request with 'async' method");
		}

		$mh = curl_multi_init();
		$curl_array = [];

		foreach (self::$async_vars as $i => $url) {
			$curl_array[$i] = curl_init($url['url']);
			curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, 1);

			if ($url['request'] == 'POST') {
				curl_setopt($curl_array[$i], CURLOPT_POST, 1);
			} else {
				curl_setopt($curl_array[$i], CURLOPT_CUSTOMREQUEST, strtoupper($url['request']));
			}

			if ($url['data'] != null) {
				curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $url['data']);
			}

			curl_multi_add_handle($mh, $curl_array[$i]);
		}

		$running = NULL;

		do {
			$status = curl_multi_exec($mh, $running);
			curl_multi_select($mh);
		} while ($running > 0);

		foreach (self::$async_vars as $i => $url) {
			self::$async_result[$url['url']] = curl_multi_getcontent($curl_array[$i]);
		}

		foreach (self::$async_vars as $i => $url) {
			curl_multi_remove_handle($mh, $curl_array[$i]);
		}

		curl_multi_close($mh);
		return new static;
	}

	/**
	 * Send request
	 *
	 * @param string $url
	 * @param string $request
	 * @param mixed $data
	 * 
	 * @return mixed
	 */
	protected function requestHandle(string $url, string $request, mixed $data = null): mixed
	{
		self::curlIsEnabled();

		//dump_die(self::$headers);

		$curl = curl_init();

		$this->options = [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPHEADER => self::$headers
		];

		if (self::$ssl_disable == true) {
			$this->options[CURLOPT_SSL_VERIFYHOST] = 0;
			$this->options[CURLOPT_SSL_VERIFYPEER] = 0;
		}

		if (self::$verbose == true) {
			$this->options[CURLOPT_VERBOSE] = 1;
		}

		$this->setAuth($this->auth_type);
		$this->setRequest($request, $data);
		$this->setHttpVersion($this->http_version);

		/* foreach ($this->options as $name => $value) {
			curl_setopt($curl, $name, $value);
		} */

		curl_setopt_array($curl, $this->options);

		$response = curl_exec($curl);

		if ($response == false) {
			http_response_code(404);
			$response = curl_error($curl);
		}

		if ($this->http_code == true) {
			$response = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
		}

		curl_close($curl);
		return $response;
	}

	/**
	 * Set request with POST, PUT, PATCH and OPTIONS
	 *
	 * @param string $request
	 * @param array|null $data
	 * 
	 * @return void
	 */
	private function setRequest(string $request, ?array $data = null): void
	{
		$request = strtoupper($request);

		if ($request != 'GET') {
			switch ($request) {
				case 'POST':
					$this->options[CURLOPT_POST] = 1;
					break;

				case 'PUT' || 'PATCH':
					$this->options[CURLOPT_CUSTOMREQUEST] = $request;
					break;

				case 'OPTIONS':
					$this->options[CURLOPT_CUSTOMREQUEST] = 'OPTIONS';
					$this->options[CURLOPT_HEADER] = true;
					$this->options[CURLOPT_NOBODY] = true;
					break;

				default:
					$this->options[CURLOPT_CUSTOMREQUEST] = $request;
					break;
			}
		}

		if ($data != null) {
			$this->options[CURLOPT_POSTFIELDS] = json_encode($data);
		}
	}

	/**
	 * Set authentication
	 *
	 * @param string|null $auth_type
	 * 
	 * @return void
	 */
	private function setAuth(?string $auth_type): void
	{
		if ($auth_type != null) {
			switch ($auth_type) {
				case 'bearer':
					$this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BEARER;
					$this->options[CURLOPT_XOAUTH2_BEARER] = $this->auth_token;
					break;

				case 'basic':
					$this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
					$this->options[CURLOPT_USERPWD] = $this->auth_token;
					break;

				case 'digest':
					$this->options[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
					$this->options[CURLOPT_USERPWD] = $this->auth_token;
					break;
			}
		}
	}

	/**
	 * Set HTTP version
	 *
	 * @param int|float $http_version
	 * 
	 * @return void
	 */
	private function setHttpVersion(int|float $http_version): void
	{
		switch ($http_version) {
			case 1.0:
				$this->options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
				break;

			case 1.1:
				$this->options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
				break;

			case 2.0:
				$this->options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2_0;
				break;

			case 0:
				$this->options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_NONE;
				break;

			default:
				throw new \Exception("HTTP version is not valid. Choise among 0, 1.0, 1.1, 2.0");
				break;
		}
	}

	/**
	 * @return void
	 */
	protected static function curlIsEnabled(): void
	{
		if (!extension_loaded('curl')) {
			http_response_code(404);
			throw new \Exception("'curl' extension is not enabled or installed");
		}
	}
}

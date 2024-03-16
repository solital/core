<?php

namespace Solital\Core\Http\Client;

class CurlHandle
{
	use CurlRequestTrait;

	/**
	 * Disable SSL verify
	 *
	 * @return HttpClient
	 */
	public function disableSSL(): HttpClient
	{
		self::$ssl_disable = true;
		return $this;
	}

	/**
	 * Set default headers
	 *
	 * @param array $headers
	 * 
	 * @return HttpClient
	 */
	public function setHeaders(array $headers): HttpClient
	{
		self::$headers = $headers;
		return $this;
	}

	/**
	 * Enable output verbose information
	 *
	 * @return HttpClient
	 */
	public function enableVerboseOutput(): HttpClient
	{
		self::$verbose = true;
		return $this;
	}

	/**
	 * Add a bearer token
	 *
	 * @param string $token
	 * 
	 * @return HttpClient
	 */
	public function bearerAuth(string $token): HttpClient
	{
		$this->auth_type = "bearer";
		$this->auth_token = $token;
		return $this;
	}

	/**
	 * Send basic authentication
	 *
	 * @param string $username
	 * @param string $password
	 * 
	 * @return HttpClient
	 */
	public function basicAuth(string $username, string $password): HttpClient
	{
		$this->auth_type = "basic";
		$this->auth_token = "$username:$password";
		return $this;
	}

	/**
	 * Send digest authentication
	 *
	 * @param string $username
	 * @param string $password
	 * 
	 * @return HttpClient
	 */
	public function basicDigest(string $username, string $password): HttpClient
	{
		$this->auth_type = "digest";
		$this->auth_token = "$username:$password";
		return $this;
	}

	/**
	 * Set HTTP version
	 *
	 * @param int|float $http_version
	 * 
	 * @return HttpClient
	 */
	public function setHttpVersion(int|float $http_version): HttpClient
	{
		$this->http_version = $http_version;
		return $this;
	}

	/**
	 * Download a file
	 *
	 * @param string $url
	 * @param string $file_name
	 * @param bool $return_result
	 * 
	 * @return mixed
	 */
	public static function downloader(string $url, string $file_name, bool $return_result = false): mixed
	{
		self::curlIsEnabled();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		$fp = fopen($file_name, 'w');
		fwrite($fp, $result);
		fclose($fp);

		if ($return_result == true) {
			return $result;
		}

		if ($result != false) {
			return true;
		}
	}

	/**
	 * Upload file with Curl
	 *
	 * @param string $url
	 * @param string $data
	 * @param string $file_name
	 * @param bool $debug
	 * 
	 * @return mixed
	 */
	public static function upload(string $url, string $data, string $file_name, bool $debug = false): mixed
	{
		self::curlIsEnabled();

		$txt_curlfile = new \CURLStringFile($data, $file_name, 'text/plain');

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);

		if ($debug == true) {
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $txt_curlfile]);
		$result = curl_exec($ch);

		return $result;
	}
}

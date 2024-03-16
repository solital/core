<?php

namespace Solital\Core\Http\Client;

class JWT
{
	/**
	 * Create JWT token
	 *
	 * @param array $payload
	 * @param string $secret
	 * 
	 * @return string
	 */
	public static function encode(array $payload, string $secret): string
	{
		$header = json_encode([
			"alg" => "HS256",
			"typ" => "JWT"
		]);

		$payload = json_encode($payload);

		$header_payload = static::base64url_encode($header) . '.' .
			static::base64url_encode($payload);

		$signature = hash_hmac('sha256', $header_payload, $secret, true);

		return
			static::base64url_encode($header) . '.' .
			static::base64url_encode($payload) . '.' .
			static::base64url_encode($signature);
	}

	/**
	 * Get payload in array ot throw exception
	 *
	 * @param string $token
	 * @param string $secret
	 * 
	 * @return array
	 */
	public static function decode(string $token, string $secret): array
	{
		$token = explode('.', $token);
		$header = static::base64_decode_url($token[0]);
		$payload = static::base64_decode_url($token[1]);

		$signature = static::base64_decode_url($token[2]);

		$header_payload = $token[0] . '.' . $token[1];

		if (hash_hmac('sha256', $header_payload, $secret, true) !== $signature) {
			http_response_code(401);
			throw new \Exception("Invalid signature: You aren't allowed to access the router");
		}

		return json_decode($payload, true);
	}

	/**
	 * Protect route with secret
	 *
	 * @param string $secret
	 * 
	 * @return array
	 */
	public static function protectRoute(#[\SensitiveParameter] string $secret): array
	{
		$token = JWT::getToken();

		if ($token == null) {
			http_response_code(404);
			throw new \Exception("Token JWT not found");
		}

		$decode = JWT::decode($token, $secret);
		return $decode;
	}

	/**
	 * Get JWT token
	 *
	 * @return string|null
	 */
	public static function getToken(): ?string
	{
		if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$token = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
			return $token[1];
		}

		return null;
	}

	private static function base64url_encode(string $data): string
	{
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
	}

	private static function base64_decode_url(string $string): string
	{
		return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
	}
}

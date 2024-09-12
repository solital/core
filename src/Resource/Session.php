<?php

namespace Solital\Core\Resource;

use Delight\Http\ResponseHeader;
use Deprecated\Deprecated;

final class Session
{
	private function __construct() {}

	/**
	 * Starts or resumes a session in a way compatible to PHP's built-in `session_start()` function
	 *
	 * @param string|null $sameSiteRestriction indicates that the cookie should not be sent 
	 * 					  along with cross-site requests (either `null`, `None`, `Lax` or `Strict`)
	 * @deprecated
	 */
	#[Deprecated(since: "4.5.0")]
	public static function start(?string $sameSiteRestriction = Cookie::SAME_SITE_RESTRICTION_LAX)
	{
		if (session_status() === PHP_SESSION_DISABLED) {
			// run PHP's built-in equivalent
			\session_start();

			// intercept the cookie header (if any) and rewrite it
			self::rewriteCookieHeader($sameSiteRestriction);
		}
	}

	/**
	 * Returns or sets the ID of the current session
	 *
	 * @param string|null $newId (optional) a new session ID to replace the current session ID
	 * @return string the (old) session ID or an empty string
	 */
	public static function id(?string $newId = null): string
	{
		return ($newId === null) ? session_id() : session_id($newId);
	}

	/**
	 * Re-generates the session ID in a way compatible to PHP's built-in `session_regenerate_id()` function
	 *
	 * @param bool $deleteOldSession whether to delete the old session or not
	 * @param string|null $sameSiteRestriction indicates that the cookie should not be sent along 
	 * 					  with cross-site requests (either `null`, `None`, `Lax` or `Strict`)
	 */
	public static function regenerate(
		bool $deleteOldSession = false,
		?string $sameSiteRestriction = Cookie::SAME_SITE_RESTRICTION_LAX
	): void {
		// run PHP's built-in equivalent
		\session_regenerate_id($deleteOldSession);

		// intercept the cookie header (if any) and rewrite it
		self::rewriteCookieHeader($sameSiteRestriction);
	}

	/**
	 * Checks whether a value for the specified key exists in the session
	 *
	 * @param string $key the key to check
	 * @return bool whether there is a value for the specified key or not
	 */
	public static function has(string $key): bool
	{
		return isset($_SESSION[$key]);
	}

	/**
	 * Returns the requested value from the session or, if not found, the specified default value
	 *
	 * @param string $key the key to retrieve the value for
	 * @param mixed $defaultValue the default value to return if the requested value cannot be found
	 * @return mixed the requested value or the default value
	 */
	public static function get(string $key, mixed $defaultValue = null): mixed
	{
		return (isset($_SESSION[$key])) ? $_SESSION[$key] : $defaultValue;
	}

	/**
	 * Returns the requested value and removes it from the session
	 *
	 * This is identical to calling `get` first and then `remove` for the same key
	 *
	 * @param string $key the key to retrieve and remove the value for
	 * @param mixed $defaultValue the default value to return if the requested value cannot be found
	 * @return mixed the requested value or the default value
	 */
	public static function take(string $key, mixed $defaultValue = null): mixed
	{
		if (isset($_SESSION[$key])) {
			$value = $_SESSION[$key];
			unset($_SESSION[$key]);

			return $value;
		} else {
			return $defaultValue;
		}
	}

	/**
	 * Sets the value for the specified key to the given value
	 *
	 * Any data that already exists for the specified key will be overwritten
	 *
	 * @param string $key the key to set the value for
	 * @param mixed $value the value to set
	 * 
	 * @return void
	 */
	public static function set(string $key, mixed $value): void
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Removes the value for the specified key from the session
	 *
	 * @param string $key the key to remove the value for
	 * 
	 * @return true
	 */
	public static function delete(string|array $key, mixed $value = null): true
	{
		if (!is_null($value)) {
			unset($_SESSION[$key][$value]);
			return true;
		}

		unset($_SESSION[$key]);
		return true;
	}

	/**
	 * Intercepts and rewrites the session cookie header
	 *
	 * @param string|null $sameSiteRestriction indicates that the cookie should not be sent along 
	 * 					  with cross-site requests (either `null`, `None`, `Lax` or `Strict`)
	 */
	private static function rewriteCookieHeader(
		?string $sameSiteRestriction = Cookie::SAME_SITE_RESTRICTION_LAX
	): void {
		// get and remove the original cookie header set by PHP
		$originalCookieHeader = ResponseHeader::take('Set-Cookie', \session_name() . '=');

		// if a cookie header has been found
		if (isset($originalCookieHeader)) {
			// parse it into a cookie instance
			$parsedCookie = Cookie::parse($originalCookieHeader);

			// if the cookie has successfully been parsed
			if (isset($parsedCookie)) {
				// apply the supplied same-site restriction
				$parsedCookie->setSameSiteRestriction($sameSiteRestriction);

				if ($parsedCookie->getSameSiteRestriction() === Cookie::SAME_SITE_RESTRICTION_NONE && !$parsedCookie->isSecureOnly()) {
					\trigger_error('You may have to enable the \'session.cookie_secure\' directive in the configuration in \'php.ini\' or via the \'ini_set\' function', \E_USER_WARNING);
				}

				// save the cookie
				$parsedCookie->save();
			}
		}
	}
}

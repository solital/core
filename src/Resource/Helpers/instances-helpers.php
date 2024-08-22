<?php

use Solital\Core\Cache\SimpleCache;
use Solital\Core\Resource\{Collection\ArrayCollection, JSON, Message, Session, Str\Str};
use Solital\Core\Resource\Memorize\{Memorizator, Storage, Utils};
use Solital\Core\Security\Hash;

/**
 * Manipulate the `ArrayCollection` class without having to instantiate it.
 * 
 * @param mixed $value
 * 
 * @return ArrayCollection
 */
function collection(mixed $value = null): ArrayCollection
{
    return new ArrayCollection($value);
}

/**
 * Manipulate the `Str` class without having to instantiate it
 * 
 * @param string $string
 * 
 * @return Str
 */
function str(string $string): Str
{
    return new Str($string);
}

/**
 * Return a `SimpleCache` instance
 *
 * @param string|null $drive
 * 
 * @return SimpleCache
 */
function cache(?string $drive = null): SimpleCache
{
    return new SimpleCache($drive);
}

/**
 * Encode any value to JSON
 * 
 * @param mixed $value
 * @param int $constants
 * 
 * @return string
 */
function encodeJSON($value, int $constants = JSON_UNESCAPED_UNICODE): string
{
    $json = container("solital-json");
    $json->setConstants($constants);
    return $json->encode($value);
}

/**
 * Decode a JSON
 * 
 * @param mixed $value
 * @param bool $toArray
 * 
 * @return object|array
 */
function decodeJSON($value, bool $toArray = false): mixed
{
    $json = container("solital-json");
    return $json->decode($value, $toArray);
}

/**
 * Create a message using `Message` class without having to instantiate it
 * 
 * @param string $key
 * @param string $message
 * 
 * @return Message
 */
function message(?string $key = null, ?string $message = null): Message
{
    $msg = new Message();
    if ($key !== null && $message !== null) $msg->new($key, $message);
    return $msg;
}

/**
 * Create or return a session value
 * 
 * @param string $key
 * @param mixed $value
 * @param mixed $key
 * @param bool $delete
 * @param bool $take
 * 
 * @return mixed
 */
function session(
    string $key,
    mixed $value = null,
    mixed $defaultValue = null,
    bool $delete = false,
    bool $take = false
): mixed {
    if ($value != null && $take == false) {
        Session::set($key, $value);
        return true;
    }
    
    if ($delete == true) return Session::delete($key);
    if ($take == true) return Session::take($key, $defaultValue);

    return Session::get($key, $defaultValue);
}

/**
 * Memorize provides simple in-var cache for closures.
 * At the first call result will be calculated and stored in cache.
 * If the closure with the same arguments was run before memorize will return result from cache without the closure call.
 *
 * @param Closure $lambda
 * @param null|string $paramsHash if pass null paramsHash will be calculated automatically
 * @return mixed
 */
function memorize(Closure $lambda, $paramsHash = null)
{
    $getStorage = function (Closure $lambda) {
        $reflection = new ReflectionFunction($lambda);
        $that = $reflection->getClosureThis();

        if ($that) {
            if (!isset($that->memorizeStorage)) {
                $that->memorizeStorage = new Storage();
            }

            $class = get_class($that);
            $reflection_class = new \ReflectionClass($class);
            $attr = $reflection_class->getAttributes();

            $values = array_map(fn($attribute) => $attribute->getName(), $attr);

            if (!in_array("AllowDynamicProperties", $values)) {
                throw new \Exception("You must add 'AllowDynamicProperties' attribute on " . $class);
            }

            return $that->memorizeStorage;
        } else {
            global $_globalMemorizeStorage;

            if (is_null($_globalMemorizeStorage)) {
                $_globalMemorizeStorage = new Storage();
            }

            return $_globalMemorizeStorage;
        }
    };

    if (is_null($paramsHash)) {
        $reflection = new ReflectionFunction($lambda);
        $paramsHash = Utils::hash($reflection->getStaticVariables());
    }

    $contextName = Utils::stringify($lambda);
    return Memorizator::memorize($contextName, $lambda, $paramsHash, $getStorage($lambda));
}

/**
 * Generates an encrypted key
 * 
 * @param string $value
 * @param string $time
 * 
 * @return string
 */
function encrypt(string $value, string $time = '+1 hour'): string
{
    return Hash::encrypt($value, $time);
}

/**
 * Decrypts a key
 *
 * @param string $key
 * 
 * @return Hash
 */
function decrypt(string $key): Hash
{
    return Hash::decrypt($key);
}
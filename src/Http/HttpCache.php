<?php

namespace Solital\Core\Http;

use DateTime;
use Solital\Core\Http\Exceptions\HttpCacheException;

class HttpCache
{
    /**
     * @var int
     */
    private $time;

    /**
     * @var int
     */
    private $code;

    /**
     * @param string $privacity
     * @param int $max_age
     * 
     * @return HttpCache
     */
    public function cacheControl(string $privacity, int $max_age, bool $revalidate = false): HttpCache
    {
        $this->time = $max_age;

        if ($privacity != "public" && $privacity != "private") {
            HttpCacheException::alertMessage(404, "'$privacity' is invalid. Use 'public' or 'private'");

            return $this;
        }

        if ($revalidate == true) {
            header("Cache-Control: " . $privacity . ", max-age=" . $this->time . ", must-revalidate");

            return $this;
        } else {
            header("Cache-Control: " . $privacity . ", max-age=" . $this->time);

            return $this;
        }
    }

    /**
     * @return HttpCache
     */
    public function noCacheControl(): HttpCache
    {
        header("Cache-Control: no-cache, no-store, must-revalidate");

        return $this;
    }

    /**
     * @return HttpCache
     */
    public function expires(): HttpCache
    {
        $date = new DateTime();
        $atual_time = $date->getTimestamp();

        #var_dump($atual_time, $this->time);

        if (strtotime($this->time) < strtotime($atual_time)) {
            $this->code = 200;
        } else {
            $this->code = 304;
        }

        $date = gmdate("D, d M Y H:i:s", time() + $this->time) . " GMT";

        /* $date = date("d M Y", strtotime($date));
        $day = date("D", strtotime($date));
        $hour = date("H:i:s", strtotime($hour)); */

        #$code = (new HttpCode())->responseCode($this->code);
        #header($code);

        http_response_code($this->code);
        header("Expires: " . $date);

        return $this;
    }

    /**
     * @return HttpCache
     */
    public function lastModified(): HttpCache
    {
        $date = new DateTime();
        $atual_time = $date->getTimestamp();

        if (strtotime($this->time) < strtotime($atual_time)) {
            $this->code = 200;
        } else {
            $this->code = 304;
        }

        $date = gmdate("D, d M Y H:i:s", time() + $this->time) . " GMT";
        http_response_code($this->code);
        header("Last-Modified: " . $date);

        return $this;
    }

    /**
     * @return HttpCache
     */
    public function ifModifiedSince(): HttpCache
    {
        $date = new DateTime();
        $atual_time = $date->getTimestamp();

        if (strtotime($this->time) < strtotime($atual_time)) {
            $this->code = 200;
        } else {
            $this->code = 304;
        }

        $date = gmdate("D, d M Y H:i:s", time() + $this->time) . " GMT";
        http_response_code($this->code);
        header("If-Modified-Since: " . $date);

        return $this;
    }

    /**
     * @param string $file
     * 
     * @return HttpCache
     */
    public function eTag(string $file): HttpCache
    {
        $hash = '"' . md5($file) . '"';

        if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER) && $_SERVER['HTTP_IF_NONE_MATCH'] == $hash) {
            header('HTTP/1.1 304 Not Modified');
            header('Date: ' . gmstrftime('%a, %d %b %Y %T %Z', $_SERVER['REQUEST_TIME']));
            header('Cache-Control: ');
            header('Pragma: ');
            header('Expires: ');
            exit(0);
        }

        header('ETag: ' . $hash);

        return $this;
    }
}

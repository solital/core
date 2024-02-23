<?php

namespace Solital\Core\Cache;

use Solital\Core\Cache\SimpleCache;
use Solital\Core\Cache\Exception\InvalidArgumentException;

class HttpCache extends SimpleCache
{
    /**
     * @var int
     */
    private int $time;

    /**
     * @var \DateTime
     */
    private \DateTime $date_time;

    /**
     * @var int
     */
    private int $code;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->date_time = new \DateTime();
    }

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
            throw new InvalidArgumentException("'$privacity' is invalid. Use 'public' or 'private'", 404);
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
        $atual_time = $this->date_time->getTimestamp();

        if (strtotime((int)$this->time) < strtotime((int)$atual_time)) {
            $this->code = 200;
        } else {
            $this->code = 304;
        }

        $date = gmdate("D, d M Y H:i:s", time() + $this->time) . " GMT";

        http_response_code($this->code);
        header("Expires: " . $date);

        return $this;
    }

    /**
     * @return HttpCache
     */
    public function lastModified(): HttpCache
    {
        $atual_time = $this->date_time->getTimestamp();

        if (strtotime((int)$this->time) < strtotime((int)$atual_time)) {
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
        $atual_time = $this->date_time->getTimestamp();

        if (strtotime((int)$this->time) < strtotime((int)$atual_time)) {
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
            header('Date: ' . date('D, d M Y H:i:s', $_SERVER['REQUEST_TIME']));
            header('Cache-Control: ');
            header('Pragma: ');
            header('Expires: ');
            exit(0);
        }

        header('ETag: ' . $hash);

        return $this;
    }
}

<?php

namespace Solital\Core\Http\Controller;

use Solital\Core\Wolf\Wolf;
use Solital\Core\Http\Controller\{
    BaseControllerTrait,
    HttpControllerTrait
};

abstract class Controller extends Wolf
{
    use BaseControllerTrait;
    use HttpControllerTrait;

    /**
     * @return void
     */
    public function removeParamsUrl(): void
    {
        $http = 'http://';

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $http = 'https://';
        }

        $url = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $url = parse_url($url);

        if (isset($url['query'])) {
            if (strpos($_SERVER["HTTP_HOST"], "localhost") !== false) {
                header('Refresh: 0, url =' . $url['scheme'] . "://" . $_SERVER["HTTP_HOST"] . $url['path']);
                die;
            } else {
                if (isset($url['path'])) {
                    header('Refresh: 0, url =' . $url['scheme'] . "://" . $url['host'] . $url['path']);
                    die;
                } else {
                    header('Refresh: 0, url =' . $url['scheme'] . "://" . $url['host']);
                    die;
                }
            }
        }
    }
}

<?php

class DummyController
{
    public function method1($path)
    {
        echo $path;
    }

    public function method2()
    {
    }

    public function method3($param1, $param2)
    {
    }

    public function param($param = null)
    {
        echo join(', ', func_get_args());
    }

    public function params($lang = null, $name = null)
    {
        echo join(', ', func_get_args());
    }

    public function unicode($listado = null, $category = null)
    {
        echo join(', ', func_get_args());
    }

    public function getTest()
    {
        echo 'getTest';
    }

    public function postTest()
    {
        echo 'postTest';
    }

    public function putTest()
    {
        echo 'putTest';
    }

    public function about()
    {
        # code...
    }

    public function contact()
    {
        # code...
    }

    public function classLoader()
    {
        echo 'Loader';
    }
}

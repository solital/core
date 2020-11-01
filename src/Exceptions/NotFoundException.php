<?php

namespace Solital\Core\Exceptions;

class NotFoundException 
{    
    /**
     * WolfNotFound
     *
     * @param string $view
     * @param string $ext
     * @return void
     */
    public static function WolfNotFound(string $view, string $ext) 
    {
        include_once dirname(__DIR__).'/Exceptions/templates/error-wolf.php';
        die;
    }
    
    /**
     * GuardianNotFound
     *
     * @param string $type
     * @param string $msg
     * @return void
     */
    public static function GuardianNotFound(string $type, string $msg) 
    {
        include_once dirname(__DIR__).'/Exceptions/templates/error-guardian.php';
        die;
    }
    
    /**
     * FileSystemNotFound
     *
     * @param string $view
     * @return void
     */
    public static function FileSystemNotFound(string $view)
    {
        include_once dirname(__DIR__).'/Exceptions/templates/error-file.php';
        die;
    }
    
}

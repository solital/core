<?php
/**
 * @generated class generated using Vinci Console
 */
namespace Solital\Components\Controller;

use Solital\Core\Http\Controller\Controller;

class NameDefault extends Controller
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function home(): mixed
    {
        return view('');
    }
}

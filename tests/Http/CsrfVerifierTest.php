<?php

require_once 'Dummy/Security/DummyCsrfVerifier.php';
require_once dirname(__DIR__) . '/Course/Dummy/Security/SilentTokenProvider.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Http\Exception\TokenMismatchException;
use Solital\Core\Http\Uri;

class CsrfVerifierTest extends TestCase
{
    public function testTokenFail()
    {
        $this->expectException(TokenMismatchException::class);

        global $_POST;

        $tokenProvider = new SilentTokenProvider();

        $router = TestRouter::router();
        $router->getRequest()->setMethod('post');
        $router->getRequest()->setUrl(new Uri('/page'));
        $csrf = new DummyCsrfVerifier();
        $csrf->setTokenProvider($tokenProvider);

        $csrf->validateToken($router->getRequest());
    }
    
    public function testTokenPass()
    {
        $_REQUEST['csrf_token'] = uniqid();
        
        global $_POST;

        $tokenProvider = new SilentTokenProvider();

        $_POST[DummyCsrfVerifier::POST_KEY] = $tokenProvider->getToken();

        TestRouter::router()->reset();

        $router = TestRouter::router();
        $router->getRequest()->setMethod('post');
        $router->getRequest()->setUrl(new Uri('/page'));

        $csrf = new DummyCsrfVerifier();
        $csrf->setTokenProvider($tokenProvider);
        $csrf->validateToken($router->getRequest());

        // If handle doesn't throw exception, the test has passed
        $this->assertTrue(true);
    }

    public function testExcludeInclude()
    {
        $router = TestRouter::router();
        $csrf = new DummyCsrfVerifier();
        $request = $router->getRequest();

        $request->setUrl(new Uri('/exclude-page'));
        $this->assertTrue($csrf->testSkip($router->getRequest()));

        $request->setUrl(new Uri('/exclude-all/page'));
        $this->assertTrue($csrf->testSkip($router->getRequest()));

        /* $request->setUrl(new Uri('/exclude-all/include-page'));
        $this->assertFalse($csrf->testSkip($router->getRequest())); */

        $request->setUrl(new Uri('/include-page'));
        $this->assertFalse($csrf->testSkip($router->getRequest()));
    }

}
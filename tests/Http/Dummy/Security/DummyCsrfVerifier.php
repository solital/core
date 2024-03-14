<?php

use Solital\Core\Http\Middleware\BaseCsrfVerifier;
use Solital\Core\Http\Request;

class DummyCsrfVerifier extends BaseCsrfVerifier {

    protected array $except = [
        '/exclude-page',
        '/exclude-all/*',
    ];

    protected array $include = [
        '/exclude-all/include-page',
    ];

    public function testSkip(Request $request) {
        return $this->skip($request);
    }
}
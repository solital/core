<?php

namespace Solital\Core\Session\Handler;

use SensitiveParameter;

/**
 * Here is a wrapper to log in a file each session's operations.
 * Useful to investigate sessions locks (which prevent PHP to serve simultaneous requests for a same client).
 * Just change the file name at the end to dump logs where you want. 
 */
class DumpSessionHandler extends \SessionHandler
{
    private string $fich;

    public function __construct()
    {
        $this->fich = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "sessions.log";
    }

    public function close(): bool
    {
        $this->log('close');
        return parent::close();
    }

    public function create_sid(): string
    {
        $this->log('create_sid');
        return parent::create_sid();
    }

    public function destroy(#[SensitiveParameter] string $session_id): bool
    {
        $this->log('destroy(' . $session_id . ')');
        return parent::destroy($session_id);
    }

    public function gc(int $maxlifetime): int|false
    {
        $this->log('close(' . $maxlifetime . ')');
        return parent::gc($maxlifetime);
    }

    public function open(string $save_path, string $session_name): bool
    {
        $this->log('open(' . $save_path . ', ' . $session_name . ')');
        return parent::open($save_path, $session_name);
    }

    public function read(#[SensitiveParameter] string $session_id): string|false
    {
        $this->log('read(' . $session_id . ')');
        return parent::read($session_id);
    }

    public function write(
        #[SensitiveParameter] string $session_id,
        #[SensitiveParameter] string $session_data
    ): bool {
        $this->log('write(' . $session_id . ', ' . $session_data . ')');
        return parent::write($session_id, $session_data);
    }

    private function log(string $action): void
    {
        $base_uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
        $hdl = fopen($this->fich, 'a');
        fwrite($hdl, date('Y-m-d h:i:s') . ' ' . $base_uri . ' : ' . $action . "\n");
        fclose($hdl);
    }
}

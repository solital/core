<?php

namespace Solital\Core\Session\Handler;

use APCu\APCu;
use Override;
use Solital\Core\Session\Exception\SessionExtensionNotFoundException;
use SensitiveParameter;

// to enable paste this line right before session_start():
//   new Session_APC;
class ApcuSessionHandler implements \SessionHandlerInterface
{
    private APCu $apcu;
    protected string $prefix;
    protected int $ttl;
    protected int $lockTimeout = 10; // if empty, no session locking, otherwise seconds to lock timeout

    public function __construct(array $params = [])
    {
        if (!extension_loaded('apcu')) throw new SessionExtensionNotFoundException('Extension `apcu` not enabled!');

        $this->apcu = new APCu();
        $def = session_get_cookie_params();
        $this->ttl = $def['lifetime'];
        if (isset($params['ttl'])) $this->ttl = $params['ttl'];
        if (isset($params['lock_timeout'])) $this->lockTimeout = $params['lock_timeout'];
    }

    #[Override]
    public function open(#[SensitiveParameter] string $savePath, string $sessionName): bool
    {
        $this->prefix = 'BSession/' . $sessionName;

        if (!$this->apcu->exists($this->prefix . '/TS')) {
            // creating non-empty array @see http://us.php.net/manual/en/function.apc-store.php#107359
            $this->apcu->store($this->prefix . '/TS', ['']);
            $this->apcu->store($this->prefix . '/LOCK', ['']);
        }
        return true;
    }

    #[Override]
    public function close(): bool
    {
        return true;
    }

    #[Override]
    public function read(#[SensitiveParameter] string $id): string|false
    {
        $key = $this->prefix . '/' . $id;
        if (!$this->apcu->exists($key)) return ''; // no session

        // redundant check for ttl before read
        if ($this->ttl) {
            $ts = $this->apcu->fetch($this->prefix . '/TS');
            if (empty($ts[$id])) {
                return ''; // no session
            } elseif (!empty($ts[$id]) && $ts[$id] + $this->ttl < time()) {
                unset($ts[$id]);
                $this->apcu->delete($key);
                $this->apcu->store($this->prefix . '/TS', $ts);
                return ''; // session expired
            }
        }

        if (!$this->lockTimeout) {
            $locks = $this->apcu->fetch($this->prefix . '/LOCK');
            if (!empty($locks[$id])) {
                while (!empty($locks[$id]) && $locks[$id] + $this->lockTimeout >= time()) {
                    usleep(10000); // sleep 10ms
                    $locks = $this->apcu->fetch($this->prefix . '/LOCK');
                }
            }
            /*
            // by default will overwrite session after lock expired to allow smooth site function
            // alternative handling is to abort current process
            if (!empty($locks[$id])) {
                return false; // abort read of waiting for lock timed out
            }
            */
            $locks[$id] = time(); // set session lock
            $this->apcu->store($this->prefix . '/LOCK', $locks);
        }

        return $this->apcu->fetch($key); // if no data returns empty string per doc
    }

    #[Override]
    public function write(#[SensitiveParameter] string $id, #[SensitiveParameter] string $data): bool
    {
        $ts = $this->apcu->fetch($this->prefix . '/TS');
        $ts[$id] = time();
        $this->apcu->store($this->prefix . '/TS', $ts);

        $locks = $this->apcu->fetch($this->prefix . '/LOCK');
        unset($locks[$id]);
        $this->apcu->store($this->prefix . '/LOCK', $locks);

        return $this->apcu->store($this->prefix . '/' . $id, $data, $this->ttl);
    }

    #[Override]
    public function destroy(#[SensitiveParameter] string $id): bool
    {
        $ts = $this->apcu->fetch($this->prefix . '/TS');
        unset($ts[$id]);
        $this->apcu->store($this->prefix . '/TS', $ts);

        $locks = $this->apcu->fetch($this->prefix . '/LOCK');
        unset($locks[$id]);
        $this->apcu->store($this->prefix . '/LOCK', $locks);

        return $this->apcu->delete($this->prefix . '/' . $id);
    }

    #[Override]
    public function gc(int $lifetime): int|false
    {
        if ($this->ttl) $lifetime = min($lifetime, $this->ttl);
        $ts = $this->apcu->fetch($this->prefix . '/TS');

        foreach ($ts as $id => $time) {
            if ($time + $lifetime < time()) {
                $this->apcu->delete($this->prefix . '/' . $id);
                unset($ts[$id]);
            }
        }

        return $this->apcu->store($this->prefix . '/TS', $ts);
    }
}

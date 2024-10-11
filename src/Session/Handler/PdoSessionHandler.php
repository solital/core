<?php

namespace Solital\Core\Session\Handler;

use Katrina\Connection\Connection;
use Solital\Core\Session\SessionMigration;
use SensitiveParameter;
use ReturnTypeWillChange;
use Override;
use PDO;

class PdoSessionHandler implements \SessionHandlerInterface
{
    private ?PDO $pdo;
    private string $sessionName;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        (new SessionMigration)->up();
    }

    #[Override]
    public function open(#[SensitiveParameter] string $savePath, string $sessionName): bool
    {
        $this->sessionName = $sessionName;
        return true;
    }

    #[Override]
    public function close(): bool
    {
        $this->pdo = null;
        return true;
    }

    #[ReturnTypeWillChange]
    #[Override]
    public function read(#[SensitiveParameter] string $id): string|false
    {
        $sql = "SELECT value FROM session WHERE name = :name AND id = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute([":name" => $this->sessionName, ":id" => $id]);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return !isset($result["value"]) ? "" : $result["value"];
    }

    #[Override]
    public function write(#[SensitiveParameter] string $id, #[SensitiveParameter] string $data): bool
    {
        $sql = "SELECT value FROM session WHERE name = :name AND id = :id";
        $sth = $this->pdo->prepare($sql);
        $sth->execute([":name" => $this->sessionName, ":id" => $id]);

        if (count($sth->fetchAll()) == 0) {
            $sql =
                "INSERT INTO session (id, name, value, last_update) values (:id, :name, :value, :last_update)";
        } else {
            $sql =
                "UPDATE session SET value = :value, last_update = :last_update WHERE id = :id AND name = :name";
        }

        $sth = $this->pdo->prepare($sql);

        return $sth->execute([
            ":id" => $id,
            ":name" => $this->sessionName,
            ":value" => $data,
            ":last_update" => strtotime(date("Y-m-d H:i:s")),
        ]);
    }

    #[Override]
    public function destroy(#[SensitiveParameter] $id): bool
    {
        $sql = "DELETE FROM session WHERE name = :name and id = :id";
        $sth = $this->pdo->prepare($sql);
        return $sth->execute([":name" => $this->sessionName, ":id" => $id]);
    }

    #[ReturnTypeWillChange]
    #[Override]
    public function gc(int $maxlifetime): int|false
    {
        $sql = "DELETE FROM session WHERE last_update < :lifetime";
        $sth = $this->pdo->prepare($sql);
        return $sth->execute([
            ":lifetime" => strtotime(date("Y-m-d H:i:s")) - $maxlifetime,
        ]);
    }

    /**
     * Update the current session id with a newly generated one with PDO
     * 
     * @param bool $delete_old_session
     *
     * @return bool
     */
    public static function regenerateId(bool $delete_old_session = false): bool
    {
        try {
            $old_id = session_id();
            $new_id = session_create_id();
            $pdo = Connection::getInstance();

            $sth = $pdo->prepare("SELECT * FROM session WHERE id = :id");
            $sth->execute([":id" => $old_id]);
            $name = $sth->fetch(PDO::FETCH_ASSOC);

            $sth = $pdo->prepare("UPDATE session SET id = :newid WHERE id = :id");
            $sth->execute([":newid" => $new_id, ":id" => $old_id]);

            if ($delete_old_session == true) {
                $sth = $pdo->prepare("DELETE FROM session WHERE id = :id");
                $sth->execute([":id" => $old_id]);
            }

            session_commit();
            session_id($new_id);
            setcookie($name['name'], $new_id, path: "/");
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}

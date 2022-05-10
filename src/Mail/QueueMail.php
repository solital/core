<?php

namespace Solital\Core\Mail;

use Katrina\Connection\Connection;
use Solital\Core\Mail\ConnectDatabase;

abstract class QueueMail
{
    /**
     * queue
     *
     * @param string $subject
     * @param string $body
     * @param string $alt_body
     * @return bool
     */
    public function queue(string $subject, string $body, string $alt_body = null): bool
    {
        ConnectDatabase::checkTableQueue();
        
        try {
            $sql = "INSERT INTO mail_queue (subject, body, from_email, from_name, recipient_email, 
            recipient_name, sent_at) VALUES (:subject, :body, :from_email, :from_name, :recipient_email, 
            :recipient_name, :sent_at)";

            $pdo = Connection::getInstance()->prepare($sql);
            $pdo->bindValue(":subject", $subject, \PDO::PARAM_STR);
            $pdo->bindValue(":body", $body, \PDO::PARAM_STR);
            $pdo->bindValue(":from_email", $this->sender, \PDO::PARAM_STR);
            $pdo->bindValue(":from_name", $this->sender_name, \PDO::PARAM_STR);
            $pdo->bindValue(":recipient_email", $this->recipient, \PDO::PARAM_STR);
            $pdo->bindValue(":recipient_name", $this->recipient_name, \PDO::PARAM_STR);
            $pdo->bindValue(":sent_at", null);
            $pdo->execute();

            return true;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * sendQueue
     *
     * @param int $per_second
     */
    public function sendQueue(int $per_second = 5)
    {
        ConnectDatabase::checkTableQueue();
        
        $mailer = new Mailer();
        $res = ConnectDatabase::select()->where("sent_at IS NULL")->get();

        foreach ($res as $send) {
            $mailer->add(
                $send->from_email,
                $send->from_name,
                $send->recipient_email,
                $send->recipient_name
            );

            if ($mailer->send($send->subject, $send->body)) {
                usleep(1000000 / $per_second);

                $this->update((int)$send->id_mail);
            }

            if ($mailer->error()) {
                echo $mailer->error();
                die;
            }
        }

        return true;
    }

    /**
     * @param int $id_mail
     * 
     * @return bool
     */
    private function update(int $id_mail): bool
    {
        try {
            $sql = "UPDATE mail_queue SET sent_at = NOW() WHERE id_mail = :id_mail";

            $pdo = Connection::getInstance()->prepare($sql);
            $pdo->bindValue(":id_mail", $id_mail, \PDO::PARAM_INT);
            $pdo->execute();

            return true;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}

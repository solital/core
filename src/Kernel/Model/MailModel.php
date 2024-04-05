<?php

namespace Solital\Core\Kernel\Model;

use Katrina\Katrina;

class MailModel extends Katrina
{
    /**
     * @var string|null
     */
    protected ?string $table = "mail_queue";

    /**
     * @var string|null
     */
    protected ?string $id = "id_mail";

    /**
     * @var bool
     */
    protected bool $timestamp = false;

    /**
     * @return void
     */
    public static function checkTableQueue(): void
    {
        $table = self::createTable('mail_queue');

        if (DB_CONFIG['DRIVE'] == "mysql") {
            $table->int('id_mail')->primary()->increment();
        }

        if (DB_CONFIG['DRIVE'] == "pgsql") {
            $table->serial('id_mail')->primary();
        }
        
        $table->varchar('subject', 200)->notNull();
        $table->varchar('body', 255)->notNull();
        $table->varchar('from_email', 100)->notNull();
        $table->varchar('from_name', 100)->notNull();
        $table->varchar('recipient_email', 100)->notNull();
        $table->varchar('recipient_name', 100)->notNull();
        $table->varchar('sent_at', 255);
        $table->createdUpdatedAt();
        $table->closeTable();
    }

    /**
     * @return void
     */
    public static function checkTableQueueBkp(): void
    {
        if (DB_CONFIG['DRIVE'] == "mysql") {
            self::createTable('mail_queue')
                ->int('id_mail')->primary()->increment()
                ->varchar('subject', 200)->notNull()
                ->varchar('body', 255)->notNull()
                ->varchar('from_email', 100)->notNull()
                ->varchar('from_name', 100)->notNull()
                ->varchar('recipient_email', 100)->notNull()
                ->varchar('recipient_name', 100)->notNull()
                ->varchar('sent_at', 255)
                ->createdUpdatedAt()
                ->closeTable();
        } elseif (DB_CONFIG['DRIVE'] == "pgsql") {
            self::createTable('mail_queue')
                ->serial('id_mail')->primary()
                ->varchar('subject', '200')->notNull()
                ->varchar('body', '255')->notNull()
                ->varchar('from_email', '100')->notNull()
                ->varchar('from_name', '100')->notNull()
                ->varchar('recipient_email', '100')->notNull()
                ->varchar('recipient_name', '100')->notNull()
                ->varchar('sent_at', '255')
                ->createdUpdatedAt()
                ->closeTable();
        }
    }
}

<?php

namespace Solital\Core\Mail;

use Katrina\Katrina;

class ConnectDatabase extends Katrina
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
        if (DB_CONFIG['DRIVE'] == "mysql") {
            self::createTable('mail_queue')
                ->int('id_mail')->primary()->increment()
                ->varchar('subject', '200')->notNull()
                ->varchar('body', '255')->notNull()
                ->varchar('from_email', '100')->notNull()
                ->varchar('from_name', '100')->notNull()
                ->varchar('recipient_email', '100')->notNull()
                ->varchar('recipient_name', '100')->notNull()
                ->varchar('sent_at', '255')
                ->createdUpdateAt()
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
                ->createdUpdateAt()
                ->closeTable();
        }
    }
}

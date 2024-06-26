<?php

namespace Solital\Core\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\MailHandler;
use Monolog\Level;
use PHPMailer\PHPMailer\PHPMailer;
use Solital\Core\Resource\Str\Str;

class MailerHandler extends MailHandler
{
    /**
     * A PHPMailer instance.
     *
     * @var PHPMailer $mailer
     */
    protected PHPMailer $mailer;

    /**
     * Constructs the PHPMailer handler.
     *
     * @param PHPMailer        $mailer A PHPMailer instance to use.
     * @param int|string|Level $level  The minimum logging level at which this handler will be triggered.
     * @param bool             $bubble Whether the messages that are handled can bubble up the stack or not.
     */
    public function __construct(PHPMailer $mailer, int|string|Level $level = Level::Error, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->mailer = $mailer;
    }

    /**
     * Send a mail with the given content.
     *
     * @param string $content Formatted email body to be sent.
     * @param array  $records The array of log records that formed this content.
     *
     * @return void
     */
    #[\Override]
    protected function send(string $content, array $records): void
    {
        $mailer = $this->buildMessage($content, $records);
        $mailer->send();
    }

    /**
     * Builds a message to be sent.
     *
     * @param string $content Formatted email body to be sent.
     * @param array  $records The array of log records that formed this content.
     *
     * @return PHPMailer The built message.
     */
    protected function buildMessage(string $content, array $records): PHPMailer
    {
        // phpcs:disable Squiz.NamingConventions.ValidVariableName

        $mailer = clone $this->mailer;

        if (Str::contains($content, '<')) {
            $mailer->ContentType = $mailer::CONTENT_TYPE_TEXT_HTML;
            $mailer->isHTML(true);
        }

        if ($records) {
            $subjectFormatter = new LineFormatter($mailer->Subject);
            $mailer->Subject = $subjectFormatter->format($this->getHighestRecord($records));
        }

        $mailer->Body = $content;

        return $mailer;

        // phpcs:enable
    }
}

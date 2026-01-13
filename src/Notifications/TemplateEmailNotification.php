<?php

namespace Athka\Saas\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplateEmailNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected string $emailSubject;
    protected string $emailBody;
    protected ?string $companyName;
    protected ?string $recipientName;
    protected ?string $recipientEmail;

    public function __construct(string $subject, string $body, ?string $companyName = null, ?string $recipientName = null, ?string $recipientEmail = null)
    {
        $this->emailSubject = $subject;
        $this->emailBody = $body;
        $this->companyName = $companyName;
        $this->recipientName = $recipientName;
        $this->recipientEmail = $recipientEmail;
    }

    public function build(): self
    {
        return $this->subject($this->emailSubject)
            ->view('saas::mail.template', [
                'body' => $this->emailBody,
                'subject' => $this->emailSubject,
                'companyName' => $this->companyName,
                'recipientName' => $this->recipientName,
                'recipientEmail' => $this->recipientEmail,
            ])
            ->from(config('mail.from.address'), config('mail.from.name'));
    }
}

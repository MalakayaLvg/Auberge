<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
    $this->mailer = $mailer;
    }

    public function sendBookingConfirmationEmail(string $to, string $bookingDetails): void
    {
        $email = (new Email())
        ->from('no-reply@auberge.com')
        ->to($to)
        ->subject('Confirmation de votre réservation')
        ->text('Merci pour votre réservation. Voici les détails : ' . $bookingDetails);

        $this->mailer->send($email);
    }

    public function sendReminderEmail(string $to, string $bookingDetails): void
    {
        $email = (new Email())
            ->from('no-reply@auberge.com')
            ->to($to)
            ->subject('Rappel de votre séjour prochain')
            ->text('Votre séjour approche. Voici les détails : ' . $bookingDetails);

        $this->mailer->send($email);
    }

    public function sendStatusUpdateEmail(string $to, string $statusUpdate): void
    {
        $email = (new Email())
            ->from('no-reply@auberge.com')
            ->to($to)
            ->subject('Mise à jour de votre réservation')
            ->text('Mise à jour : ' . $statusUpdate);

        $this->mailer->send($email);
    }
}
<?php
namespace App\Service;

use App\Entity\Contact;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class MailerService
{
    public const string CONTACT_ADDRESS = "administrateur@clyna.fr";

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $param
    ) {}

    public function sendContactMail(Contact $contact): bool
    {
        $adminEmail = $this->param->get('app.admin.email');
        $email = $this->newMailFromContact()
        ->to($adminEmail)
        ->subject('Vitrine|Contact')
        ->htmlTemplate('emails/contact/template.html.twig')
        ->context([
            'client_email' => $contact->getEmail(),
            'subject' => $contact->getSubject(),
            'message' => $contact->getContent()
        ]);

        try {
            $this->mailer->send($email);
        } catch (Exception $e) { 
            return false;
        }
        return true;
    }
    
    private function newMailFromContact(): TemplatedEmail
    {
        return (new TemplatedEmail())->from(new Address(self::CONTACT_ADDRESS, 'Clyna'));
    }
}
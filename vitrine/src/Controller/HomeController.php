<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\CustomChart;
use App\Form\ContactType;
use App\Form\CustomchartType;
use App\Service\MailerService;
use App\Service\RecaptchaService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly RecaptchaService $recaptchaService,
        private readonly MailerService $mailerService,
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * @todo import tailwind
     * @todo import Recaptcha
     * @todo Use OVH MAILER DNS
     * 
     * @todo pages 
     *      accueil
     *      formulaire
     */
    #[Route(path: '/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', []);
    }

    #[Route(path: '/vitrine2', name: 'app_vitrine2')]
    public function vitrine2(): Response
    {
        return $this->render('vitrine2/index.html.twig', []);
    }

    #[Route(path: '/contact', name: 'app_contact')]
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactType::class, new Contact)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->recaptchaService->verify($request)) {
                $this->addFlash('danger', 'recaptcha invald');
                return $this->redirectToRoute('app_contact');
            }
            /** @var Contact $contact */
            $contact = $form->getData();
            if ($this->mailerService->sendContactMail($contact)) {
                $this->addFlash('success', 'Demande envoyée!');
                return $this->redirectToRoute('app_home');
            }
            $this->addFlash('danger', 'Une erreur est survenu!');
            return $this->redirectToRoute('app_contact');
        }
        return $this->render('home/contact.html.twig', [
            'form' => $form
        ]);
    }
}

<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\CustomChart;
use Symfony\Component\Routing\Attribute\Route;

final class ChartegraphiqueController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }
    #[Route(path: '/chartegraphique', name: 'app_chartegraphique')]
    public function charteGraphique(Request $request): Response
    {
        $charts = $this->em->getRepository(CustomChart::class)->findAll();
        return $this->render('chartegraphique/charte_graphique.html.twig', [
            'charts' => $charts
        ]);
    }

    #[Route(path: '/charte_graphique/{id}', name: 'app_charte_graphique_show')]
    public function charteGraphiqueshow(Request $request, CustomChart $customChart): Response
    {

        return $this->render('chartegraphique/charte_graphique_show.html.twig', [
            'chart' => $customChart
        ]);
    }
}

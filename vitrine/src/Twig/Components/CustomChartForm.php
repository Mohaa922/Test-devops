<?php

namespace App\Twig\Components;

use App\Form\CustomchartType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;
use App\Entity\CustomChart;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\LiveAction;

#[AsLiveComponent]
final class CustomChartForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveCollectionTrait;

    public function __construct( private readonly EntityManagerInterface $em)
    {}
    public function instantiateForm(): FormInterface {
        return $this->createForm(CustomchartType::class, new CustomChart());
    }
    
    #[LiveAction]
    public function submit(): Response  
    {
        $this->submitForm();
        $customchart = $this->getForm()->getData();
        $this->em->persist($customchart);
        $this->em->flush();
        $this->addFlash('success', 'envoye');
        return $this->redirectToRoute("app_charte_graphique");
    }
}

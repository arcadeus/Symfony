<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\ServiceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/order', name: 'app_order')]
    public function add(Request $request, ServiceRepository $serviceRepository): Response
    {
        $new_order = new Order();
        $form = $this->createForm(
            OrderType::class,
            $new_order,
            ['services' => $serviceRepository->findAll()]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->doctrine->getManager();
            $em->persist($new_order);
            $em->flush();

            return $this->redirectToRoute('app_order');
        }

        return $this->render('order/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

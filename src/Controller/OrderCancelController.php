<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderCancelController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/order/error/{stripeSessionId}', name: 'order_cancel')]
    public function index($stripeSessionId): Response
    {
        // We get our order
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        // We check if the command does not exist and if the current user is different from the one who made the command
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // Envoyer un email pour à notre utilisateur pour lui indiquer l'échec de paiement

        return $this->render('order_cancel/index.html.twig',[
            'order' => $order
        ]);
    }
}

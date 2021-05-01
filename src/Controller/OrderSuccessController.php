<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/order/thanks/{stripeSessionId}', name: 'order_success')]
    public function index(Cart $cart, $stripeSessionId): Response
    {
        // We get our order
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        // We check if the command does not exist and if the current user is different from the one who made the command
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if (!$order->getIsPaid()) {
            // Empty the session "cart"
            $cart->remove();

            // Change the isPaid status of our order to 1
            $order->setIsPaid(1);
            $this->entityManager->flush();
        }
        // Envoyer un email à notre client pour lui confirmer sa commande

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}

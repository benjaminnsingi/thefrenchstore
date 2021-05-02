<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
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
        // Send an email to our customer to confirm his order
        $mail = new Mail();
        $content = "Bonjour" .$order->getUser()->getFirstname()."<br>Merci pour votre commande.<br><br/>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam cursus, ligula quis sagittis tempor, turpis justo pretium sem, nec maximus magna ex in nibh. Donec aliquet               sapien placerat dui sodales facilisis. Integer pretium porttitor nunc in feugiat. Integer vehicula lacus sed hendrerit tincidunt. Nulla venenatis pretium massa, nec                    feugiat metus accumsan nec. Sed in scelerisque metus. Sed vel nulla id magna molestie suscipit eu id ipsum. Proin gravida, erat quis faucibus tempor, urna est pretium                lectus, non tristique dolor nisi sit amet neque.";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Bienvenue sur La Boutique Française', $content);

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}

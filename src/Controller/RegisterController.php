<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {

        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'register')]
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if (!$search_email) {
                $passport = $encoder->encodePassword($user, $user->getPassword());

                $user->setPassword($passport);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content = "Bonjour" .$user->getFirstname()."<br>Bienvenue sur la première boutique dédiée au made in France.<br><br/>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam cursus, ligula quis sagittis tempor, turpis justo pretium sem, nec maximus magna ex in nibh. Donec aliquet               sapien placerat dui sodales facilisis. Integer pretium porttitor nunc in feugiat. Integer vehicula lacus sed hendrerit tincidunt. Nulla venenatis pretium massa, nec                    feugiat metus accumsan nec. Sed in scelerisque metus. Sed vel nulla id magna molestie suscipit eu id ipsum. Proin gravida, erat quis faucibus tempor, urna est pretium                lectus, non tristique dolor nisi sit amet neque.";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur La Boutique Française', $content);

                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte.";
            } else {
                $notification = "L'email que vous avez renseigné existe déjà";
            }
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}

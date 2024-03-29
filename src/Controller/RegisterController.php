<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/register", name="register")
     */
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        // handle form request
        $form->handleRequest($request);

        $notificationType = "";
        // if form is valid
        if ($form->isSubmitted() && $form->isValid()) {

            // show content of form
            // dd($form->getData());

            $user = $form->getData();

            $search_email = $this->em->getRepository(User::class)->findOneByEmail($user->getEmail());


            if (!$search_email) {
                $notification = "Inscription réussie. Vous pouvez dès à présent vous connecter à votre compte.";
                $notificationType = "info";

                // hash user password
                $password = $hasher->hashPassword($user, $user->getPassword());
                // replace clear text password with hashed one before user saving
                $user->setPassword($password);
                
                $this->em->persist($user);
                $this->em->flush();
    
                $mail = new Mail();
                $content = "Bonjour ".$user->getFirstname()."<br/>Bienvenue au paradis des plaisirs. Lorem ipsum...";
                $mail->send($this->getParameter('mailjet_api_key'), $user->getEmail(), $user->getFirstname(), 'Bienvenue sur Le Monde du Chocolat', $content);
            // user already exists
            } else {
                $notification = "Cet email est déjà utilisé par un autre utilisateur";
                $notificationType = "danger";

            }
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
            'notificationType' => $notificationType
        ]);
    }
}

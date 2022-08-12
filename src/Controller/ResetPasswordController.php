<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/reset-password", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')) {
            $user = $this->em->getRepository(User::class)->findOneByEmail($request->get('email'));

            if ($user) {
                // Save password reset demand
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user);
                $resetPassword->setToken(uniqid());
                $resetPassword->setCreatedAt(new DateTimeImmutable());
                $this->em->persist($resetPassword);
                $this->em->flush();
            
                // Send email to user with token link
                $url = $this->generateUrl('update_password', [
                    'token' => $resetPassword->getToken()
                ]);

                $content = "Bonjour ".$user->getFirstname().",<br/>Vous avez demandé à réinitialiser votre mot de passe sur le site Le Monde du Chocolat<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>";

                $mail = new Mail();
                $mail->send($this->getParameter('mailjet_api_key'), $user->getEmail(), $user->getFirstname().' '.$user->getLastname(), 'Réinitialiser votre mot de passe - Le monde du chocolat', $content);
            }
            
            $this->addFlash('notice', 'Un lien de réinitialisation de mot de passe a été envoyé à cette adresse');

        }

        return $this->render('reset_password/index.html.twig');
    }

    
    /**
     * @Route("/update-password/{token}", name="update_password")
     */
    public function update(Request $request, $token, UserPasswordHasherInterface $encoder)
    {
        $resetPassword = $this->em->getRepository(ResetPassword::class)->findOneByToken($token);
        
        if (!$resetPassword) {
            return $this->redirectToRoute('reset_password');
        }

        $now = new \DateTime();
        // if token is not valid anymore
        if ($now > $resetPassword->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash('notice', 'Votre demande de réinitialisation de mot de passe a expirée. Merci de la renouveler');
            return $this->redirectToRoute('reset_password');
        }

        // return view with password and confirm password
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();
            // hash passwords
            $user = $resetPassword->getUser();
            $password = $encoder->hashPassword($user, $newPassword);
            $user->setPassword($password);
            $this->em->flush();
            // TODO remove ResetPassword instance or add one use only

            // redirect user to connection page
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour.');
            return $this->redirectToRoute('login');

        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
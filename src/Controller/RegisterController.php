<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function index(Request $request): Response
    {

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        // handle form request
        $form->handleRequest($request);

        // if form is valid
        if ($form->isSubmitted() && $form->isValid()) {

            // show content of form
            // dd($form->getData());

            $user = $form->getData();
            
            $this->em->persist($user);
            $this->em->flush();
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

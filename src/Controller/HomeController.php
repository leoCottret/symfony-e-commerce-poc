<?php

namespace App\Controller;

use App\Classe\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $mail = new Mail();
        $mail->send($this->getParameter("mailjet_api_key"), "test15mpl@yopmail.com", "Le monde receiver", "Subject3", "TODO contenu mail");

        return $this->render('home/index.html.twig');
    }
}

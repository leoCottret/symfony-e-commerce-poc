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

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;        
    }

    /**
     * @Route("/order/success/{stripeSessionId}", name="order_validate")
     */
    public function index($stripeSessionId, Cart $cart): Response
    {
        $order = $this->em->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($order->getState() == 0) {
            // delete cart session
            $cart->remove();

            // set order as paid
            $order->setState(1);
            $this->em->flush();
            // send email to client to validate order
            $mail = new Mail();
            $user = $this->getUser();
            $content = "Bonjour ".$user->getFirstname()."<br/>Merci pour votre commande. Lorem ipsum...";
            $mail->send($this->getParameter('mailjet_api_key'), $user->getEmail(), $user->getFirstname(), 'Votre commande Le Monde du Chocolat est bien validée.', $content);
        }

        // display order informations to user
        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}

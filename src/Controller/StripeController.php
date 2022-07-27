<?php

namespace App\Controller;

use App\Classe\Cart;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    /**
     * @Route("/stripe/create-session", name="stripe_create_session")
     */
    public function index(Cart $cart): Response
    {
        $YOUR_DOMAIN = 'http://localhost:8000';

        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $products_for_stripe = [];

        // set products details for stripe
        foreach($cart->getFull() as $choice) {
                $products_for_stripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $choice['product']->getPrice(),
                        'product_data' => [
                            'name' => $choice['product']->getName(),
                            'images' => [$YOUR_DOMAIN."/uploads/".$choice['product']->getIllustration()],
                        ],
                    ],
                    'quantity' => $choice['quantity'],
                ];
        }

        $checkout_session = Session::create([
            'line_items' => [
                $products_for_stripe
            ],            
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/success.html',
            'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
        ]);
        
        return $this->redirect($checkout_session->url);
    }
}

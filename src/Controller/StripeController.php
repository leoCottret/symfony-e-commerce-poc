<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    /**
     * @Route("/stripe/create-session/{reference}", name="stripe_create_session")
     */
    public function index(EntityManagerInterface $em, string $reference): Response
    {
        $YOUR_DOMAIN = 'http://localhost:8000';

        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        // we set a unique reference in OrderController, and we get it here
        // interesting if we want to send a link that point to the order of the client, instead of iterating on the cart session variable
        // especially since the cart variable will be empty if the client was disconnected
        $order = $em->getRepository(Order::class)->findOneByReference($reference);
        $products_for_stripe = [];

        // if for some reason, the order doesn't exist anymore, redirect to order route to generate a new one
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('order');
        }

        // set products details for stripe
        foreach($order->getOrderDetails()->getValues() as $orderDetail) {
            $product = $em->getRepository(Product::class)->findOneByName($orderDetail->getProduct());
                $products_for_stripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $product->getPrice(),
                        'product_data' => [
                            'name' => $product->getName(),
                            'images' => [$YOUR_DOMAIN."/uploads/".$product->getIllustration()],
                        ],
                    ],
                    'quantity' => $orderDetail->getQuantity(),
                ];
        }

        // add carrier
        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice() * 100,
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN], // TODO
                ],
            ],
            'quantity' => 1,
        ];

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => [
                $products_for_stripe
            ],            
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/order/success/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/order/error/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $em->flush();
        
        return $this->redirect($checkout_session->url);
    }
}

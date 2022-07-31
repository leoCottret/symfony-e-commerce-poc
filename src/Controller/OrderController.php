<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;        
    }

    /**
     * @Route("/order", name="order")
     */
    public function index(Cart $cart, Request $request): Response
    {
        // if user has no address yet, then redirect him so that he can create one
        if (!$this->getUser()->getAddresses()->getValues()) {
            return $this->redirectToRoute('account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser() // otherwise we get the addresses of all users in the form
        ]);

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }

    /**
     * @Route("/order/summary", name="order_summary", methods={"POST"})
     */
    public function add(Cart $cart, Request $request): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser() // otherwise we get the addresses of all users in the form
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $carrier = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();
            
            $delivery_content = $delivery->getFirstname().' '.$delivery->getLastname();
            $delivery_content .= $delivery->getCompany() ? '<br/>'.$delivery->getCompany() : '';
            $delivery_content .= '<br/>'.$delivery->getAddress();
            $delivery_content .= '<br/>'.$delivery->getPostal().' '.$delivery->getCity();
            $delivery_content .= '<br/>'.$delivery->getCountry();

            // save basket (Order)
            $order = (new Order())
                ->setUser($this->getUser())
                ->setCreatedAt(new DateTimeImmutable())
                ->setCarrierName($carrier->getName())
                ->setCarrierPrice($carrier->getPrice())
                ->setDelivery($delivery_content)
                ->setIsPaid(false)
                ->setReference((new DateTimeImmutable())->format('dmY').'-'.uniqid());

            $this->em->persist($order);

            // save products (OrderDetails)
            foreach($cart->getFull() as $choice) {
                $orderDetails = (new OrderDetails())
                    ->setMyOrder($order)
                    ->setProduct($choice['product']->getName())
                    ->setQuantity($choice['quantity'])
                    ->setPrice($choice['product']->getPrice())
                    ->setTotal($choice['product']->getPrice() * $choice['quantity']);
                $this->em->persist($orderDetails);
            }

            $this->em->flush();

            return $this->render('order/add.html.twig', [
                'cart' => $cart->getFull(),
                'carrier' => $carrier,
                'delivery' => $delivery_content,
                'reference' => $order->getReference()
            ]);
        }

        return $this->redirectToRoute('cart');
    }
}

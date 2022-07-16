<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/cart", name="cart")
     */
    public function index(Cart $cart): Response
    {
        $cartComplete = [];

        foreach($cart->get() as $id => $quantity) {
            $cartComplete[] = [
                'product' => $this->em->getRepository(Product::class)->findOneById($id),
                'quantity' => $quantity
            ];
        }



        return $this->render('cart/index.html.twig', [
            'cart' => $cartComplete
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="add_to_cart")
     */
    public function add(Cart $cart, $id): Response
    {
        $cart->add($id);

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/delete", name="delete_cart")
     */
    public function delete(Cart $cart): Response
    {
        $cart->delete();

        return $this->redirectToRoute('products');
    }
}

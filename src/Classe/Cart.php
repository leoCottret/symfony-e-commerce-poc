<?php

namespace App\Classe;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class Cart
{
    private $em;
    private $session;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function get()
    {
        return $this->session->get('cart');
    }

    public function add($id)
    {
        $cart = $this->session->get('cart');

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        $this->session->set('cart', $cart);
    }

    public function decrease($id)
    {
        $cart = $this->session->get('cart');

        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }
        
        $this->session->set('cart', $cart);
    }

    public function delete($id)
    {
        $cart = $this->session->get('cart');

        unset($cart[$id]);
        
        $this->session->set('cart', $cart);
    }

    public function remove()
    {
        return $this->session->remove('cart');
    }

    public function getFull() {
        $cartComplete = [];

        if ($this->get()) {
            foreach($this->get() as $id => $quantity) {
                $product = $this->em->getRepository(Product::class)->findOneById($id);

                // if product doesn't exist (eg if it has been deleted since then, or someone tried to add an unexisting product), remove it from cart
                if (!$product) {
                    $this->delete($id);
                    continue;
                }

                $cartComplete[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartComplete;
    }
}
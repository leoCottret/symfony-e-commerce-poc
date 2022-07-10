<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
private $em;

public function __construct(EntityManagerInterface $em)
{
    $this->em = $em;
}


    /**
     * @Route("/products", name="app_product")
     */
    public function index(): Response
    {
        $products = $this->em->getRepository(Product::class)->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }
}

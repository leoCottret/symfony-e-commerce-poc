<?php

namespace App\Controller\Admin;

use App\Entity\Carrier;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{

    private $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(OrderCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Le monde du chocolat');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Users', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Orders', 'fa fa-shopping-cart', Order::class);
        yield MenuItem::linkToCrud('Categories', 'fa fa-list', Category::class);
        yield MenuItem::linkToCrud('Products', 'fa fa-tag', Product::class);
        yield MenuItem::linkToCrud('Carriers', 'fa fa-truck', Carrier::class);
    }
}

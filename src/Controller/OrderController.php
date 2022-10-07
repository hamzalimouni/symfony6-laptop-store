<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Produit;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use LDAP\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class OrderController extends AbstractController
{
    private $orderRepositoy, $entityManager;
    public function __construct(OrderRepository $orderRepository, ManagerRegistry $doctrine)
    {
        $this->orderRepositoy = $orderRepository;
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/orders', name: 'order_list')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(): Response
    {
        $orders = $this->orderRepositoy->findAll();
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/update/orders/{order}/{status}', name: 'update_order_status')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateOrderStatus(Order $order, $status): Response
    {
        $order->setStatus($status);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'Your order status was updated'
        );
        return $this->redirectToRoute('order_list');
    }

    #[Route('/delete/orders/{order}', name: 'delete_order')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteOrder(Order $order): Response
    {
        $this->entityManager->remove($order);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'Your order was deleted'
        );
        return $this->redirectToRoute('order_list');
    }

    #[Route('/delete/orders/user/{order}', name: 'delete_user_order')]
    public function deleteUserOrder(Order $order): Response
    {
        $this->entityManager->remove($order);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'Your order was deleted'
        );
        return $this->redirectToRoute('user_order_list');
    }

    #[Route('/user/orders', name: 'user_order_list')]
    public function userOrder(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('order/userOrder.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/store/order/{product}', name: 'order_store')]
    public function orderStore(Produit $product): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $orderExist = $this->orderRepositoy->findOneBy([
            'user' => $this->getUser(),
            'pname' => $product->getName()
        ]);

        if ($orderExist) {
            $this->addFlash(
                'warning',
                'Your have already ordred this product !'
            );
            return $this->redirectToRoute('user_order_list');
        }

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setPname($product->getName());
        $order->setPrice($product->getPrice());
        $order->setStatus('Processing...');
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            'Your order was saved'
        );
        return $this->redirectToRoute('user_order_list');
    }
}

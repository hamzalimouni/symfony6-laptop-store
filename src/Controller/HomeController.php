<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Produit;
use App\Repository\CategoryRepository;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $productRepository, $categoryRepository, $entityManager;
    public function __construct(ProduitRepository $produitRepository, CategoryRepository $categoryRepository, ManagerRegistry $doctrine)
    {
        $this->productRepository = $produitRepository;
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $doctrine->getManager();
    }
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $product = $this->productRepository->findAll();
        $category = $this->categoryRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $product,
            'categories' => $category
        ]);
    }

    #[Route('/product/{category}', name: 'product_by_category')]
    public function categoryProduct(Category $category): Response
    {
        $categories = $this->categoryRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $category->getProduits(),
            'categories' => $categories
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CategoryController extends AbstractController
{
    private $categoryRepository, $entityManager;
    public function __construct(CategoryRepository $categoryRepository, ManagerRegistry $doctrine)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/category', name: 'category_list')]
    /**
     * @IsGranted("ROLE_ADMIN", statusCode=404, message="Page not found")
     */
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/add', name: 'category_create')]
    /**
     * @IsGranted("ROLE_ADMIN", statusCode=404, message="Page not found")
     */
    public function createCategory(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'New category was saved'
            );
            return $this->redirectToRoute('category_list');
        }
        return $this->renderForm('category/create.html.twig', [
            'form' => $form
        ]);
    }
}

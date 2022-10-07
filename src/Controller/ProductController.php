<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProductType;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ProductController extends AbstractController
{
    private $productRepository, $entityManager;

    public function __construct(ProduitRepository $produitRepository, ManagerRegistry $doctrine)
    {
        $this->productRepository = $produitRepository;
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/product', name: 'product_list')]
    /**
     * @IsGranted("ROLE_ADMIN", statusCode=404, message="Page Not Found")
     */
    public function index(): Response
    {
        $product = $this->productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $product,
        ]);
    }

    #[Route('/store/product', name: 'product_store')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function store(Request $request): Response
    {
        $product = new Produit();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $image = $request->files->get('product')['image'];
                $image_name = time() . '_' . $image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $product->setImage($image_name);
            }
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Your Product was saved'
            );
            return $this->redirectToRoute('product_list');
        }

        return $this->renderForm('product/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/product/details/{id}', name: 'product_show')]
    public function show(/*$id*/Produit $product): Response
    {
        // $product = $this->productRepository->find($id);
        return $this->render('product/show.html.twig', [
            'products' => $product
        ]);
    }

    #[Route('/product/edit/{id}', name: 'product_edit')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function editProduct(Produit $product, Request $request)
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $image = $request->files->get('product')['image'];
                $image_name = time() . '_' . $image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $product->setImage($image_name);
            }
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Your product was updated'
            );
            return $this->redirectToRoute('product_list');
        }
        return $this->renderForm('product/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/product/delete/{id}', name: 'product_delete')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteProduct(Produit $product)
    {
        $filesSystem = new Filesystem();
        $imagePath = './uploads/' . $product->getImage();
        if ($filesSystem->exists($imagePath)) {
            $filesSystem->remove($imagePath);
        }
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'Your product was deleted'
        );
        return $this->redirectToRoute('product_list');
    }
}

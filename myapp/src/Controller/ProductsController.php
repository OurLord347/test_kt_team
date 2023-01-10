<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Entity\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class ProductsController extends AbstractController
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/products", name="app_products")
     */
    public function index(Request $request): Response
    {
        // создает объект задачи и инициализирует некоторые данные для этого примера
        $product = new Product();

        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('weight', NumberType::class)
            ->add('save', SubmitType::class, ['label' => 'Поиск'])
            ->getForm();

        $form->handleRequest($request);
        //С формами не работал оказалсоь тут не надо заморачиватся с валидацией
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
        }
        return $this->render('products/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/import", name="app_import")
     */
    public function import(Request $request): Response
    {
        $file = new File();
        $form = $this->createFormBuilder($file)
            ->add('attachment', FileType::class)
            ->add('save', SubmitType::class, ['label' => 'Отправить'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bytes = random_bytes(20);
            $someNewFilename = bin2hex($bytes).'.xml';
            $importFile = $form['attachment']->getData();
            $importFile->move('public', $someNewFilename);
            $file->setName($someNewFilename);
            $file->setStatus(1);
            $this->entityManager->persist($file);
            $this->entityManager->flush();
        }
        return $this->render('products/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}

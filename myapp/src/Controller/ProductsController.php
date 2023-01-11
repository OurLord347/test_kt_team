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
    )
    {
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
            ->add('name', TextType::class, array('label' => 'Название', 'required' => false))
            ->add('description', TextType::class, array('label' => 'Описание', 'required' => false))
            ->add('weight', NumberType::class, array('label' => 'Вес', 'required' => false))
            ->add('save', SubmitType::class, ['label' => 'Поиск'])
            ->getForm();

        $form->handleRequest($request);
        //С формами не работал оказалось тут не надо заморачиватся с валидацией
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $product Product */
            $product = $form->getData();

            //todo Да я знаю что это будет работать медленно
            $query = $this->entityManager->getRepository(Product::class)->createQueryBuilder('p');
            $query->where("p.description LIKE :description")
                ->setParameter('description', "%" . $product->getDescription() . "%");
            $query->andWhere("p.name LIKE :name")
                ->setParameter('name', "%" . $product->getName() . "%");
            //todo при стандартизации массы здесь можно будет легко избавится от полнотекстового поиска
            $query->andWhere("p.weight LIKE :weight")
                ->setParameter('weight', "%" . $product->getWeight() . "%");
            $products = $query->getQuery()->getResult();

        }
        return $this->render('products/search.html.twig', [
            'form' => $form->createView(),
            'products' => $products
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
        //Просто сохраняю файл так как импорт будет идти в фоновом режиме
        if ($form->isSubmitted() && $form->isValid()) {
            $bytes = random_bytes(20);
            $someNewFilename = bin2hex($bytes) . '.xml';
            $importFile = $form['attachment']->getData();
            $importFile->move('public', $someNewFilename);
            $file->setName($someNewFilename);
            $file->setStatus(1);
            $this->entityManager->persist($file);
            $this->entityManager->flush();
        }
        return $this->render('products/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}

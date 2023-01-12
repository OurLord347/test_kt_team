<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Entity\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Filter\ProductFilter;
use Knp\Component\Pager\PaginatorInterface;

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
    public function index(
        Request $request,
        PaginatorInterface $paginator
    ): Response
    {
        $repository = $this->entityManager
            ->getRepository(Product::class);

        $form = $this->get('form.factory')->create(ProductFilter::class);


        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));

            $filterBuilder = $repository->createQueryBuilder("p");
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $filterBuilder);

            $query = $filterBuilder->getQuery();
            $form = $this->get('form.factory')->create(ProductFilter::class);
            var_dump($filterBuilder->getDql());
        } else {
            $query = $repository->createQueryBuilder("p")
                ->getQuery();
        }

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );


        return $this->render('products/search.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/import", name="app_import")
     */
    public function import(Request $request): Response
    {
        //Господи боже если это ктото читает некогда не берите образ bitnami/symfony
        //Он отврититлен я уже везде пытался менять memory_limit но это не помогло судя по документации они сами не знают где его надо поменять чтоб заработало
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

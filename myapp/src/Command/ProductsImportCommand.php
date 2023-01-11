<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\File;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProductsImportCommand extends Command
{

    private $entityManager;
    private $projectDir;

    public function __construct(
        $projectDir,
        EntityManagerInterface $entityManager
    ) {
        $this->projectDir = $projectDir;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected static $defaultName = 'app:products-import';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = $this->entityManager->createQuery(
            'SELECT f
            FROM App\Entity\File f
            WHERE f.status = 1'
        );
        $files = $query->getResult();

        $query = $this->entityManager->createQuery(
            'SELECT c
            FROM App\Entity\Category c'
        );
        $categorys = $query->getResult();

        $serializer = new Serializer( [new ObjectNormalizer()], [new XmlEncoder()]);
        //todo рефакторинг но пока сдеалю просто то что требуется
        //Перебираю файлы для импорта
        foreach ($files as $file){
            /* @var $file File */
            $filePath = $this->projectDir.'/public/public/'.$file->getName();
            $rows = $serializer->decode(file_get_contents($filePath),'xml');
            //Сохраняю продукты
            $countPersist = 0;
            foreach ($rows['product'] as $row){
                print_r($row);
                /* @var $thisCategory Category */
                //Ищу котегории
                $thisCategory = null;
                foreach ($categorys as $category){
                    /* @var $category Category */
                    if($row['category'] == $category->getName()){
                        $thisCategory = $category;
                    }
                }
                if(empty($thisCategory)){
                    $newCategory = new Category();
                    $newCategory->setName($row['category']);
                    $this->entityManager->persist($newCategory);
                    $this->entityManager->flush();
                    $countPersist = 0;
                    $categorys[] = $newCategory;
                    $thisCategory = $newCategory;
                }
                //Сохраняю продукт
                $newProduct = new Product();
                $newProduct->setName($row['name']);//todo По идее продукты не должны повторятся и стоилобы сделать проверку но пока неясна бизнес логика просто импортирую как есть.
                $newProduct->setCategoryId($thisCategory);
                $newProduct->setWeight($row['weight']);//todo Бесполезные данные баз стандартизации и приведения к единному виду но пока пусть будет так
                $newProduct->setDescription($row['description']);
                $this->entityManager->persist($newProduct);
                //Чтоб снизить нагрузку на бд
                if($countPersist >= 50){
                    $this->entityManager->flush();
                    $countPersist = 0;
                }
                $countPersist++;
            }
            $this->entityManager->flush();
            $countPersist = 0;
        }

        return Command::SUCCESS;
    }
}
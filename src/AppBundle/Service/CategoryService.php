<?php

namespace AppBundle\Service;

use AppBundle\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class CategoryService
{
    private $em;
    private $requestStack;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function addCategory()
    {

        $request = $this->requestStack->getCurrentRequest();
        $name = $request->request->get("name", null);
        $description = $request->request->get("description", null);


        if (!is_null($name) && $name != '') {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($description);

            $this->em->persist($category);
            $this->em->flush();
            return $category;


        } else {
            $message = "An error has occurred trying to add new category - Error: You must to provide a category name";
            return $message;
        }
    }

    public function getAllCategories()
    {

        $categories = $this->em->getRepository("App:Category")->findAll();
        return $categories;
    }

    public function editCategory($id)
    {
        $request = $this->requestStack->getCurrentRequest();
        $name = $request->request->get("name", null);
        $description = $request->request->get("description", null);

        $category = $this->em->getRepository("App:Category")->find($id);

        if (!is_null($category)) {
            if (!is_null($name) && $name != '') {
                $category->setName($name);
            }

            if (!is_null($description)) {
                $category->setDescription($description);
            }

            $this->em->persist($category);
            $this->em->flush();

        }
        return $category;


    }

    public function deleteCategory($id)
    {
        $category = $this->em->getRepository("App:Category")->find($id);
        $categoryrelatedwithproducts = $this->em->getRepository("App:Product")->findByCategory($id);

        if (is_null($category)) {
            $message = "An error has occurred trying to remove the currrent category - Error: The category id does not exist";
            return $message;
        }


        if ($categoryrelatedwithproducts) {
            foreach ($categoryrelatedwithproducts as $product) {
                $product->setCategory(null);
                $this->em->persist($category);
            }
            $this->em->flush();
            $this->em->remove($category);
            $this->em->flush();
            $message = "The category is related to products. The related product now have null on category field";
        } else {
            $this->em->remove($category);
            $this->em->flush();
            $message = "The category was removed successfully!";
        }

        return $message;

    }
}
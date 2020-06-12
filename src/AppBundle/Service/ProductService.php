<?php

namespace AppBundle\Service;

use AppBundle\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductService
{
    private $em;
    private $requestStack;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function getAllProducts()
    {

        $products = $this->em->getRepository(Product::class)->findAll();

        return $products;
    }

    public function getFeaturedProducts()
    {
        $products = $this->em->getRepository(Product::class)->findByFeatured(1);

        return $products;

    }

    public function addProduct()
    {
        $request = $this->requestStack->getCurrentRequest();
        $name = $request->request->get("name", null);
        $category = $request->request->get("category", null);
        $price = $request->request->get("price", null);
        $currency = $request->request->get("currency", null);
        $featured = $request->request->get("featured", null);
        $category = $this->em->getRepository("App:Category")->findOneById($category);

        if (!is_null($name) && ($currency == Product::EUR || $currency == Product::USD)) {
            $product = new Product();
            $product->setName($name);
            $product->setCategory($category);
            $product->setPrice($price);
            $product->setCurrency($currency);
            $product->setFeatured($featured);
            $this->em->persist($product);
            $this->em->flush();

            return $product;

        } else {
            $product = null;
        }
    }
}

?>
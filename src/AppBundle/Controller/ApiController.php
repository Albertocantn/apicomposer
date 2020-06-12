<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Service\ProductService;
use AppBundle\Service\CategoryService;
use AppBundle\Service\ChangeCurrencyService;

/**
 * Class ApiController
 *
 * @Route("/api")
 */
class ApiController extends AbstractFOSRestController
{

    /**
     * @Rest\Get("/v1/products", name="products_list_all")
     */
    public function getAllProduct()
    {
        $productService = $this->getProductService();
        $serializer = $this->getSerializer();
        $products = [];

        try {
            $code = 200;
            $error = false;
            $products = $productService->getAllProducts();
        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Products - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $products : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Get("/v1/products/featured", name="products_list_featured")
     */
    public function getFeaturedProduct(Request $request)
    {

        try {

            $products = $this->getProductService()->getFeaturedProducts();
            $code = 200;
            $error = false;

            if ($request->query->has("currency")) {
                $currency = $request->query->get("currency");
                if ($currency == Product::EUR || $currency == Product::USD) {
                    $USDPriceFromEUR = $this->getChangeCurrencyService()->getUSDValueFromEUR();
                    $EURPriceFromUSD = $this->getChangeCurrencyService()->getEURValueFromUSD();

                    $currencyAndValues = [
                        'currency' => $currency,
                        'USDPriceFromEUR' => $USDPriceFromEUR,
                        'EURPriceFromUSD' => $EURPriceFromUSD
                    ];

                    $products = $this->getChangeCurrencyService()->changeCurrency($products, $currencyAndValues);
                } else {
                    $code = 500;
                    $error = true;
                    $message = "The selected currency is not available, select one between USD and EUR";
                }
                $data = $products;

            } else {
                $data = $products;
            }


        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Products - Error";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $data : $message,
        ];

        return new Response($this->getSerializer()->serialize($response, "json"));
    }


    /**
     * @Rest\Post("/v1/product", name="product_add")
     */

    public function addProduct()
    {

        $product = [];

        try {
            $code = 201;
            $error = false;
            $product = $this->getproductService()->addProduct();
            if (is_null($product)) {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to add new product - Verify your parameters (currency only acepted EUR or USD and you must provide a name)";
            }


        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to add new product - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 201 ? $product : $message,
        ];

        return new Response($this->getSerializer()->serialize($response, "json"));
    }


    /**
     * @Rest\Post("/v1/category", name="category_add")
     */

    public function addCategory()
    {

        $category = [];
        $message = "";

        try {
            $code = 201;
            $error = false;
            $category = $this->getcategoryService()->addCategory();
        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to add new category - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 201 ? $category : $message,
        ];

        return new Response($this->getSerializer()->serialize($response, "json"));
    }


    /**
     * @Rest\Get("/v1/categories", name="categories_list_all")
     */
    public function getAllCategories()
    {

        $categories = [];
        $message = "";

        try {
            $code = 200;
            $error = false;
            $categories = $this->getcategoryService()->getAllCategories();


        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Categories - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $categories : $message,
        ];

        return new Response($this->getSerializer()->serialize($response, "json"));
    }


    /**
     * @Rest\Put("/v1/category/{id}", name="category_edit")
     */

    public function editCategory($id)
    {

        $category = [];

        try {

            $category = $this->getcategoryService()->editCategory($id);
            if (!is_null($category)) {
                $code = 200;
                $error = false;
            } else {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to updating category - Error: The category id does not exist";
            }
        } catch
        (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to edit the current category - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $category : $message,
        ];

        return new Response($this->getSerializer()->serialize($response, "json"));
    }


    /**
     * @Rest\Delete("/v1/category/{id}", name="category_remove")
     */


    public function deleteCategory($id)
    {

        try {
            $code = 200;
            $error = false;
            $message = $this->getCategoryService()->deleteCategory($id);


        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to remove the current category - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $message,
        ];

        return new Response($this->getSerializer()->serialize($response, "json"));
    }

    private function getProductService()
    {
        $productService = $this->container->get('AppBundle\Service\ProductService');

        return $productService;
    }

    private function getCategoryService()
    {
        $categoryService = $this->container->get('AppBundle\Service\CategoryService');

        return $categoryService;
    }

    private function getSerializer()
    {
        $serializer = $this->container->get('jms_serializer');

        return $serializer;
    }

    private function getChangeCurrencyService()
    {
        $changeCurrencyService = $this->container->get('AppBundle\Service\ChangeCurrencyService');

        return $changeCurrencyService;
    }

}
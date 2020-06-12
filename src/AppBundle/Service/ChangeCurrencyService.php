<?php

namespace AppBundle\Service;

use AppBundle\Entity\Product;
use GuzzleHttp\Client;


class ChangeCurrencyService
{


    public function getEURValueFromUSD()
    {
        $client =  new Client();
        $response = $client->request('GET', 'https://api.exchangeratesapi.io/latest?base=USD&symbols=EUR');
        $contents = json_decode($response->getBody(), true);
        $EURPriceFromUSD = $contents['rates'][Product::EUR];
        return $EURPriceFromUSD;
    }


    public function getUSDValueFromEUR()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.exchangeratesapi.io/latest?base=EUR&symbols=USD');
        $contents = json_decode($response->getBody(), true);
        $USDPriceFromEUR = $contents['rates'][Product::USD];
        return $USDPriceFromEUR;
    }

    public function changeCurrency($products, $currencyAndValues)
    {
        foreach ($products as $product) {
            $productcurrency = $product->getCurrency();
            $productprice = $product->getPrice();

            if ($currencyAndValues['currency'] != $productcurrency) {

                if ($currencyAndValues['currency'] == Product::USD) {
                    $product->setPrice($productprice *= $currencyAndValues['USDPriceFromEUR']);
                } elseif ($currencyAndValues['currency'] == Product::EUR) {
                    $product->setPrice($productprice *= $currencyAndValues['EURPriceFromUSD']);
                }

            }


        }
        return $products;
    }
}

?>
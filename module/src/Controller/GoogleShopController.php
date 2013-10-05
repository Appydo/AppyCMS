<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Credentials {
    public static function get() {
        return array(
            "merchantId" => "9168880",
            // "email"      => "430971367315@developer.gserviceaccount.com",
            // "password"   => "notasecret",
            "email"      => "appydo@gmail.com",
            "password"   => "iodxyadgvozpegly",
        );
    }
}

class GoogleShopController extends AbstractActionController {
    
    public function indexAction() {
        
        require_once(ROOT_PATH . '/vendor/gshoppingcontent-php/GShoppingContent.php');

        $creds = Credentials::get();
        
        $client = new \GSC_Client($creds["merchantId"]);
        $client->login($creds["email"], $creds["password"]);

        $product = new \GSC_Product();
        $product->setSKU("dd192");
        $product->setTargetCountry('FR');
        $product->setContentLanguage('fr');
        $product->setProductLink("http://breizhadonf.com/");
        $product->setTitle("Dijji Digital Camera");
        $product->setPrice("99.99", "eur");
        $product->setAdult("false");
        $product->setCondition("new");
        $product->setBatchOperation("insert");
        $product->setAvailability("out of stock");
        $gpc = "Vêtements et accessoires &gt; Vêtements";
        // $product->setGoogleProductCategory($gpc);
        $shippingService = "Colissimo";
        $product->addShipping("FR", null, "6", "EUR", $shippingService);
        $product->addTax("FR", null, "19.6", "true");
        
        $batch = new \GSC_ProductList();
        $batch->addEntry($product);
        
        $feed = $client->batch($batch);
        $products = $feed->getProducts();

        foreach($products as $operation) {
            echo('Updated: ' . $operation->getTitle() . "\n");
            echo('Status: ' . $operation->getBatchStatus() . "\n");
            echo('Reason: ' . $operation->getBatchStatusReason() . "\n");
        }

        exit();
        
        return array(
            
        );

    }


}

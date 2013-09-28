<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AtosController extends AbstractActionController {

    public function indexAction() {
        
        // Check security user

        $baskets = $this->db->query('SELECT * FROM Basket WHERE b.hide=0 and b.user_id=:user ORDER BY id ASC')->execute(array('user' => $this->user->id));

        $basket    = $this->db->query('INSERT INTO BankOrder bo
            WHERE bo.description=:description and bo.hide=0 and bo.count=:count and bo.payment=0 and bo.user_id=:user and b.product_id=:id')
                ->execute(array(
                    'count' => count($baskets),
                    'user' => $this->user->id,
                    'description' => isset($_POST['shop_comment'])?$_POST['shop_comment']:''
                    ));

        $price = 0;

        foreach ($baskets as $basket) {
            $product = $basket['product'];
            $price   = $price + ($product['price'] * $basket['count']);
            /*
            $bo = new BasketOrder();
            $bo->setCount($basket->getCount());
            $bo->setProduct($product);
            $bo->setAuthor($user);
            $bo->setBankorder($order);
            $bo->setHide(false);
            $bo->setPayment(false);
            $em->persist($bo);
             */
            $basket    = $this->db->query('INSERT INTO BasketOrder bo
            WHERE bo.description=:description and bo.hide=0 and bo.count=:count and bo.payment=0 and bo.user_id=:user and b.product_id=:id')
                ->execute(array(
                    'user' => $this->user->id,
                    'description' => isset($_POST['shop_comment'])?$_POST['shop_comment']:'',
                    'count' => $basket['count'],
                    'product' => $basket['product_id'],
                    'bankorder_id' => $order,
                    ));
        }

        $order->setPrice($price);
        $em->persist($order);
        $em->flush();

        $price_amount = $price * 100;

        $parm = "merchant_id=044224119600017";
        $parm = "$parm merchant_country=fr";
        $parm = "$parm amount=" . $price_amount;
        $parm = "$parm currency_code=978";
        $parm = "$parm pathfile=/home/www/kentucky/bin/creditagricole/pathfile";
        $parm = "$parm normal_return_url=http://www.kentucky-sportswear.com/";
        $parm = "$parm cancel_return_url=http://www.kentucky-sportswear.com/";
        $parm = "$parm automatic_response_url=http://www.kentucky-sportswear.com/creditagricole/autoresponse";
        $parm = "$parm language=fr";
        $parm = "$parm payment_means=CB,2,VISA,2,MASTERCARD,2";
        $parm = "$parm customer_id=" . $user->id;
        $parm = "$parm customer_email=" . $user->email;
        $parm = "$parm customer_ip_address=" . $this->getIP();
        $parm = "$parm order_id=".$order->getId();

        $path_bin = "/home/www/kentucky/bin/creditagricole/request";
        $parm     = escapeshellcmd($parm);
        $result   = exec("$path_bin $parm");
        $tableau  = explode("!", "$result");
        $code     = $tableau[1];
        $error    = $tableau[2];
        $message  = $tableau[3];

        $msg_error = '';

        if (( $code == "" ) && ( $error == "" )) {
            $msg_error = "executable request non trouve $path_bin";
        }

        // Erreur, affiche le message d'erreur
        else if ($code != 0) {
            $msg_error = "message erreur : $error";
        }

        return array(
            'msg_error' => $msg_error,
            'error' => $error,
            'message' => $message
        );
    }

    public function adminFacturesAction() {
            $payments = $em->getRepository('AppydoShopBundle:BankOrder')->findBy(
                array('payment' => true), array('id' => 'ASC')
            );
            return array(
                'payments' => $payments
            );
    }

	/**
     * @Route("/admin/payments", name="creditagricole_devis")
     * @Template()
     */
	public function adminDevissAction() {
		$devis = $em->getRepository('AppydoShopBundle:BankOrder')->findBy(
                array(), array('id' => 'ASC')
        );
		return array(
			'devis' => $devis
		);
	}

    /**
     * @Route("/autoresponse", name="creditagricole_autoresponse")
     */
    public function autoresponseAction() {
        $em = $this->getDoctrine()->getEntityManager();
        
        // $message = "message={$HTTP_POST_VARS['DATA']}";
        $message = "message={$_POST['DATA']}";
        
        $data = new BankData();
        $data->setData($_POST['DATA']);
        $em->persist($data);
        $em->flush();
        
        $pathfile = "pathfile=/home/www/kentucky/bin/creditagricole/pathfile";
        $path_bin = "/home/www/kentucky/bin/creditagricole/response";
        $message = escapeshellcmd($message);
        $result = exec("$path_bin $pathfile $message");
        $tableau = explode("!", $result);

        $code  = $tableau[1];
        $error = $tableau[2];
        $merchant_id = $tableau[3];
        $merchant_country = $tableau[4];
        $amount = $tableau[5];
        $transaction_id = $tableau[6];
        $payment_means  = $tableau[7];
        $transmission_date = $tableau[8];
        $payment_time  = $tableau[9];
        $payment_date  = $tableau[10];
        $response_code = $tableau[11];
        $payment_certificate = $tableau[12];
        $authorisation_id = $tableau[13];
        $currency_code = $tableau[14];
        $card_number = $tableau[15];
        $cvv_flag = $tableau[16];
        $cvv_response_code  = $tableau[17];
        $bank_response_code = $tableau[18];
        $complementary_code = $tableau[19];
        $complementary_info = $tableau[20];
        $return_context = $tableau[21];
        $caddie = $tableau[22];
        $receipt_complement = $tableau[23];
        $merchant_language  = $tableau[24];
        $language = $tableau[25];
        $customer_id = $tableau[26];
        $order_id = $tableau[27];
        $customer_email = $tableau[28];
        $customer_ip_address = $tableau[29];
        $capture_day  = $tableau[30];
        $capture_mode = $tableau[31];
        $data = $tableau[32];
        $order_validity = $tableau[33];
        $transaction_condition = $tableau[34];
        $statement_reference   = $tableau[35];
        $card_validity = $tableau[36];
        $score_value = $tableau[37];
        $score_color = $tableau[38];
        $score_info  = $tableau[39];
        $score_threshold = $tableau[40];
        $score_profile   = $tableau[41];

        // $logfile="/home/repertoire/log/logfile.txt";
        $logfile = "/home/www/kentucky/log/autoresponse.txt";

        // Ouverture du fichier de log en append
        $fp = fopen($logfile, "a");

        //  analyse du code retour
        if (( $code == "" ) && ( $error == "" )) {
            fwrite($fp, "erreur appel response\n");
            fwrite($fp, "executable response non trouve $path_bin\n");
        } else if ($code != 0) {
            fwrite($fp, " API call error.\n");
            fwrite($fp, "Error message :  $error\n");
        } else {
            // OK, Sauvegarde des champs de la réponse
            fwrite($fp, "merchant_id : $merchant_id\n");
            fwrite($fp, "merchant_country : $merchant_country\n");
            fwrite($fp, "amount : $amount\n");
            fwrite($fp, "transaction_id: $transaction_id\n");
            fwrite($fp, "transmission_date: $transmission_date\n");
            fwrite($fp, "payment_means: $payment_means\n");
            fwrite($fp, "payment_time : $payment_time\n");
            fwrite($fp, "payment_date : $payment_date\n");
            fwrite($fp, "response_code : $response_code\n");
            fwrite($fp, "payment_certificate : $payment_certificate\n");
            fwrite($fp, "authorisation_id : $authorisation_id\n");
            fwrite($fp, "currency_code : $currency_code\n");
            fwrite($fp, "card_number : $card_number\n");
            fwrite($fp, "cvv_flag: $cvv_flag\n");
            fwrite($fp, "cvv_response_code: $cvv_response_code\n");
            fwrite($fp, "bank_response_code: $bank_response_code\n");
            fwrite($fp, "complementary_code: $complementary_code\n");
            fwrite($fp, "complementary_info: $complementary_info\n");
            fwrite($fp, "return_context: $return_context\n");
            fwrite($fp, "caddie : $caddie\n");
            fwrite($fp, "receipt_complement: $receipt_complement\n");
            fwrite($fp, "merchant_language: $merchant_language\n");
            fwrite($fp, "language: $language\n");
            fwrite($fp, "customer_id: $customer_id\n");
            fwrite($fp, "order_id: $order_id\n");
            fwrite($fp, "customer_email: $customer_email\n");
            fwrite($fp, "customer_ip_address: $customer_ip_address\n");
            fwrite($fp, "capture_day: $capture_day\n");
            fwrite($fp, "capture_mode: $capture_mode\n");
            fwrite($fp, "data: $data\n");
            fwrite($fp, "order_validity: $order_validity\n");
            fwrite($fp, "transaction_condition: $transaction_condition\n");
            fwrite($fp, "statement_reference: $statement_reference\n");
            fwrite($fp, "card_validity: $card_validity\n");
            fwrite($fp, "card_validity: $score_value\n");
            fwrite($fp, "card_validity: $score_color\n");
            fwrite($fp, "card_validity: $score_info\n");
            fwrite($fp, "card_validity: $score_threshold\n");
            fwrite($fp, "card_validity: $score_profile\n");
            fwrite($fp, "-------------------------------------------\n");
        }

        fclose($fp);
    }

    /**
     * @Route("/response", name="creditagricole_response")
     * @Template()
     */
    public function responseAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        if ($user == null or $user == "anon.") {
            return $this->redirect($this->generateUrl('_appydo_login'));
        }

        // $message = "message={$HTTP_POST_VARS['DATA']}";
        $message = "message={$_POST['DATA']}";
        $pathfile = "pathfile=/home/www/kentucky/bin/creditagricole/pathfile";
        $path_bin = "/home/www/kentucky/bin/creditagricole/response";
        $message = escapeshellcmd($message);
        $result = exec("$path_bin $pathfile $message");
        $tableau = explode("!", $result);
        
        $customer_id = $tableau[26];
        $order_id = $tableau[27];

        if (( $code == "" ) && ( $error == "" )) {
            $msg_error = "executable request non trouve $path_bin";
        } else if ($code != 0) {
            $msg_error = "message erreur : $error";
        } else {
            $bankorder = $em->getRepository('AppydoShopBundle:BankOrder')->findOneBy(
                array('id' => $order_id)
            );
            $bankorder->setPayment(true);
            $em->persist($order);
            
            $baskets = $em->getRepository('AppydoShopBundle:Basket')->findBy(
                array('author' => $user->getId(), 'bankorder' => $order_id), array('id' => 'ASC')
            );
            // Empty user basket
            foreach($baskets as $basket) {
                $em->remove($basket);
            }
            // Set payment is true for order
            $orders = $em->getRepository('AppydoShopBundle:BasketOrder')->findBy(
                array('author' => $user->getId()), array('id' => 'ASC')
            );
            foreach($orders as $order) {
                $order->setPayment(true);
                $em->persist($order);
            }
            $em->flush();
        }

        return array(
            'tableau' => $tableau,
            'msg_error' => $msg_error,
            'error' => $error,
            'message' => $message
        );
    }

    function getIP() {
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "UNKNOWN";
        return $ip;
    }
    
}

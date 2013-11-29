<?php
namespace Index\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class BasketController extends AbstractActionController {
    
    private $website = 'http://www.breizhadonf.com';
    
    private function getKey() {
        if (!isset($_COOKIE['appyshop_key'])) {
            $key = $this->generateSalt();
            setcookie('appyshop_key', $key, time()+(3600*365), '/');
        } else {
            $key = $_COOKIE['appyshop_key'];
        }
        return $key;
    }
    
    public function indexAction() {
        $this->layout('breizhadonf/twocolumn1');
        
        $id = $this->params('id');
        $request = $this->getRequest();
        
        // Get Key
        $key = $this->getKey();
        
        $sac_ids = '';
        if ($request->isPost() and $request->getPost('attributes')) {
            foreach($request->getPost('attributes') as $attribute) {
                $sac_ids .= $attribute .',';
            }
        }
        
        $sac_ids = substr($sac_ids, 0, -1);
        $attributes = $this->db->query('
            SELECT sac.sac_name, sa.sa_name, sac.sac_price
            FROM ShopAttributeChoice sac
            LEFT JOIN ShopAttribute sa USING(sa_id)
            WHERE sac.sac_id IN ( '.$sac_ids.' )
            ')->execute(array(
                // 'sac' => $sac_ids,
                ));

        foreach($attributes as $attribute) {
            $attributes_names[$attribute['sa_name']] = $attribute['sac_name'];
        }
        
        // insert attributes
        if (!empty($id)) {
            $this->db->query('
                INSERT INTO Basket (user_id, key, product_id, attributes, hide, count, created, updated)
                VALUES (:user_id, :key, :product_id, :attributes, :hide, :count, :created, :updated)')->execute(array(
                    'user_id'    => $this->user->id,
                    'key'        => $key,
                    'product_id' => $id,
                    'attributes' => json_encode($attributes_names),
                    'hide'       => 0,
                    'count'      => 1,
                    'created'    => time(),
                    'updated'    => time()
                    ));
        }
        
        // If an action is submit (POST)
        if ($request->isPost()) {
            if (isset($_POST['empty_basket'])) {
                $user   = $this->db->query('DELETE FROM Basket WHERE (user_id=:user or key=:key)')
                        ->execute(array('user'=>$this->user->id, 'key'=>$key));
            }
            if (isset($_POST['delete_basket'])) {
                $user   = $this->db->query('DELETE FROM Basket WHERE (user_id=:user or key=:key) and id=:id')
                        ->execute(array('user'=>$this->user->id, 'key'=>$key, 'id'=>$_POST['delete_basket']));
            }
            if (isset($_POST['basket_count'])) {
                foreach($_POST['basket_count'] as $q_id=>$quantity) {
                    // die($key);
                    $product = $this->db->query('UPDATE Basket SET count=:count WHERE id=:id')
                        ->execute(array('id'=>$q_id, 'count'=>$quantity))
                        ->current();
                }
            }
        }

        // If user is NOT connected => COOKIE
        if (!$this->user) {
            $query   = $this->db->query('SELECT b.*
                    FROM Basket b
                    WHERE b.key=:key and b.hide=0 ORDER BY b.id')
                    ->execute(array('key'=>$key));
        // If user is connected
        } else {
            $query  = $this->db->query('SELECT b.*
                    FROM Basket b
                    WHERE (b.user_id=:user_id or b.key=:key) and b.hide=0 ORDER BY b.id')
                    ->execute(array(
                        'user_id' => $this->user->id,
                        'key' => $key
                        ));
        }
        
        
        
        $weight = 0;
        $total  = 0;   
        $baskets = array();
        // get all items
        foreach($query as $key=>$basket) {
            $product = $this->db->query('SELECT * FROM product WHERE id=:id')
                    ->execute(array('id'=>$basket['product_id']))
                    ->current();
            $baskets[$key] = $basket;
            if (!empty($product['discount']))
                $baskets[$key]['price'] = $product['discount'];
            else
                $baskets[$key]['price'] = $product['price'];
            foreach(json_decode($basket['attributes']) as $attribute_name=>$attribute_value) {
                $sac = $this->db->query('
                    SELECT sac_price
                    FROM ShopAttribute
                    LEFT JOIN ShopAttributeChoice USING(sa_id)
                    WHERE sa_name=:attribute_name and sac_name=:attribute_value')
                    ->execute(array(
                        'attribute_name'=>$attribute_name,
                        'attribute_value'=>$attribute_value,
                        ))
                    ->current();
                $baskets[$key]['price'] += $sac['sac_price'];
            }
            $baskets[$key]['total'] = ($baskets[$key]['price'] * $baskets[$key]['count']);
            $baskets[$key]['image_path'] = $product['image_path'];
            $baskets[$key]['image_name'] = $product['image_name'];
            $baskets[$key]['name']  = $product['name'];
            $weight                += $product['weight'];
            $total                 += $baskets[$key]['total'];
        }
        
        $deliveries = $this->db->query('
                        SELECT price, delivery_rule
                        FROM Delivery')
                    ->execute();
        foreach($deliveries as $delivery) {
            $delivery['delivery_rule'] = str_replace('price','$total',$delivery['delivery_rule']);
            $delivery['delivery_rule'] = str_replace('weight','$weight',$delivery['delivery_rule']);
            // die($total);
            if (eval('return '.$delivery['delivery_rule'].';')) {
               $delivery_price = $delivery['price'];
               break; 
            }
        }

        // $cookie = json_decode($_COOKIE['appyshop_product'], true);

        $layout = $this->layout();
        $layout->title = 'Mon panier';

        return new ViewModel(array(
                    'price'     => 0,
                    'transport' => $delivery_price,
                    'baskets'   => $baskets,
                    'total'     => $total,
                    'form'      => new \Index\Form\LoginForm()
                ));
    }
    
    public function validAction() {
        $this->layout('breizhadonf/twocolumn1');
        if ($this->user==null) die('Wrong user;');
        
        $key = (isset($_COOKIE['appyshop_key'])) ? $_COOKIE['appyshop_key'] : '' ;
        
        $query  = $this->db->query('SELECT b.*
                    FROM Basket b
                    WHERE (b.user_id=:user_id or b.key=:key) and b.hide=0 ORDER BY b.id')
                    ->execute(array(
                        'user_id' => $this->user->id,
                        'key' => $key
                        ));        
        
        $bankOrder = $this->db->query('INSERT INTO BankOrder (count, user_id, description, hide, payment, created, updated)
            VALUES (:count, :user, :description, :hide, :payment, :created, :updated)')->execute(array(
                'count'   => count($query),
                'user'    => $this->user->id,
                'description' => (isset($_POST['shop_comment'])) ? $_POST['shop_comment'] : '',
                'hide'    => false,
                'payment' => false,
                'created' => time(),
                'updated' => time(),
                ));

        $baskets      = array();
        $bankOrder_id = $this->db->getDriver()->getLastGeneratedValue();
        $price        = 0;
        $weight       = 0;
        $txt          = '<table><tr>';
        $txt         .= '<th>Articles</th>';
        $txt         .= '<th>Quantit&eacute;es</th>';
        $txt         .= '<th>Prix Unitaire</th>';
        $txt         .= '<th>Montant</th>';
        $txt         .= '</tr>';
        foreach ($query as $key=>$basket) {
            $product = $this->db->query('SELECT * FROM Product WHERE id=:id')
                    ->execute(array('id'=>$basket['product_id']))
                    ->current();

            $this->db->query('INSERT INTO BasketOrder (count, user_id, product_id, hide, payment, created, updated, attributes, bankorder_id)
                VALUES (:count, :user, :product_id, :hide, :payment, :created, :updated, :attributes, :bankorder_id)')->execute(array(
                    'count' => $basket['count'],
                    'user'  => $this->user->id,
                    'product_id'  => $product['id'],
                    'hide'    => 0,
                    'payment' => 0,
                    'created' => time(),
                    'updated' => time(),
                    'attributes'  => (isset($basket['attributes']) and !empty($basket['attributes'])) ? $basket['attributes'] : NULL,
                    'bankorder_id' => $bankOrder_id
                    ));

            
            
            $weight   = $weight + ($product['weight'] * $basket['count']);
            $baskets[$key] = $basket;
            // $baskets[$key]['price'] = $product['price'];
            
            $baskets[$key] = $basket;
            if (!empty($product['discount']))
                $baskets[$key]['price'] = $product['discount'];
            else
                $baskets[$key]['price'] = $product['price'];
            $price   = $price + ($baskets[$key]['price'] * $basket['count']);
            foreach(json_decode($basket['attributes']) as $attribute_name=>$attribute_value) {
                $sac = $this->db->query('SELECT sac_price FROM ShopAttribute LEFT JOIN ShopAttributeChoice USING(sa_id) WHERE sa_name=:attribute_name and sac_name=:attribute_value')
                    ->execute(array(
                        'attribute_name'=>$attribute_name,
                        'attribute_value'=>$attribute_value,
                        ))
                    ->current();
                $baskets[$key]['price'] += $sac['sac_price'];
            }

            $baskets[$key]['total'] += $baskets[$key]['price'] * $baskets[$key]['count'];
            $baskets[$key]['image_path'] = $product['image_path'];
            $baskets[$key]['image_name'] = $product['image_name'];
            $baskets[$key]['name']  = $product['name'];
            $weight                += $product['weight'];
            $total                 += $baskets[$key]['total'];
            $attributes = (isset($basket['attributes'])) ? "options :  {$basket['attributes']} -" : '';
            $txt .= "<tr><td><b>{$product['name']}</b><br />{$attributes}</td><td>{$basket['count']}</td><td>{$product['price']}</td><td>{$baskets[$key]['total']}</td></tr>";
        }
        $txt .= '</table>';

        $deliveries = $this->db->query('
                        SELECT price, delivery_rule
                        FROM Delivery')
                    ->execute();
        foreach($deliveries as $delivery) {
            $delivery['delivery_rule'] = str_replace('price','$total',$delivery['delivery_rule']);
            $delivery['delivery_rule'] = str_replace('weight','$weight',$delivery['delivery_rule']);
            // die($total);
            if (eval('return '.$delivery['delivery_rule'].';')) {
               $delivery_price = $delivery['price'];
               break; 
            }
        }
        
        $delivery_date = date("d/m/Y", mktime(0, 0, 0, date('m'), date('d')+15, date('Y')));
        
        $bankOrder = $this->db->query('UPDATE BankOrder SET price=:price WHERE id=:id')->execute(array(
            'price' => $price,
            'id'    => $bankOrder_id
            ));

        // $port = new \FraisPort();
        // $fpd  = $port->calcul($poids); 
        
        $price_amount = ($total + $delivery_price) * 100;
        
        $website = 'http://www.breizhadonf.com';

        $parm = "merchant_id=078913541500014";
        // $parm = "merchant_id=013044876511111";
        $parm = "$parm merchant_country=fr";
        $parm = "$parm amount=" . $price_amount;
        $parm = "$parm currency_code=978";
        $parm = "$parm pathfile=/home/www/breizhadonf/bin/creditagricole/pathfile";
        $parm = "$parm normal_return_url=$website";
        $parm = "$parm cancel_return_url=$website";
        $parm = "$parm automatic_response_url=".$website."/module/basket/autoresponse";
        $parm = "$parm language=fr";
        $parm = "$parm payment_means=CB,2,VISA,2,MASTERCARD,2";
        $parm = "$parm customer_id=" . $this->user->id;
        $parm = "$parm customer_email=" . $this->user->email;
        $parm = "$parm customer_ip_address=" . $this->getIP();
        $parm = "$parm order_id=" . $bankOrder_id;

        $path_bin = "/home/www/breizhadonf/bin/creditagricole/request";
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

        $price_mail = $price_amount / 100;

        if (isset($_POST['shop_comment']) and !empty($_POST['shop_comment'])) {
            $shop_comment = "Commentaire :
------
{$_POST['shop_comment']}
------
";
        } else {
            $shop_comment = '';
        }
        $date = date('d/m/Y H:i');
        $email = "
            <html>
            <head>
            <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
            </head>
            <body style='font-family: arial;'>
                <p>Un nouveau devis vient d'être créé sur $website</p>
                
                <table>
                    <tr>
                        <th>Utilisateur</th>
                        <td>{$this->user->firstname} {$this->user->username}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{$this->user->email}</td>
                    </tr>
                    <tr>
                        <th>Commande</th>
                        <td>{$bankOrder_id}</td>
                    </tr>
                    <tr>
                        <th>Prix</th>
                        <td>{$price_mail} &euro;</td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td>{$date}</td>
                    </tr>
                </table>

                <p>{$shop_comment}</p>
                    
<p>$txt</p>
                    
                <p>Liens : ".$website."/admin/devis/show/{$bankOrder_id}</p>
                    </body>
            </html>
            ";

        // mail('mystheme@free.fr', '['.$website.'] Nouveau devis ', $email);
        // mail('rbtcreation@free.fr', '['.$website.'] Nouveau devis ', $email);
        
        $this->mail('mystheme@free.fr', '['.$website.'] Nouveau devis ', $email);
        $this->mail('contact@breizhadonf.com', '['.$website.'] Nouveau devis ', $email);
        
        $project = $this->db->query('SELECT * FROM Project WHERE id=1')->execute(
                )->current();
        
        $entities = $this->db->query('SELECT * FROM Basket WHERE hide=:false and user_id=:user')->execute(
                array('user'=>$this->user->id)
                );

        $layout = $this->layout();
        $layout->title = 'Valider ma commande';
        
        return array(
            'baskets'   => $baskets,
            'delivery'  => $delivery_date,
            'msg_error' => $msg_error,
            'transport' => $delivery_price,
            'error'     => $error,
            'message'   => $message,
            'code'      => $code,
            'project'   => $project,
            'total'     => $total,
            'theme'     => ($project) ? $project['theme'] : 'default'
        );
    }

    public function responseAction() {
        // $message = "message={$HTTP_POST_VARS['DATA']}";
        $message  = "message={$_POST['DATA']}";
        $pathfile = "pathfile=/home/www/breizhadonf/bin/creditagricole/pathfile";
        $path_bin = "/home/www/breizhadonf/bin/creditagricole/response";
        $message  = escapeshellcmd($message);
        $result   = exec("$path_bin $pathfile $message");
        $tableau  = explode("!", $result);

        $code     = $tableau[1];
        $error    = $tableau[2];
        $message  = $tableau[3];
        $customer_id = $tableau[26];
        $order_id = $tableau[27];

        if (( $code == "" ) && ( $error == "" )) {
            $msg_error = "executable request non trouve $path_bin";
        } else if ($code != 0) {
            $msg_error = "message erreur : $error";
        } else {

            $bankorder = $this->db->query('UPDATE BankOrder SET payment=true WHERE id=:id')
                    ->execute(array(
                        'id' => $order_id
                        ))->current();

            $baskets = $this->db->query('SELECT b.*
                FROM Basket b
                WHERE author=:author and bankorder=:bankorder
                ORDER BY id DESC')
                    ->execute(array(
                        'author' => $this->user->id,
                        'bankorder' => $order_id
                        ));

            // Empty user basket
            foreach ($baskets as $basket) {
                $this->db
                    ->query('DELETE FROM Basket WHERE id=:id')
                    ->execute(array(
                        'id' => $basket['id']
                        ));
            }
            // Set payment is true for order
            $orders  = $this->db->query('SELECT bo.*
                FROM BasketOrder bo
                WHERE bo.author=:author
                ORDER BY id ASC')
                    ->execute(array(
                        'author' => $this->user->id
                        ));
            foreach ($orders as $order) {
                $basketorder  = $this->db->query('UPDATE BasketOrder SET payment=1 WHERE id=:id')
                    ->execute(array(
                        'id' => $order['id']
                        ))->current();
            }

            $bankdata  = $this->db->query('INSERT INTO BasketData
                (price, code, bank_code, cvv_code, command, customer, data) VALUES
                (:price, :code, :bank_code, :cvv_code, :command, :customer, :data)')
                    ->execute(array(
                        'price'     => $tableau[5],
                        'code'      => $tableau[1],
                        'bank_code' => $tableau[18],
                        'cvv_code'  => $tableau[17],
                        'command'   => $tableau[27],
                        'customer'  => $tableau[26],
                        'data'      => json_encode($tableau)
                        ));

            $message = "
                Une nouvelle facture est arrivé sur {$this->website}
                
                Prix : {$tableau[5]}
                Code : {$tableau[1]}
                Client : {$this->user->username}
            ";
            mail('mystheme@free.fr', '['.$this->website.'] Facture ' . $order_id, $message);
            mail('contact@breizhadonf.com', '['.$this->website.'] Facture' . $order_id, $message);
            
            $message = "
                Votre commande à bien été finalisé sur {$this->website}
                
                Prix : {$tableau[5]}
                Code : {$tableau[1]}
            ";
            mail($customer_email, '['.$this->website.'] Commande finalisé', $message);
        }

        return array(
            'tableau'   => $tableau,
            'msg_error' => $msg_error,
            'error'     => $error,
            'message'   => $message
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
    
    function generateSalt($max = 15) {
        $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*?";
        $i = 0;
        $salt = '';
        $cpt = strlen($characterList);
        for ($i=0;$i < $max;$i++) {
            $salt .= $characterList{mt_rand(0, ($cpt - 1))};
        }
        return $salt;
    }

    public function autoresponseAction() {

        // $message = "message={$HTTP_POST_VARS['DATA']}";
        $message = "message={$_POST['DATA']}";

        $pathfile = "pathfile=/home/www/breizhadonf/bin/creditagricole/pathfile";
        $path_bin = "/home/www/breizhadonf/bin/creditagricole/response";
        $message  = escapeshellcmd($message);
        $result   = exec("$path_bin $pathfile $message");
        $tableau  = explode("!", $result);
        
        $bankdata  = $this->db->query('INSERT INTO BankData
            (price, code, bank_code, cvv_code, command, customer, data, created, updated) VALUES
            (:price, :code, :bank_code, :cvv_code, :command, :customer, :data, :created, :updated)')
                    ->execute(array(
                        'price'     => $tableau[5],
                        'code'      => $tableau[1],
                        'bank_code' => $tableau[18],
                        'cvv_code'  => $tableau[17],
                        'command'   => $tableau[27],
                        'customer'  => $tableau[26],
                        'data'      => json_encode($tableau),
                        'created'   => time(),
                        'updated'   => time(),
                        ));
        /*
            $data = new BankData();
            $data->price = $tableau[5];
            $data->code = $tableau[1];
            $data->bank_code = $tableau[18];
            $data->cvv_code = $tableau[17];
            $data->command = $tableau[27];
            $data->customer = $tableau[26];
            $data->setData(json_encode($tableau));
            $em->persist($data);
            $em->flush();
         * 
         */

        $code = $tableau[1];
        $error = $tableau[2];
        $merchant_id = $tableau[3];
        $merchant_country = $tableau[4];
        $amount = $tableau[5];
        $transaction_id = $tableau[6];
        $payment_means = $tableau[7];
        $transmission_date = $tableau[8];
        $payment_time = $tableau[9];
        $payment_date = $tableau[10];
        $response_code = $tableau[11];
        $payment_certificate = $tableau[12];
        $authorisation_id = $tableau[13];
        $currency_code = $tableau[14];
        $card_number = $tableau[15];
        $cvv_flag = $tableau[16];
        $cvv_response_code = $tableau[17];
        $bank_response_code = $tableau[18];
        $complementary_code = $tableau[19];
        $complementary_info = $tableau[20];
        $return_context = $tableau[21];
        $caddie = $tableau[22];
        $receipt_complement = $tableau[23];
        $merchant_language = $tableau[24];
        $language = $tableau[25];
        $customer_id = $tableau[26];
        $order_id = $tableau[27];
        $customer_email = $tableau[28];
        $customer_ip_address = $tableau[29];
        $capture_day = $tableau[30];
        $capture_mode = $tableau[31];
        $data = $tableau[32];
        $order_validity = $tableau[33];
        $transaction_condition = $tableau[34];
        $statement_reference = $tableau[35];
        $card_validity = $tableau[36];
        $score_value = $tableau[37];
        $score_color = $tableau[38];
        $score_info = $tableau[39];
        $score_threshold = $tableau[40];
        $score_profile = $tableau[41];

        // $logfile="/home/repertoire/log/logfile.txt";
        $logfile = "/home/www/breizhadonf/log/autoresponse.txt";

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

            // Set the order : payment true
            $bankorder = $this->db
                    ->query('UPDATE BankOrder SET payment=1 WHERE id=:id')
                    ->execute(array(
                        'id' => $order_id
                        ))->current();

            // Empty user basket
            $this->db
                ->query('DELETE FROM Basket WHERE user_id=:author')
                ->execute(array(
                    'author' => $customer_id
                    ));

            $baskets  = $this->db->query('SELECT bo.*, p.name as product_name, p.price as product_price, p.discount, p.image_path as image_path, p.image_name as image_name
                FROM BasketOrder bo
                LEFT JOIN Product p ON bo.product_id=p.id
                WHERE bo.user_id=:author and bo.bankorder_id=:order
                ORDER BY bo.id ASC')
                    ->execute(array(
                        'author' => $customer_id,
                        'order'  => $order_id
                        ));

            
            $txt = '<table><tr><th>Article</th><th>Quantité</th><th>Prix</th></tr>';
            $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
            foreach ($baskets as $basket) {
                $img_path     = $this->website . $renderer->basePath($basket['image_path'] . 'thumb/' . $basket['image_name']);
                $product_path = $this->website . $renderer->url('index', array('controller'=>'product','action'=>'show','id'=>$basket['product_id']));
                $attributes = (isset($basket['attributes'])) ? "{$basket['attributes']}" : '';
                if (!empty($product['discount']))
                    $product_price = $product['discount'];
                else
                    $product_price = $product['product_price'];
                $txt .= "<tr><td>
                <img style='border:solid 1px #dedede;border-radius:10px;margin-right: 10px;max-height: 6em;float: left;' src='".$img_path."' />
                <a href='{$product_path}'>{$basket['product_name']}</a><br />{$attributes}</td><td>{$basket['count']}</td><td>{$product_price} &euro;</td></tr>\n";
            }
            $txt .= '</table>';
            $price_mail = $tableau[5] / 100;
            $date = date('d/m/Y H:i');
            $message = "
                <html>
                <head>
                <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
                </head>
                <body style='font-family: arial;'>
                <p>Une nouvelle facture est arrivé sur {$this->website}</p>
                
                <p>{$this->website}/admin/bill/show/{$order_id}</p>

                <table>
                <tr>
                    <th>Prix</th>
                    <td>$price_mail &euro;</td>
                </tr>
                <tr>
                    <th>Code</th>
                    <td>{$tableau[1]}</td>
                </tr>
                <tr>
                    <th>Client</th>
                    <td>{$this->website}/admin/user/edit/{$customer_id}</td>
                </tr>
                <tr>
                    <th>Email client</th>
                    <td>{$customer_email}</td>
                </tr>
                <tr>
                    <th>IP</th>
                    <td>{$customer_ip_address}</td>
                </tr>
                <tr>
                    <th>SECURE</th>
                    <td>$transaction_condition</td>
                </tr>
                <tr>
                    <th>Bank reponse</th>
                    <td>$response_code</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{$date}</td>
                </tr>
                </table>

                $txt
                </body>
                </html>
            ";
            
            $this->mail('mystheme@free.fr', '['.$this->website.'] Facture ' . $order_id, $message, $message);
            $this->mail('rbtcreation@free.fr', '['.$this->website.'] Facture ' . $order_id, $message, $message);
            
            $message = "
                <html>
                <head>
                <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
                </head>
                <body style='font-family: arial;'>
                <p>Breizhadonf vous remercie pour votre confiance !</p>
                <p>Votre commande à bien été finalisé sur <a href='{$this->website}'>{$this->website}</a></p>

                $txt

                <p>Total : {$price_mail} &euro;</p>

                <p style='font-weight:bold;'>Besoin de conseils ?</p>
                <p><span style='color:rgb(255, 165, 0);'>02 51 12 98 61</span> ( 9h30 - 17h30 )</p>
                <p><a href='http://www.breizhadonf.com/module/contact'>Contact</a> ( 24/24, 7j/7 )</p>

                <p>Merci et à bientôt sur <a href='{$this->website}'>{$this->website}</a></p>
                </body>
                </html>
            ";

            $this->mail($customer_email, '['.$this->website.'] Commande finalisé ' . $order_id, $message);
        }

        fclose($fp);
        
        return array(
            'tableau'   => $tableau,
            'msg_error' => $msg_error,
            'error'     => $error,
            'message'   => $message
        );
    }
    
    public function mail($to, $subject, $html) {
        $text = new MimePart($text);
        $text->type = "text/plain";

        $html = new MimePart($html);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array($html, $text));

        $message = new Message();
        $message->setEncoding("UTF-8");
        $message->setBody($body);
        $message->setFrom('contact@breizhadonf.com', 'Breizhadonf.com');
        $message->addTo($to);
        $message->setSubject($subject);

        $transport = new \Zend\Mail\Transport\Sendmail();
        $transport->send($message);
    }
    
}
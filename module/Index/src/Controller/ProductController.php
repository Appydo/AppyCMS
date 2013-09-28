<?php
namespace Index\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProductController extends AbstractActionController {

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
    
    public function indexAction() {
        // $this->layout('breizhadonf/twocolumn1');
        $topic = $this->params('id');
        $stmt  = $this->db->createStatement('
            SELECT p.*, u.username as author, t.name as topic_name
            FROM Product p
            LEFT JOIN users u on p.user_id=u.id
            LEFT JOIN Topic t on p.topic_id=t.id
            WHERE p.project_id=:project and p.topic_id=:topic and p.hide=0 ORDER BY p.id DESC');

        // Trick for rewind
        $products = $stmt->execute(array('project' => $this->project['id'], 'topic' => $topic))->getResource()->fetchAll();

        /*
          $topics = $this->db->query('SELECT t.*, u.username as author
          FROM Topic t
          LEFT JOIN users u on t.user_id=u.id
          WHERE t.project_id=:project and t.topic_id is NULL and t.hide=0 ORDER BY t.id DESC'
          )->execute(array('project' => 1));
         */
        
        $this->basket();
        
        $layout = $this->layout();
        $layout->title = 'Boutique';

        return new ViewModel(array(
                    'image_path' => '/uploads/products/' . $this->project['id'] . '/',
                    'products' => $products,
                ));
    }
    
    public function categoryAction() {
        // $this->layout('breizhadonf/twocolumn1');
        $topic = $this->params('id');
        $stmt  = $this->db->createStatement('
                    SELECT p.*, u.username as author, t.name as topic_name
                    FROM Product p
                    LEFT JOIN users u on p.user_id=u.id
                    LEFT JOIN topic t on (t.topic_id=:topic or t.id=:topic)
                    LEFT JOIN Product2Topic pt on p.id=pt.product_id
                    WHERE p.project_id=:project and pt.topic_id=t.id and p.hide=0
                    ORDER BY pt.topic_id, p.id DESC');

        // Trick for rewind
        $products = $stmt->execute(array('project' => $this->project['id'], 'topic' => $topic))
                ->getResource()
                ->fetchAll();

        /*
          $topics = $this->db->query('SELECT t.*, u.username as author
          FROM Topic t
          LEFT JOIN users u on t.user_id=u.id
          WHERE t.project_id=:project and t.topic_id is NULL and t.hide=0 ORDER BY t.id DESC'
          )->execute(array('project' => 1));
         */
        if (!isset($_COOKIE['appyshop_key'])) {
            $key = $this->generateSalt();
            setcookie('appyshop_key', $key, time()+(3600*365), '/');
        } else {
            $key = $_COOKIE['appyshop_key'];
        }
        $this->basket($key);
        
        $layout = $this->layout();
        $layout->title = 'Boutique '.str_replace('_', ' ', $this->params('category'));

        return new ViewModel(array(
                    'image_path' => '/uploads/products/' . $this->project['id'] . '/',
                    'products' => $products,
                ));
    }
    
    public function shopAction() {
        // $this->layout('breizhadonf/twocolumn1');

        $stmt  = $this->db->createStatement('SELECT p.*, t.name as topic_name
                    FROM Product p
                    LEFT JOIN topic t on t.id=p.topic_id
                    WHERE p.project_id=:project and p.hide=0
                    ORDER BY t.name, p.id DESC');

        // Trick for rewind
        $products = $stmt->execute(array('project' => $this->project['id']))->getResource()->fetchAll();

        /*
          $topics = $this->db->query('SELECT t.*, u.username as author
          FROM Topic t
          LEFT JOIN users u on t.user_id=u.id
          WHERE t.project_id=:project and t.topic_id is NULL and t.hide=0 ORDER BY t.id DESC'
          )->execute(array('project' => 1));
         */
        if (!isset($_COOKIE['appyshop_key'])) {
            $key = $this->generateSalt();
            setcookie('appyshop_key', $key, time()+(3600*365), '/');
        } else {
            $key = $_COOKIE['appyshop_key'];
        }
        $this->basket($key);

        $layout = $this->layout();
        $layout->title = 'Boutique';
        
        return new ViewModel(array(
                    'image_path' => '/uploads/products/' . $this->project['id'] . '/',
                    'products' => $products,
                ));
    }
    
    public function showAction() {
        // $this->layout('breizhadonf/twocolumn1');
        
        $id = $this->params('id');
        
        if (!empty($id) and isset($_POST['order'])) {
            $request = $this->getRequest();
        
            if (!isset($_COOKIE['appyshop_key'])) {
                $key = $this->generateSalt();
                setcookie('appyshop_key', $key, time()+(3600*365), '/');
            } else {
                $key = $_COOKIE['appyshop_key'];
            }
            
            $sac_ids = '';
            foreach($_POST['attributes'] as $attribute) {
                $sac_ids .= $attribute .',';
            }
            $sac_ids = substr($sac_ids, 0, -1);
            $attributes = $this->db->query('
                SELECT sac.sac_name, sa.sa_name
                FROM ShopAttributeChoice sac
                LEFT JOIN ShopAttribute sa USING(sa_id)
                WHERE sac.sac_id IN ( '.$sac_ids.' )
                ')->execute(array(
                    // 'sac' => $sac_ids,
                    ));

            foreach($attributes as $attribute) {
                $attributes_names[$attribute['sa_name']] = $attribute['sac_name'];
            }
            $order =$this->db->query('INSERT INTO Basket (user_id, key, product_id, attributes, hide, count, created, updated)
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
        } else {
            $order = false;
        }
        
        $stmt   = $this->db->createStatement('
                    SELECT p.*, u.username as author, t.id as topic_id, t.name as topic_name, parent.name as parent_name, parent.id as parent_id
                    FROM Product p
                    LEFT JOIN users u on p.user_id=u.id
                    LEFT JOIN topic t on t.id=p.topic_id
                    LEFT JOIN topic parent on parent.id=t.topic_id
                    WHERE p.project_id=:project and p.hide=0 and p.id=:id');

        $entity = $stmt->execute(array('project' => $this->project['id'], 'id' => $id))->current();

        $options = $this->db->query('
            SELECT pac.*, sac.sac_name, sa.sa_name, sac.sac_default
            FROM ProductAttribute pa
            LEFT JOIN ProductAttributeChoice pac USING(pa_id)
            LEFT JOIN ShopAttribute sa USING(sa_id)
            LEFT JOIN ShopAttributeChoice sac USING(sac_id)
            WHERE pa.product_id=:id
            ')->execute(array('id' => $id));

        $query  = $this->db->query('
            SELECT p.*, u.username as author, t.name as topic_name
            FROM Product p
            LEFT JOIN topic t on t.id=p.topic_id
            LEFT JOIN users u on p.user_id=u.id
            WHERE p.id!=:id and p.project_id=:project and p.topic_id=:topic and p.hide=0
            ORDER BY p.id DESC');

        // Trick for rewind
        $products = $query->execute(array('id' => $id, 'project' => $this->project['id'], 'topic' => $entity['topic_id']));
        
        $this->basket($key);
        
        $layout = $this->layout();
        $layout->title     = $entity['name'];
        
        $files = $this->db
        ->query('
            SELECT *
            FROM ProductFile pf
            WHERE pf.product_id=:id and pf.productfile_position!=0
            ')
        ->execute(array('id'=>$id));

        return new ViewModel(array(
                    'entity' => $entity,
                    'products' => $products,
                    'options'  => $options,
                    'order' => $order,
                    'files'   => $files,
                    'image_path' => '/uploads/products/' . $this->project['id'] . '/'
                ));
    }

    public function topicAction() {
        $id = $this->params('id');
        if (empty($id)) {
            $name = $this->params('name');
            $topic = $this->db->query('SELECT t.*, u.username as author
                    FROM Topic t
                    LEFT JOIN users u on t.user_id=u.id
                    WHERE t.project_id=:project and t.name=:name'
                    )->execute(array('project' => 1, 'name' => $name))->current();
        } else {
            $topic = $this->db->query('SELECT t.*, u.username as author
                    FROM Topic t
                    LEFT JOIN users u on t.user_id=u.id
                    WHERE t.project_id=:project and t.id=:id'
                    )->execute(array('project' => 1, 'id' => $id))->current();
        }
        return new ViewModel(array(
                    'topic' => $topic,
                ));
    }
    
    public function basket($key) {
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
        
        $total  = 0;   
        $weight = 0;
        $baskets = array();
        foreach($query as $key=>$basket) {
            $product = $this->db->query('SELECT * FROM product WHERE id=:id')
                    ->execute(array('id'=>$basket['product_id']))
                    ->current();
            $baskets[$key] = $basket;
            $baskets[$key]['price'] = $product['price'];
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
            $weight += $product['weight'];
            $total                 += $baskets[$key]['total'];
        }
        
        $delivery = $this->db->query('SELECT min(price) FROM Delivery WHERE weight<=:weight')
                    ->execute(array('weight'=>$weight))
                    ->current();

        $layout = $this->layout();
        $layout->baskets   = $baskets;
        $layout->transport = $delivery['min(price)'];
        $layout->total     = $total;
    }

}
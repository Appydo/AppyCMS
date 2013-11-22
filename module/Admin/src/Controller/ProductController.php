<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Lib\SimpleImage as SimpleImage;
use Zend\Barcode\Barcode;

class ProductController extends AbstractActionController {

    private $thumb_width = 200;
	private $table      = 'Product';
    private $controller = 'Product';
    private $id         = 'id';
    private $module     = 'admin';

    public function indexAction() {
	
		if (isset($_GET['page']))
            $page = $_GET['page'];
        else
            $page  = 1;
        if (isset($_GET['move'])) {
            if ($_GET['move']=='next') {
                $page++;
            } elseif ($_GET['move']=='prev' and $page!=1) {
                $page--;
            }
        }
		if (empty($page)) $page = 0;
        $nb    = 20;
        $start = ($page * $nb) - $nb;
        if (isset($_GET['order']))
            $order = $_GET['order'];
        else
            $order = '';
        if(isset($_GET['sort']) and $_GET['sort']=='ASC')
            $sort  = 'ASC';
        else
            $sort  = 'DESC';

        
        if (!empty($order)) $order_string = 'ORDER BY '.$order.' '.$sort;
        else $order_string = 'ORDER BY p.hide ASC, p.id ASC';

		$where_string = 'WHERE u2.id=:id and t.project_id=u2.project_id';
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('query') != '') {
                $where = array();
                $query = $request->getPost('query');
				$where[] = 'p.name LIKE "%'.$query.'%"';
				// $where[] = 'p.description LIKE "%'.$query.'%"';
				if (is_numeric($query)) $where[] = 'id='.$query;
                $where_string = ' and ('.implode(' or ',$where).')';
                $stmt = $this->db->createStatement('SELECT * FROM '.$this->table);
            } elseif ($request->getPost('action_submit') == '1') {
                if ($request->getPost('action_select') == 'delete') {
                    // die(var_dump($request->getPost('action')));
                    foreach ($request->getPost('action') as $action) {
                        return $this->deleteAction($action);
                    }
                }
            }
        }
		
        $query = $this->db->query('
            SELECT p.*, u.username author, t.name as topic_name
            FROM users u2, Product p
            LEFT OUTER JOIN Topic t ON p.topic_id=t.id
            LEFT JOIN users u ON p.user_id = u.id
			'.$where_string.' '.$order_string.' LIMIT '.$start.','.$nb
        );

        return array(
            // 'image_path' => '/uploads/products/' . $this->user->project_id . '/',
            'image_path' => '/uploads/products/1/',
            'entities' => $query->execute(array('id' => $this->user->id)),
			'order' => $order,
            'sort' => $sort,
            'page' => $page,
        );
    }

    public function stockAction() {
        $query = $this->db->query('SELECT p.*, u.username author
            FROM Product p
            LEFT JOIN Topic t ON p.topic_id = t.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE t.project_id=:project
            ORDER BY p.stock, p.id ASC'
        );
        return array(
            'entities' => $query->execute(array('project' => $this->user->project_id))
        );
    }

    public function newAction() {
        $topics = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic p, Topic t
            LEFT JOIN users u on t.project_id=u.project_id
            LEFT JOIN users tu on t.user_id=tu.id
            WHERE t.lft BETWEEN p.lft AND p.rgt
            and u.id=:user and t.hide=0
            GROUP BY t.id
            ORDER BY t.lft'
        )->execute(array('user' => $this->user->id));
        return array(
            'form' => new \Admin\Form\ProductForm(),
            'topics' => $topics
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\ProductForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid($request->getPost())) {

                if (isset($_FILES['file']) and isset($_FILES['file']['size']) and !empty($_FILES['file']['size']) and !empty($_FILES['file']['size'][0])) {
                    $path = __DIR__ . '/../../../../public/uploads/products/' . $this->user->project_id . '/';

                    $image = new SimpleImage();
                    
                    @mkdir($path . '/large/');
                    @mkdir($path . '/thumb/');
                    
                    copy($_FILES['file']['tmp_name'], $path . 'large/' . $_FILES['file']['name']);
                    copy($_FILES['file']['tmp_name'], $path . 'medium/' . $_FILES['file']['name']);
                    copy($_FILES['file']['tmp_name'], $path . 'thumb/' . $_FILES['file']['name']);

                    $image->load($path . 'thumb/' . $_FILES['file']['name']);

                    if ($image->getWidth()) {
                        $image->resizeToWidth($this->thumb_width);
                        if ($image->getHeight() > $this->thumb_width)
                            $image->resizeToHeight($this->thumb_width);
                        $image->save($path . 'thumb/' . $_FILES['file']['name']);
                    }
                    
                    $imageLarge = new SimpleImage();
                    $imageLarge->load($path . 'medium/' . $_FILES['file']['name']);
                    if ($imageLarge->getWidth()) {
                        $imageLarge->resizeToWidth(250);
                        if ($imageLarge->getHeight() > 250)
                            $imageLarge->resizeToHeight(250);
                        $imageLarge->save($path . 'large/' . $_FILES['file']['name']);
                    }

                    $imageLarge = new SimpleImage();
                    $imageLarge->load($path . 'large/' . $_FILES['file']['name']);
                    if ($imageLarge->getWidth()) {
                        $imageLarge->resizeToWidth(600);
                        if ($imageLarge->getHeight() > 600)
                            $imageLarge->resizeToHeight(600);
                        $imageLarge->save($path . 'large/' . $_FILES['file']['name']);
                    }
                    // $image_path = '/uploads/products/' . $this->user->project_id . '/';
                    $image_path = '/uploads/products/1/';
                    $image_name = $_FILES['file']['name'];
                } else {
                    $image_path = null;
                    $image_name = null;
                }

                $insert = $this->db->query(
                    "INSERT INTO Product (name, description, created, updated, user_id, project_id, price, weight, stock, image_path, image_name, topic_id, hide)
                    VALUES (:name, :description, :created, :updated, :user_id, :project_id, :price, :weight, :stock, :image_path, :image_name, :topic_id, :hide)", array(
                        'name' => $request->getPost('name'),
                        'description' => $request->getPost('description'),
                        'created' => time(),
                        'updated' => time(),
                        'user_id' => $this->user->id,
                        'project_id' => $this->user->project_id,
                        'price'  => $request->getPost('price'),
                        'weight' => $request->getPost('weight'),
                        'stock'  => $request->getPost('stock'),
                        'image_path' => $image_path,
                        'image_name' => $image_name,
                        'topic_id' => ($request->getPost('parent') == 0) ? null : $request->getPost('parent'),
                        'hide'  => ($request->getPost('hide') == 'on') ? 1 : 0
                    ));

                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    if (isset($_FILES['file']) and isset($_FILES['file']['size']) and !empty($_FILES['file']['size'])) {
                    $count_files = count($_FILES['file']['size']);
                    $path = __DIR__ . '/../../../../public/uploads/products/' . $this->user->project_id . '/';
                    $image_count = $this->db
                            ->query('
			                    SELECT count(*)
			                    FROM ProductFile pf
			                    WHERE pf.product_id=:id
			                    ')
                            ->execute(array('id' => $id))
                            ->current();
                    for ($i = 0; $i < $count_files; $i++) {
                        if (!empty($_FILES['file']['size'][$i])) {
                            move_uploaded_file($_FILES['file']['tmp_name'][$i], $path . $_FILES['file']['name'][$i]);
                            $sizes = $this->db->query('
			                    SELECT *
			                    FROM ProductImageResize p
			                    WHERE p.pir_hide!=1
			                    ')->execute();
                            $image = new SimpleImage();
                            foreach($sizes as $size) {
                                @mkdir($path . '/' . $size['pir_name'] . '/');
                                $image->load($path . $_FILES['file']['name'][$i]);
                                if ($image->getWidth()) {
                                    $image->resizeToWidth($size['pir_width']);
                                    if ($image->getHeight() > $size['pir_height']) {
                                        $image->resizeToHeight($size['pir_height']);
                                    }
                                    $image->save($path . '/' . $size['pir_name'] . '/' . $_FILES['file']['name'][$i]);
                                }
                            }
                            if ($i < $image_count['count(*)']) {
                                $this->db->query(
                                        'UPDATE ProductFile SET productfile_name=:name WHERE product_id=:id and productfile_position=:position', array(
                                    'name' => $_FILES['file']['name'][$i],
                                    'position' => $i,
                                    'id' => $id
                                ));
                            } else {
                                $this->db->query(
                                        'INSERT INTO ProductFile (productfile_name, product_id, productfile_position) VALUES (:name, :id, :position)', array(
                                    'name' => $_FILES['file']['name'][$i],
                                    'position' => $i,
                                    'id' => $id
                                ));
                            }
                            if ($i == 0) {
                                $update = $this->db->query(
                                        'UPDATE Product SET image_name=:image_name WHERE id=:id', array(
                                    'image_name' => $_FILES['file']['name'][0],
                                    'id' => $id
                                        ));
                            }
                        }
                    }
                }
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'product',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form,
                    'topics' => $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project')->execute(array('project' => $this->user->project_id))
                ));
        $vm->setTemplate('admin/product/new');
        return $vm;
    }

    public function editAction() {
        
        // Only the text to draw is required
        $barcodeOptions = array('text' => 'ZEND-FRAMEWORK');
        // No required options
        $rendererOptions = array();
        // Draw the barcode in a new image,
        $imgBarcode = Barcode::draw(
            'code39', 'image', $barcodeOptions, $rendererOptions
        );

        $request = $this->getRequest();
        $id = $this->params('id');
        $entity = $this->db->query('
            SELECT p.* FROM Product p
            WHERE p.user_id=:user and p.id=:id')
                ->execute(array('id' => $id, 'user' => $this->user->id))
                ->current();
        $entity['options'] = json_decode($entity['options'], true);

        $topics = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic p, Topic t
            LEFT JOIN users u on t.project_id=u.project_id
            LEFT JOIN users tu on t.user_id=tu.id
            WHERE t.lft BETWEEN p.lft AND p.rgt
            and u.id=:user and t.hide=0
            GROUP BY t.id
            ORDER BY t.lft'
        )->execute(array('user' => $this->user->id));

        $options = $this->db->query('
            SELECT sac.*, sa.sa_name
            FROM ShopAttributeChoice sac
            LEFT JOIN ShopAttribute sa USING(sa_id)
            ORDER BY sa_id
            ')->execute();

        if (empty($entity)) {
            die('Product not found.');
        }

        $product_attributes_choices = $this->db
                ->query('
                    SELECT pac.*, pa.sa_id
                    FROM ProductAttributeChoice pac
                    LEFT JOIN ProductAttribute pa USING(pa_id)
                    WHERE pa.product_id=:id
                    ')
                ->execute(array('id' => $id));
        $poc_ids = array();
        foreach ($product_attributes_choices as $product_attribute_choice) {
            $poc_ids[$product_attribute_choice['sa_id']][$product_attribute_choice['sac_id']] = 1;
        }

        $files = $this->db
                ->query('
            SELECT *
            FROM ProductFile pf
            WHERE pf.product_id=:id
            ')
                ->execute(array('id' => $id));

        return array(
            'form' => new \Admin\Form\ProductForm(),
            'entity' => $entity,
            'topics' => $topics,
            'options' => $options,
            'poc_ids' => $poc_ids,
            'files' => $files,
            'barcode' => $imgBarcode,
            // 'image_path' => '/uploads/products/' . $this->user->project_id . '/'
            'image_path' => '/uploads/products/1/'
        );
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db->query('SELECT p.* FROM Product p WHERE p.user_id=:user and p.id=:id')->execute(array('id' => $id, 'user' => $this->user->id))->current();

        if (empty($entity)) {
            die('Product not found.');
        }

        $form = new \Admin\Form\ProductForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {

                if ($request->getPost('attributes') != '') {
                    /*
                      $product_attributes = $this->db
                      ->query('SELECT * FROM ProductAttribute WHERE product_id=:id')
                      ->execute(array('id'=>$id));
                      $po_ids = array();
                      foreach($product_attributes as $product_attribute) {
                      $po_ids[$product_attribute['sa_id']] = $product_attribute['pa_id'];
                      }
                      $product_attributes_choices = $this->db
                      ->query('SELECT pac.* FROM ProductAttributeChoice pac LEFT JOIN ProductAttribute pa ON pac.pa_id=pa.pa_id WHERE pa.product_id=:id')
                      ->execute(array('id'=>$id));
                      $poc_ids = array();
                      foreach($product_attributes_choices as $product_attribute_choice) {
                      $poc_ids[$product_attribute_choice['sac_id']] = 1;
                      }
                     */
                    $attribute_id = null;
                    $attributes_ids = '';
                    $choices_ids = '';
                    foreach ($request->getPost('attributes') as $attribute => $choices) {
                        $attributes_ids .= $attribute . ',';
                        foreach ($choices as $choice => $valid) {
                            $choices_ids .= $choice . ',';
                        }
                    }
                    $this->db
                            ->query('DELETE FROM ProductAttribute WHERE product_id=:id and pa_id NOT IN (:attributes_ids);')
                            ->execute(array('id' => $id, 'attributes_ids' => $attributes_ids));
                    $this->db
                            ->query('
                                DELETE FROM ProductAttributeChoice
                                WHERE pa_id IN (SELECT pa_id FROM ProductAttribute WHERE product_id=:id)
                                and pac_id NOT IN (:choices_ids);
                                ')
                            ->execute(array('id' => $id, 'choices_ids' => $choices_ids));
                    foreach ($request->getPost('attributes') as $attribute => $choices) {
                        if (!isset($po_ids[$attribute])) {
                            $this->db
                                    ->query('
                                INSERT INTO ProductAttribute
                                (product_id, sa_id, user_id, created, updated)
                                VALUES (:id, :attribute, :user, :created, :updated);
                                ')
                                    ->execute(array(
                                        'id' => $id,
                                        'attribute' => $attribute,
                                        'user' => $this->user->id,
                                        'created' => time(),
                                        'updated' => time()
                                    ));
                            $attribute_id = $this->db->getDriver()->getLastGeneratedValue();
                        } else {
                            $attribute_id = null;
                        }

                        foreach ($choices as $choice => $valid) {
                            if (!isset($poc_ids[$choice])) {
                                $this->db
                                        ->query('
                                        INSERT INTO ProductAttributeChoice
                                        (sac_id, pa_id, user_id, created, updated)
                                        VALUES (:id, :pa_id, :user, :created, :updated)
                                        ')
                                        ->execute(array(
                                            'id' => $choice,
                                            'pa_id' => (isset($po_ids[$attribute])) ? $po_ids[$attribute] : $attribute_id,
                                            'user' => $this->user->id,
                                            'created' => time(),
                                            'updated' => time()
                                        ));
                            }
                        }
                    }
                }

                /*
                 * Download and resize image
                 */
                if (isset($_FILES['file']) and isset($_FILES['file']['size']) and !empty($_FILES['file']['size'])) {
                    $count_files = count($_FILES['file']['size']);
                    $path = __DIR__ . '/../../../../public/uploads/products/' . $this->user->project_id . '/';
                    $image_count = $this->db
                            ->query('
			                    SELECT count(*)
			                    FROM ProductFile pf
			                    WHERE pf.product_id=:id
			                    ')
                            ->execute(array('id' => $id))
                            ->current();
                    for ($i = 0; $i < $count_files; $i++) {
                        if (!empty($_FILES['file']['size'][$i])) {
                            move_uploaded_file($_FILES['file']['tmp_name'][$i], $path . $_FILES['file']['name'][$i]);
                            $sizes = $this->db->query('
			                    SELECT *
			                    FROM ProductImageResize p
			                    WHERE p.pir_hide!=1
			                    ')->execute();

                            $image = new SimpleImage();
                            foreach($sizes as $size) {
                                @mkdir($path . '/' . $size['pir_name'] . '/');
                                // die(var_dump($_FILES['file']['name'][$i]));
                                $image->load($path . $_FILES['file']['name'][$i]);
                                if ($image->getWidth()) {
                                    $image->resizeToWidth($size['pir_width']);
                                    if ($image->getHeight() > $size['pir_height']) {
                                        $image->resizeToHeight($size['pir_height']);
                                    }
                                    $image->save($path . '/' . $size['pir_name'] . '/' . $_FILES['file']['name'][$i]);
                                }
                            }
                            if ($i < $image_count['count(*)']) {
                                $this->db->query(
                                        'UPDATE ProductFile SET productfile_name=:name WHERE product_id=:id and productfile_position=:position', array(
                                    'name' => $_FILES['file']['name'][$i],
                                    'position' => $i,
                                    'id' => $id
                                ));
                            } else {
                                $this->db->query(
                                        'INSERT INTO ProductFile (productfile_name, product_id, productfile_position) VALUES (:name, :id, :position)', array(
                                    'name' => $_FILES['file']['name'][$i],
                                    'position' => $i,
                                    'id' => $id
                                ));
                            }
                            if ($i == 0) {
                                $update = $this->db->query(
                                        'UPDATE Product SET image_name=:image_name WHERE id=:id', array(
                                    'image_name' => $_FILES['file']['name'][0],
                                    'id' => $id
                                        ));
                            }
                        }
                    }
                }
                /*
                  if (isset($_FILES['primary_file']) and isset($_FILES['primary_file']['size']) and !empty($_FILES['file']['size'])) {
                  $path = __DIR__ . '/../../../../public/uploads/products/' . $this->user->project_id . '/';
                  $image = new SimpleImage();
                  @mkdir($path.'/large/');
                  @mkdir($path.'/thumb/');
                  copy($_FILES['file']['tmp_name'], $path.'large/'.$_FILES['file']['name']);
                  copy($_FILES['file']['tmp_name'], $path.'thumb/'.$_FILES['file']['name']);
                  move_uploaded_file($_FILES['file']['tmp_name'], $path.$_FILES['file']['name']);

                  $image->load($path.'thumb/'.$_FILES['file']['name']);

                  if ($image->getWidth()) {
                  $image->resizeToWidth($this->thumb_width);
                  if ($image->getHeight()>$this->thumb_width)
                  $image->resizeToHeight($this->thumb_width);
                  $image->save($path.'thumb/'.$_FILES['file']['name']);
                  }

                  $imageLarge = new SimpleImage();
                  $imageLarge->load($path.'large/'.$_FILES['file']['name']);
                  if ($imageLarge->getWidth()) {
                  $imageLarge->resizeToWidth(600);
                  if ($imageLarge->getHeight()>600) {
                  $imageLarge->resizeToHeight(600);
                  }
                  $imageLarge->save($path.'large/'.$_FILES['file']['name']);
                  }

                  $update = $this->db->query(
                  'UPDATE Product SET name=:name, description=:description, project_id=:project_id, price=:price, topic_id=:topic_id, updated=:updated, image_name=:image_name, image_path=:image_path, weight=:weight, stock=:stock, hide=:hide WHERE id=:id', array(
                  'name' => $request->getPost('name'),
                  'description' => $request->getPost('description'),
                  'project_id'  => $this->user->project_id,
                  'price' => $request->getPost('price'),
                  'topic_id' => ($request->getPost('parent')==0) ? null : $request->getPost('parent'),
                  'updated' => time(),
                  'image_path' => '/uploads/products/' . $this->user->project_id . '/',
                  'image_name' => $_FILES['file']['name'],
                  'weight' => $request->getPost('weight'),
                  'stock'  => $request->getPost('stock'),
                  'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                  'id' => $id
                  ));
                  } else {
                 */
                
                $this->db
                        ->query('DELETE FROM Product2Topic WHERE product_id=:id;')
                        ->execute(array('id' => $id));
                foreach ($request->getPost('topics') as $topic) {
                    $this->db
                            ->query('
                            INSERT INTO Product2Topic
                            (topic_id, product_id, created)
                            VALUES (:topic_id, :product_id, :created)
                            ')
                            ->execute(array(
                                'topic_id' => $topic,
                                'product_id' => $id,
                                'created' => time()
                            ));
                }
                
                $update = $this->db->query(
                        'UPDATE Product SET name=:name, description=:description, project_id=:project_id, price=:price, updated=:updated, weight=:weight, stock=:stock, hide=:hide, discount=:discount WHERE id=:id', array(
                    'name' => $request->getPost('name'),
                    'description' => $request->getPost('description'),
                    'project_id' => $this->user->project_id,
                    'price' => $request->getPost('price'),
                    // 'topic_id' => ($request->getPost('parent') == 0) ? null : $request->getPost('parent'),
                    'updated' => time(),
                    'weight' => $request->getPost('weight'),
                    'discount' => $request->getPost('discount'),
                    'stock' => $request->getPost('stock'),
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                    'id' => $id
                        ));
                // }


                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'product',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form,
                    'topics' => $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project')->execute(array('project' => $this->user->project_id))
                ));
        $vm->setTemplate('admin/product/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query('DELETE FROM Product WHERE id=:id')
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute('admin', array(
                                'controller' => 'product'
                            ));;
    }

}

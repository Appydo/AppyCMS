<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class BillController extends AbstractActionController {

    private $table = 'BankOrder';
    
    public function indexAction() {
        
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        // $tableNames = $metadata->getTableNames();
        
        $columns = $metadata->getTable($this->table)->getColumns();

        $page  = 1;
        $nb    = 10;
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
        else $order_string = 'ORDER BY bo.id DESC';

        $where_string = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('query') != '') {
                $where = array();
                $query = $request->getPost('query');
                $metadata = new \Zend\Db\Metadata\Metadata($this->db);
                $columns  = $metadata->getTable($this->table)->getColumns();
                if (is_numeric($request->getPost('query'))) {
                    foreach($columns as $column) {
                        if ($column->getDataType()=='int' or $column->getDataType()=='float') {
                            $where[] = $column->getName().' LIKE "%'.$query.'%"';
                        }
                    }
                }
                foreach($columns as $column) {
                    if ($column->getDataType()=='text' or $column->getDataType()=='varchar') {
                        $where[] = $column->getName().' LIKE "%'.$query.'%"';
                    }
                }
                $where[] = 'u.email="'.$query.'"';
                if (!empty($where)) $where_string = 'and '.implode(' or ',$where);
                $stmt = $this->db->createStatement('SELECT * FROM '.$this->table);
            } elseif ($request->getPost('action_submit') == '1') {
                if ($request->getPost('action_select') == 'delete') {
                    foreach ($request->getPost('action') as $action) {
                        return $this->deleteAction($action);
                    }
                }
            }
        }

        $stmt = $this->db
                ->createStatement('
                    SELECT bo.id, bo.count, bo.price, bo.created, bo.shipment, bo.payment, u.username, u.firstname, u.email, bd.bank_code, bo.user_id
                    FROM '.$this->table.' bo
                    LEFT JOIN BankData bd ON bd.command=bo.id
                    LEFT JOIN users u ON u.id=bo.user_id
                    WHERE bo.payment=1 '.$where_string.' '.$order_string
                    );
        $entities = $stmt->execute()
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);

        $total = 0 ;
        foreach ($entities as $e) {
            if($e['bank_code']=='00' and $e['payment']==1)
                $total += $e['price'];
        }

        return array(
            'entities' => $entities,
            'columns'  => $columns,
            'sort' => $sort,
            'total' => $total
        );

    }

    public function pdfAction() {

        $id = $this->params('id');
        
        $entity = $this->db->query('SELECT *
            FROM ' . $this->table . '
            WHERE id=:id', array(
                'id' => $id
            ))->current();
        
        $project = $this->db->query('SELECT p.*, u.email
            FROM Project p
            LEFT JOIN users u ON u.id=p.user_id
            WHERE p.id=:id', array(
                'id' => $this->user->project_id
            ))->current();
        
        if (!$entity) die('Empty bill');
        
        $client_user = $this->db->query('SELECT *
            FROM users
            WHERE id=:id', array(
                'id' => $entity['user_id']
            ))->current();
        
        $query = $this->db->query('SELECT b.*
                    FROM BasketOrder b
                    WHERE b.bankorder_id=:id ORDER BY b.id')
                ->execute(array(
                    'id' => $id
                ));

        $baskets = array();
        foreach ($query as $key => $basket) {
            $product = $this->db->query('SELECT * FROM Product WHERE id=:id')
                    ->execute(array('id' => $basket['product_id']))
                    ->current();
            $baskets[$key] = $basket;
            if (!empty($product['discount']))
                $baskets[$key]['price'] = $product['discount'];
            else
                $baskets[$key]['price'] = $product['price'];
            foreach (json_decode($basket['attributes']) as $attribute_name => $attribute_value) {
                $sac = $this->db->query('SELECT sac_price FROM ShopAttribute LEFT JOIN ShopAttributeChoice USING(sa_id) WHERE sa_name=:attribute_name and sac_name=:attribute_value')
                        ->execute(array(
                            'attribute_name' => $attribute_name,
                            'attribute_value' => $attribute_value,
                        ))
                        ->current();
                $baskets[$key]['price'] += $sac['sac_price'];
            }

            $baskets[$key]['total'] += $baskets[$key]['price'] * $baskets[$key]['count'];
            $baskets[$key]['image_path'] = $product['image_path'];
            $baskets[$key]['image_name'] = $product['image_name'];
            $baskets[$key]['name'] = $product['name'];
            $weight += $product['weight'];
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
        
        $this->transport = $delivery_price;
        $this->baskets = $baskets;
        $this->total   = $total;
        $this->totalht = round(($total/1.20),2,PHP_ROUND_HALF_UP);
        $this->tva = $this->total - $this->totalht;
        // $this->tva     = round(($total*0.196),2,PHP_ROUND_HALF_UP);
        

        ob_start();
        include( ROOT_PATH . '/public/themes/default/admin/bill/pdf.phtml');
        $content = ob_get_clean();

        $content = '<page>' . $content . '</page>';

        // convert to PDF
        require_once(ROOT_PATH . '/vendor/html2pdf/html2pdf.class.php');
        try {
            $html2pdf = new \HTML2PDF('P', 'A4', 'fr');
            $html2pdf->writeHTML($content, false);
            $html2pdf->Output('bill.pdf');
            exit();
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
        return array();
    }

    public function showAction() {
        $id = $this->params('id');
        $entity = $this->db->query('
            SELECT *
            FROM ' . $this->table . '
            WHERE id=:id', array(
                'id' => $id
            ))->current();
        $query = $this->db->query('SELECT b.*
                    FROM BasketOrder b
                    WHERE b.bankorder_id=:id ORDER BY b.id')
                ->execute(array(
                    'id' => $id
                ));
        $baskets = array();
        $weight  = 0;
        foreach ($query as $key => $basket) {
            $product = $this->db->query('SELECT * FROM Product WHERE id=:id')
                    ->execute(array('id' => $basket['product_id']))
                    ->current();
            $baskets[$key] = $basket;
            if (!empty($product['discount']))
                $baskets[$key]['price'] = $product['discount'];
            else
                $baskets[$key]['price'] = $product['price'];
            foreach (json_decode($basket['attributes']) as $attribute_name => $attribute_value) {
                $sac = $this->db->query('SELECT sac_price FROM ShopAttribute LEFT JOIN ShopAttributeChoice USING(sa_id) WHERE sa_name=:attribute_name and sac_name=:attribute_value')
                        ->execute(array(
                            'attribute_name' => $attribute_name,
                            'attribute_value' => $attribute_value,
                        ))
                        ->current();
                $baskets[$key]['price'] += $sac['sac_price'];
            }
            $baskets[$key]['total'] += $baskets[$key]['price'] * $baskets[$key]['count'];
            $baskets[$key]['image_path'] = $product['image_path'];
            $baskets[$key]['image_name'] = $product['image_name'];
            $baskets[$key]['name'] = $product['name'];
            $weight += $product['weight'];
            $total  += $baskets[$key]['total'];
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

        $layout = $this->layout();
        $layout->title = 'Mes commandes';

        return array(
            'transport' => $delivery_price,
            'baskets' => $baskets,
            'entity' => $entity,
            'total' => $total,
            'totalht' => round($total-($total*0.196),2,PHP_ROUND_HALF_UP),
        );
    }

    public function newAction() {
        
        $acl = new Acl();
        $query = $this->db->createStatement('
            SELECT role_name, role_parent
            FROM Role r
            LEFT JOIN users u ON u.project_id = r.project_id
            WHERE u.id=:user_id'
        );
        $roles = $query->execute(array('user_id' => $this->user->id))->getResource()->fetchAll();
        
        $user_role = $this->db->query('
                SELECT r.role_name
                FROM users u
                LEFT JOIN Acl a ON (u.id=a.user_id and a.project_id=u.project_id)
                LEFT JOIN Role r USING(role_id) WHERE u.id=:user_id'
            )->execute(array('user_id' => $this->user->id))->current();

        $acl->addResource(new Resource('user'));

        foreach ($roles as $role) {
            $acl->addRole(new Role($role['role_name']), $role['role_parent']);
        }

        $acl->allow('admin');
        if (!$acl->isAllowed($user_role['role_name'], 'user', 'list')) {
            $this->getResponse()->setStatusCode(404); 
            return;
        }
        
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
        
        if (!empty($order)) $order_string = 'ORDER BY u.'.$order.' '.$sort;
        else $order_string = '';
        
        $where_string = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('query') != '') {
                $where = array();
                $query = $request->getPost('query');
                $metadata = new \Zend\Db\Metadata\Metadata($this->db);
                $columns  = $metadata->getTable($this->table)->getColumns();
                foreach($columns as $column) {
                    // echo $column->getDataType();
                    if ($column->getDataType()=='text' or $column->getDataType()=='varchar') {
                        $where[] = $column->getName().' LIKE "%'.$query.'%"';
                    }
                }
                if (!empty($where)) $where_string = 'WHERE '.implode(' or ',$where);
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
        
        $entities = $this->db->query('
                SELECT u.*, r.role_name
                FROM users u
                LEFT JOIN Acl a ON (u.id=a.user_id and a.project_id=:project_id)
                LEFT JOIN Role r USING(role_id)
                '.$where_string.' '.$order_string.' LIMIT '.$start.','.$nb
            )->execute(array(
                    'project_id'=>$this->project['id']
                ));
        
        $count = $this->db->query('
                SELECT count(u.id) as count
                FROM users u
                LEFT JOIN Acl a ON (u.id=a.user_id and a.project_id=:project_id)
                LEFT JOIN Role r USING(role_id)
                '.$where_string.' '.$order_string.' LIMIT '.$start.','.$nb
            )->execute(array(
                    'project_id'=>$this->project['id']
                ))->current();

        return array(
            'entities' => $entities,
            'controller' => $this->controller,
            'id' => $this->id,
            'thead' => true, // display thead
            'module' => $this->module,
            'order' => $order,
            'sort' => $sort,
            'page' => $page,
            'query' => $request->getPost('query'),
            'count' => $count['count']
        );
    }

    public function createOrderAction() {
        $user_id  = $this->params('id');
        $result = array();
        if (is_numeric($user_id)) {
            $insert = $this->db->query('INSERT INTO BankOrder (user_id, count, price, created, updated, hide, payment) VALUES (:user_id, 0, 0, :created, :updated, 0, 0)')
                ->execute(array('user_id' => $user_id, 'created' => time(), 'updated' => time()));
        }
        if ($insert) {
            $id = $this->db->getDriver()->getLastGeneratedValue();
            return $this->redirect()->toRoute('admin', array(
                        'controller' => 'bill',
                        'action' => 'new2',
                        'id' => $id
                    ));
        } else {
            $this->getResponse()->setStatusCode(404);
            return;
        }
    }

    public function addProductAction() {
        $product   = $this->params('id');
        $bankorder = $_GET['bankorder'];
        $result = array();
        if (is_numeric($user_id)) {
            $insert = $this->db->query('INSERT INTO BasketOrder b (product_id, bankorder_id, count, created, updated, hide) VALUES (:product, :bankorder, :created, 1, :updated, 0)')
                ->execute(array('id' => $id, 'bankorder' => $bankorder));
        }
        if ($insert) {
            $id = $this->db->getDriver()->getLastGeneratedValue();
            return $this->redirect()->toRoute('admin', array(
                        'controller' => 'bill',
                        'action' => 'new2',
                        'id' => $bankorder
                    ));
        } else {
            $this->getResponse()->setStatusCode(404);
            return;
        }
    }

    public function new2Action() {
    
        $bankorder  = $this->params('id');

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
            'entities'   => $query->execute(array('id' => $this->user->id)),
            'order'      => $order,
            'sort'       => $sort,
            'page'       => $page,
            'bankorder'  => $bankorder
        );
    }

    public function new3Action() {
        $user_id  = $this->params('id');
        
        return array(
            'form' => new \Admin\Form\BillForm()
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\DeliveryForm();

        if ($request->isPost()) {
            /*
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
            $inputFilter->add($factory->createInput(array(
                'name'     => 'name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 30,
                        ),
                    ),
                ),
            )));
            $form->setInputFilter($inputFilter);
             */
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $insert = $this->db->query(
                    "INSERT INTO Delivery (delivery_name, price, weight, created, updated, user_id, hide)
                    VALUES (:delivery_name, :price, :weight, :created, :updated, :user_id, :hide)", array(
                    'delivery_name' => $request->getPost('delivery_name'),
                    'price' => $request->getPost('price'),
                    'weight' => $request->getPost('weight'),
                    'created' => time(),
                    'updated' => time(),
                    'user_id' => $this->user->id,
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0
                     ));
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'delivery',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/delivery/new');
        return $vm;
    }

    public function editAction() {

        $id = $this->params('id');
        $form = new \Admin\Form\BillForm();
        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE delivery_id=:id')
                ->execute(array('id'=>$id))
                ->current();
        $form->setData($entity);
        if (empty($entity)) {
            die($this->table.' not found.');
        }

        return array(
            'form' => $form,
            'entity' => $entity
        );
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE delivery_id=:id')
                ->execute(array('id'=>$id))
                ->current();

        if (empty($entity)) {
            die('Delivery not found.');
        }

        $form = new \Admin\Form\DeliveryForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update = $this->db->query('UPDATE '.$this->table.'
                    SET price=:price, updated=:updated, weight=:weight, hide=:hide, delivery_name=:delivery_name WHERE delivery_id=:id', array(
                    'delivery_name' => $request->getPost('delivery_name'),
                    'price' => $request->getPost('price'),
                    'weight' => $request->getPost('weight'),
                    'updated' => time(),
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                    'id' => $id
                    ));
                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'delivery',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }
        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/bill/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query('DELETE FROM BankOrder WHERE id=:id')
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute('admin', array(
                                'controller' => 'bill'
                            ));;
    }

}

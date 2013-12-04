<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ShipController extends AbstractActionController {

    private $table = 'BankOrder';

    public function dataAction() {
        $id = $this->params('id');
        $entity = $this->db
                ->query('SELECT * FROM BankData WHERE command=:id')
                ->execute(array('id'=>$id))
                ->current();
        return array(
            'entity' => $entity,
            'columns'  => $columns,
            'baskets'  => $baskets,
            'total'    => $total,
        );
    }
    
    public function indexAction() {
        
        $request = $this->getRequest();
        if ($request->isPost() and $request->getPost('action_submit')=='1') {
            if ($request->getPost('action_select')=='delete') {
                $this->deleteAction();
            } elseif ($request->getPost('action_select')=='send') {
                foreach($request->getPost('action') as $action) {
                    $update = $this->db->query('UPDATE '.$this->table.'
                        SET shipment=:shipment WHERE id=:id', array(
                        'shipment' => 1,
                        'id' => $action
                        ));
                }
            }
        }
        
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        // $tableNames = $metadata->getTableNames();
        
        $columns = $metadata->getTable($this->table)->getColumns();

        $stmt = $this->db
                ->createStatement('
                    SELECT bo.id, bo.description, bo.count, bo.price, bo.created, bo.created as date, u.address, u.city, u.postal, u.firstname, u.username, u.email, u.id as user_id, bd.bank_code as code, u.phone
                    FROM '.$this->table.' bo
                    LEFT JOIN users u ON u.id=bo.user_id
                    LEFT JOIN BankData bd ON bo.id=bd.command
                    WHERE (bo.shipment is NULL or bo.shipment=0) and (bo.hide!=1) and bo.payment=1
                    ');
                $entities = $stmt->execute()
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);
        $baskets = array();
        foreach($entities as $i=>$entity) {
            $query = $this->db->query('SELECT b.*
                        FROM BasketOrder b
                        WHERE b.bankorder_id=:id ORDER BY b.id')
                    ->execute(array(
                        'id' => $entity['id']
                    ));
            
            foreach ($query as $key => $basket) {
                $product = $this->db->query('SELECT * FROM Product WHERE id=:id')
                        ->execute(array('id' => $basket['product_id']))
                        ->current();
                $baskets[$i][$key] = $basket;
                $baskets[$i][$key]['price'] = $product['price'];
                foreach (json_decode($basket['attributes']) as $attribute_name => $attribute_value) {
                    $sac = $this->db->query('SELECT sac_price FROM ShopAttribute LEFT JOIN ShopAttributeChoice USING(sa_id) WHERE sa_name=:attribute_name and sac_name=:attribute_value')
                            ->execute(array(
                                'attribute_name' => $attribute_name,
                                'attribute_value' => $attribute_value,
                            ))
                            ->current();
                    $baskets[$i][$key]['price'] += $sac['sac_price'];
                }
                $baskets[$i][$key]['total'] += $baskets[$key]['price'] * $baskets[$key]['count'];
                $baskets[$i][$key]['image_path'] = $product['image_path'];
                $baskets[$i][$key]['image_name'] = $product['image_name'];
                $baskets[$i][$key]['name'] = $product['name'];
                $weight[$i] = $product['weight'];
                $total[$i]  = $baskets[$key]['total'];
            }
        }

        return array(
            'entities' => $entities,
            'columns'  => $columns,
            'baskets' => $baskets,
            'total' => $total,
        );

    }

    public function showAction() {

        $id = $this->params('id');
        $form = new \Admin\Form\BillForm();
        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE id=:id')
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

    public function newAction() {
        
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
        $vm->setTemplate('admin/ship/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $update = $this->db->query('UPDATE '.$this->table.'
                        SET hide=:hide WHERE id=:id', array(
                        'hide' => 1,
                        'id' => $action
                        ));
            }
            
        }
        
        return $this->redirect()->toRoute('admin', array(
                                'controller' => 'ship'
                            ));;
    }

}

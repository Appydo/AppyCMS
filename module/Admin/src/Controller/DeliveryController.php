<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class DeliveryController extends AbstractActionController {

    private $title      = 'Delivery';
    private $table      = 'Delivery';
    private $controller = 'Delivery';
    private $template   = 'admin/delivery';
    private $id         = 'delivery_id';
    private $select     = 'delivery_id';
    private $module     = 'admin';
    private $table_row  = 20;

    private function defaultTemplateVars() {
        $result = array();
        $result['primary_id'] = $this->id;
        $result['controller'] = $this->controller;
        $result['module']     = $this->module;
        return $result;
    }
    
    public function indexAction() {

        $result = array();

        $result['entities'] = $this->db
                ->createStatement('SELECT * FROM '.$this->table.'')
                ->execute()
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);

        return $result;

    }

    public function showAction() {
        $id = $this->params('id');
        $entity = $this->db->query('SELECT *
            FROM '.$this->table.'
            WHERE id=:id', array('id' => $id)
            )->current();
        $query  = $this->db->query('SELECT b.*
                    FROM BasketOrder b
                    WHERE b.bankorder_id=:id ORDER BY b.id')
                    ->execute(array(
                        'id' => $id,
                        ));
        $baskets = array();
        foreach($query as $key=>$basket) {
            $product = $this->db->query('SELECT * FROM product WHERE id=:id')
                    ->execute(array('id'=>$basket['product_id']))
                    ->current();
            $baskets[$key] = $basket;
            $baskets[$key]['price'] = $product['price'];
            $baskets[$key]['name']  = $product['name'];
            $total                 += $product['price'];
        }
        return array(
            'baskets' => $baskets,
            'entity' => $entity
        );
    }

    public function newAction() {
        $result = $this->defaultTemplateVars();
        $result['form'] = new \Admin\Form\DeliveryForm();
        $result['id']   = $this->id;
        return $result;
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\DeliveryForm();

        if ($request->isPost()) {
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

        $result = $this->defaultTemplateVars();
        $result['form']   = new \Admin\Form\DeliveryForm();
        $result['id']     = $this->params('id');
        $result['entity'] = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
                ->execute(array('id'=>$result['id']))
                ->current();
        $result['form']->setData($result['entity']);
        if (empty($result['entity'])) {
            die($this->table.' not found.');
        }

        return $result;
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE delivery_id=:id')
                ->execute(array('id'=>$id))
                ->current();

        if (empty($entity)) {
            throw new \Exception("Could not find row $id");
        }

        $form = new \Admin\Form\DeliveryForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update = $this->db->query('UPDATE '.$this->table.'
                    SET price=:price, updated=:updated, hide=:hide, delivery_name=:delivery_name, delivery_delay=:delivery_delay, delivery_rule=:delivery_rule WHERE delivery_id=:id', array(
                    'delivery_name' => $request->getPost('delivery_name'),
                    'delivery_delay' => $request->getPost('delivery_delay'),
                    'delivery_rule' => $request->getPost('delivery_rule'),
                    'price' => $request->getPost('price'),
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
        $vm->setTemplate('admin/delivery/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($model->delete($request->getPost('action'))==0) {
                $this->flashMessenger()->addErrorMessage('Error : no row deleted.');
            } else {
                $this->flashMessenger()->addSuccessMessage($count.' row(s) deleted.');
            }
        } else {
            $this->flashMessenger()->addErrorMessage('Delete error : no data found.');
        }
        return $this->redirect()
            ->toRoute($this->module, array(
                'controller' => $this->controller,
            ));;
    }

}

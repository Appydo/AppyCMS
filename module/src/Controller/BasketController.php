<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BasketController extends AbstractActionController {
    
    public function indexAction()
    {
        $entities  = $this->db->query('SELECT b.*, p.price as price, p.weight as weight FROM Basket b LEFT JOIN Product p ON p.id=b.product_id WHERE b.hide=0 and b.user_id=:user')->execute(array('user' => $this->user->id))->current();
        
        $price  = 0;
        $weight = 0;
        
        foreach ($entities as $entity) {
            $price   = $price + ($entity['price'] * $entity['count']);
            $weight  = $weight + ($entity['weight'] * $entity['count']);
        }
        
        // $port = new \FraisPort();
        // $fdp  = $port->calcul($poids);
        
        $transport = 4.80;
        
        return array(
            'transport' => $transport,
            'form' => new \Admin\Form\BillForm()
            );
    }
    
    public function addAction()
    {
        $request   = $this->getRequest();
        $id        = $this->params('id');
        $product   = $this->db->query('SELECT p.* FROM Product p WHERE p.user_id=:user and p.id=:id')->execute(array('id' => $id))->current();
        $basket    = $this->db->query('INSERT INTO Basket b WHERE b.hide=0 and b.user_id=:user and b.product_id=:id')->execute(array('id' => $id, 'user' => $this->user->id))->current();
        
        $entities  = $this->db->query('SELECT b.*, p.price as price, p.weight as weight FROM Basket b LEFT JOIN Product p ON p.id=b.product_id WHERE b.hide=0 and b.user_id=:user')->execute(array('user' => $this->user->id))->current();
        
        $price  = 0;
        $weight = 0;
        
        foreach ($entities as $entity) {
            $price   = $price + ($entity['price'] * $entity['count']);
            $weight  = $weight + ($entity['weight'] * $entity['count']);
        }
        
        $port = new \FraisPort();
        $fdp  = $port->calcul($poids);
        
        return array(
            'form' => new \Admin\Form\BillForm()
            );
        
    }
    
    public function newAction()
    {
        return array(
            'form' => new \Admin\Form\BillForm()
            );
        
    }

    public function createAction()
    {
        $request   = $this->getRequest();
        $form = new \Admin\Form\BillForm();
        if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid($request->getPost())) {
                $insert = $this->db->query("INSERT INTO Bill (name, description, created, updated, user_id, price, weight, topic_id, hide, stock)
                    VALUES (:name, :description, :created, :updated, :user_id, :price, :weight, :topic_id, :hide, :stock)", array(
                    'name'     => $request->getPost('name'),
                    'description'  => $request->getPost('description'),
                    'created'  => time(),
                    'updated'  => time(),
                    'user_id'  => $this->user->id,
                    'price'    => $request->getPost('price'),
                    'weight'   => $request->getPost('weight'),
                    'topic_id' => ($request->getPost('parent')==0) ? null : $request->getPost('parent'),
                    'hide'     => ($request->getPost('hide')=='on') ? 1 : 0,
                    'stock'    => 0
                ));

                if($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'bill',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }
        
        return array(
            'form' => $form
        );
    }

    public function editAction() {
        $request = $this->getRequest();
        $id      = $this->params('id');
        $entity  = $this->db->query('SELECT p.* FROM Product p WHERE p.user_id=:user and p.id=:id')->execute(array('id' => $id, 'user' => $this->user->id))->current();
        
        if (empty($entity)) {
            die('Bill #'.$id.' not found.');
        }

        return array(
            'form' => new \Admin\Form\BillForm(),
            'entity' => $entity
        );
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id      = $this->params('id');
        
        $entity  = $this->db->query('SELECT p.* FROM Product p WHERE p.user_id=:user and p.id=:id')
                ->execute(array(
                    'id' => $id,
                    'user' => $this->user->id
                ))->current();

        if (empty($entity)) {
            die('Product not found.');
        }

        $form = new \Admin\Form\ProductForm();
        
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update = $this->db->query(
                    'UPDATE Product
                        SET name=:name, description=:description, price=:price, topic_id=:topic_id, updated=:updated, weight=:weight, hide=:hide
                        WHERE id=:id', array(
                        'name'     => $request->getPost('name'),
                        'description' => $request->getPost('description'),
                        'price'    => $request->getPost('price'),
                        'topic_id' => ($request->getPost('parent')==0) ? null : $request->getPost('parent'),
                        'updated'  => time(),
                        'weight'   => $request->getPost('weight'),
                        'hide'     => ($request->getPost('hide') == 'on') ? 1 : 0,
                        'id'       => $id
                        ));
                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'product',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }
        return array(
            'form' => $form
        );
    }

}

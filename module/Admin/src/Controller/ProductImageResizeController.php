<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ProductImageResizeController extends AbstractActionController {

    private $table      = 'ProductImageResize';
    private $controller = 'ProductImageResize';
    private $id         = 'pir_id';
    
    public function indexAction() {

        $request = $this->getRequest();
        $like  = '';

        if ($request->isPost()) {
            if ($request->getPost('query')!='') {
                $like = $request->getPost('query');
            }
        }

        if (empty($like)) {
            $entities = $this->db
                ->createStatement('
                    SELECT pir_id, pir_name as name, pir_width as width, pir_height as height, pir_hide as active
                    FROM '.$this->table)
                ->execute()
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $entities = $this->db
                ->createStatement('
                    SELECT pir_id, pir_name as name, pir_width as width, pir_height as height, pir_hide as active
                    FROM '.$this->table.' WHERE pir_name LIKE :like')
                ->execute(array('like'=>'%'.$like.'%'))
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);
        }

        return array(
            'thead' => false,
            'entities' => $entities,
            'controller' => $this->controller,
            'id' => $this->id,
        );

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
        return array(
            'form' => new \Admin\Form\ProductImageResizeForm(),
            'controller' => $this->controller,
            'id' => $this->id,
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\ProductImageResizeForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $insert_args = array();
                foreach($form as $element) {
                    if(!in_array($element->getName(),array($this->id, 'csrf', 'submit')))
                        $insert_args[$element->getName()] = $request->getPost($element->getName());
                }
                $insert_args['created'] = time();
                $insert_args['updated'] = time();

                $insert = $this->db->query(
                    "INSERT INTO ".$this->table." (".implode(",", array_keys($insert_args)).")
                    VALUES (:".implode(",:", array_keys($insert_args)).")",
                        $insert_args
                        );
                
                if ($insert) {
                    $this->flashMessenger()->addSuccessMessage('The item was created successfully.');
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => $this->controller,
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/delay/new');
        return $vm;
    }

    public function editAction() {
        $id = $this->params('id');
        $form = new \Admin\Form\ProductImageResizeForm();
        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
                ->execute(array('id'=>$id))
                ->current();
        $form->setData($entity);
        if (empty($entity)) {
            die($this->table.' not found.');
        }

        return array(
            'form' => $form,
            'entity' => $entity,
            'controller' => $this->controller,
            'id' => $this->id,
        );
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
                ->execute(array('id'=>$id))
                ->current();

        if (empty($entity)) {
            die('Entity not found.');
        }

        $form = new \Admin\Form\ProductImageResizeForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update_args = array();
                foreach($form as $element) {
                    if(!in_array($element->getName(),array($this->id, 'csrf', 'submit')))
                        $update_args[$element->getName()] = $request->getPost($element->getName());
                }
                $update_set = substr($update_set, 0, -1);
                $update_args['updated'] = time();
                $update_args[$this->id] = $id;
                $update_set = array();
                foreach($update_args as $key=>$value) {
                    $update_set[] = $key . '=:' . $key;
                }

                $update = $this->db->query('UPDATE '.$this->table.'
                    SET '.implode(",", $update_set).'  WHERE '.$this->id.'=:'.$this->id,
                        $update_args
                        );
                if ($update) {
                    $this->flashMessenger()->addSuccessMessage('The item was updated successfully.');
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => $this->controller,
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/'.$this->controller.'/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query('DELETE FROM '.$this->table.' WHERE '.$this->id.'=:id')
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute('admin', array(
                                'controller' => $this->controller,
                            ));;
    }

}

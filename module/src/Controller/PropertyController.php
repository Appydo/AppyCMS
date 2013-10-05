<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class PropertyController extends AbstractActionController {

    private $table      = 'Property';
    private $controller = 'Property';
    private $form       = 'PropertyForm';
    private $id         = 'property_id';
    
    public function indexAction() {
        
        // $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        // $tableNames = $metadata->getTableNames();
        // $columns = $metadata->getTable($this->table)->getColumns();
        $page  = 1;
        $nb    = 10;
        $start = ($page * $nb) - $nb;
        
        $order = '';
        if($_GET['sort']=='ASC')
            $sort  = 'ASC';
        else
            $sort  = 'DESC';
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
        }

        $stmt = $this->db
                ->createStatement('
                    SELECT *
                    FROM '.$this->table);

        $entities = $stmt->execute(array())
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);

        return array(
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
            $baskets[$key]['image_path'] = $product['image_path'];
            $baskets[$key]['image_name'] = $product['image_name'];
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
        $form = "\\Admin\\Form\\".$this->form;
        return array(
            'form' => new $form(),
            'controller' => $this->controller,
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = "\\Admin\\Form\\".$this->form;
        $form = new $form();

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
                    'form' => $form,
                    'controller' => $this->controller,
                ));
        $vm->setTemplate('admin/'.$this->controller.'/new');
        return $vm;
    }

    public function editAction() {
        $id = $this->params('id');
        $form = "\\Admin\\Form\\".$this->form;
        $form = new $form();
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

        $form = "\\Admin\\Form\\".$this->form;
        $form = new $form();

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

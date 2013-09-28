<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class DelayController extends AbstractActionController {

    private $table = 'Delay';
    
    public function indexAction() {
        
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        // $tableNames = $metadata->getTableNames();
        
        $columns = $metadata->getTable($this->table)->getColumns();

        $stmt = $this->db
                ->createStatement('
                    SELECT *
                    FROM '.$this->table.'
                    ');
                $entities = $stmt->execute()
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);

        return array(
            'entities' => $entities,
            'columns'  => $columns
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
            'form' => new \Admin\Form\DelayForm()
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\DelayForm();

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
                    "INSERT INTO Delay (delay_name, delay, delay_rule, created, updated, hide)
                    VALUES (:delay_name, :delay, :delay_rule, :created, :updated, :hide)", array(
                    'delay_name' => $request->getPost('delay_name'),
                    'delay' => $request->getPost('delay'),
                    'delay_rule' => $request->getPost('delay_rule'),
                    'created' => time(),
                    'updated' => time(),
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0
                     ));
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'delay',
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
        $form = new \Admin\Form\DelayForm();
        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE delay_id=:id')
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
                ->query('SELECT * FROM '.$this->table.' WHERE delay_id=:id')
                ->execute(array('id'=>$id))
                ->current();

        if (empty($entity)) {
            die('Delay not found.');
        }

        $form = new \Admin\Form\DelayForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update = $this->db->query('UPDATE '.$this->table.'
                    SET  updated=:updated, hide=:hide, delay_name=:delay_name, delay=:delay, delay_rule=:delay_rule WHERE delay_id=:id', array(
                    'delay_name' => $request->getPost('delay_name'),
                    'delay' => $request->getPost('delay'),
                    'delay_rule' => $request->getPost('delay_rule'),
                    'updated' => time(),
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                    'id' => $id
                    ));
                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'delay',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/delay/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query('DELETE FROM Delay WHERE delay_id=:id')
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute('admin', array(
                                'controller' => 'delay'
                            ));;
    }

}

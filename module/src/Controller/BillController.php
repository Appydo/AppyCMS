<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class BillController extends AbstractActionController {

    private $table = 'BankOrder';
    
    public function indexAction() {
        
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        // $tableNames = $metadata->getTableNames();
        
        $columns = $metadata->getTable($this->table)->getColumns();

        $stmt = $this->db
                ->createStatement('
                    SELECT bo.id, bo.count, bo.price, bo.created, bo.shipment, u.username, u.firstname, u.email
                    FROM '.$this->table.' bo
                    LEFT JOIN users u ON u.id=bo.user_id
                    WHERE bo.payment=1
                    ORDER BY bo.id DESC
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

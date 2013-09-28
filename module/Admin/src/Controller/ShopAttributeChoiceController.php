<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ShopAttributeChoiceController extends AbstractActionController {

    private $table = 'ShopAttributeChoice';
    
    public function indexAction() {
        $id = $this->params('id');

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('sa_edit')=='1' and $request->getPost('sa_name')!='') {
		$update = $this->db->query(
                    "UPDATE ShopAttribute SET sa_name=:name WHERE sa_id=:id", array(
                    'name' => $request->getPost('sa_name'),
                    'id' => $id
                    ));
            }
            if ($request->getPost('action_submit')=='1') {
                return $this->deleteAction($id);
            }
            if ($request->getPost('choice')!='') {
            $insert = $this->db->query("INSERT INTO {$this->table} (sac_name, sa_id, created, updated, user_id)
                VALUES (:name, :sa_id, :created, :updated, :user_id)", array(
                'name'    => $request->getPost('choice'),
                'sa_id'   => $id,
                'created' => time(),
                'updated' => time(),
                'user_id' => $this->user->id,
                 ));
            }
            if ($request->getPost('sac_default')!='') {
                $update = $this->db->query(
                    "UPDATE {$this->table} SET sac_default=0 WHERE sa_id=:id and sac_default=1", array(
                    'id' => $id
                    ));
                $update = $this->db->query(
                    "UPDATE {$this->table} SET sac_default=1 WHERE sac_id=:id", array(
                    'id' => $request->getPost('sac_default')
                    ));
            }
            foreach($request->getPost('price') as $sac_id=>$price) {
                $update = $this->db->query(
                    "UPDATE {$this->table} SET sac_price=:price WHERE sac_id=:id", array(
                        'price' => $price,
                        'id' => $sac_id
                    ));
            }
            foreach($request->getPost('name') as $sac_id=>$name) {
                $update = $this->db->query(
                    "UPDATE {$this->table} SET sac_name=:name WHERE sac_id=:id", array(
                        'name' => $name,
                        'id' => $sac_id
                    ));
            }
            foreach($request->getPost('position') as $sac_id=>$position) {
                $update = $this->db->query(
                    "UPDATE {$this->table} SET sac_position=:position WHERE sac_id=:id", array(
                        'position' => $position,
                        'id' => $sac_id
                    ));
            }
        }

        $option = $this->db
                ->query('SELECT * FROM ShopAttribute WHERE sa_id=:id')
                ->execute(array('id'=>$id))
                ->current();

        $entities = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE sa_id=:id')
                ->execute(array('id'=>$id));

        return array(
            'option' => $option,
            'entities' => $entities
        );

    }

    public function showAction($id) {
        $entity = $this->db->query('SELECT *
            FROM Project p
            WHERE p.id=:id', array('id' => $id)
            )->current();

        return array(
            'entity' => $entity
        );
    }

    public function newAction() {
        return array(
            'form' => new \Admin\Form\ProjectForm()
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\ProjectForm();

        if ($request->isPost()) {
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
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $insert = $this->db->query("INSERT INTO Project (name, description, created, updated, user_id, hide)
                    VALUES (:name, :description, :created, :updated, :user_id, :hide)", array(
                    'name'    => $request->getPost('name'),
                    'description' => $request->getPost('description'),
                    'created' => time(),
                    'updated' => time(),
                    'user_id' => $this->user->id,
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0
                     ));
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'project',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/project/new');
        return $vm;
    }

    public function editAction() {

        $id = $this->params('id');
        $entity = $this->db
                ->query('SELECT * FROM '.$this->table)
                ->execute()
                ->current();

        if (empty($entity)) {
            die($this->table.' not found.');
        }

        return array(
            'form' => new \Admin\Form\ProductOptionForm(),
            'entity' => $entity
        );
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db->query('SELECT p.* FROM Project p WHERE p.user_id=:user and p.id=:id')->execute(array('id' => $id, 'user' => $this->user->id))->current();

        if (empty($entity)) {
            die('Project not found.');
        }

        $form = new \Admin\Form\ProjectForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {

                $update = $this->db->query(
                        'UPDATE Project SET name=:name, description=:description, updated=:updated, comment=:comment, hide=:hide WHERE id=:id', array(
                    'name' => $request->getPost('name'),
                    'description' => $request->getPost('description'),
                    'updated' => time(),
                    'comment' => ($request->getPost('comment') == 'on') ? 1 : 0,
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                    'id' => $id
                        ));
                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'project',
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

    public function deleteAction($id=null) {
        $request = $this->getRequest();

        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query("DELETE FROM {$this->table} WHERE sac_id=:id")
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute('admin', array(
                                'controller' => 'shopattributechoice',
                                'id'=>$id
                            ));;
    }

}

<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DatabaseController extends AbstractActionController {

    private $table = 'StructureVersion';
    
    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $insert = $this->db->query("INSERT INTO {$this->table} (sa_name, created, updated, user_id)
                VALUES (:name, :created, :updated, :user_id)", array(
                'name'    => $request->getPost('option'),
                'created' => time(),
                'updated' => time(),
                'user_id' => $this->user->id,
                 ));
        }
        /*
        $entities   = $this->db
                ->query('
                    SELECT *
                    FROM '.$this->table.' sa
                    ')
                ->execute();
        */
        $entities = array();
        $stats = $this->db->query('SELECT table_schema "Data Base Name",
        SUM( data_length + index_length ) / 1024 / 1024 "Data Base Size in MB",
        SUM( data_free )/ 1024 / 1024 "Free Space in MB"
        FROM information_schema.TABLES
        GROUP BY table_schema')->execute()->current();
        return array(
            'entities' => $entities,
            'stats' => $stats
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
                    'form' => $form,
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

    public function deleteAction($id) {
        $request = $this->getRequest();

        if ($form->isValid()) {

            $this->view->entity = $this->db->query('SELECT p.* FROM Project p WHERE p.user_id=:user and p.id=:id', array('id' => $id, 'user' => $this->user->id))->fetch();

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Project entity.');
            }

            $em->remove($entity);
        }

        return $this->redirect($this->generateUrl('project'));
    }

}

<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class ResourceController extends AbstractActionController {

    private $title      = 'Resource';
    private $table      = 'Resource';
    private $controller = 'Resource';
    private $template   = 'admin/resource';
    private $id         = 'resource_id';
    private $select     = 'resource_id';
    private $module     = 'admin';
    private $table_row  = 20;
    
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

    public function newAction() {
        
        return array(
            'form' => new \Admin\Form\ResourceForm()
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\ResourceForm();

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
                    "INSERT INTO Resource (resource_name, created, updated, project_id, hide)
                     VALUES (:resource_name, :created, :updated, :project, :hide)", array(
                    'resource_name' => $request->getPost('resource_name'),
                    'project' => $this->project['id'],
                    'created' => time(),
                    'updated' => time(),
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0
                     ));
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'resource',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/resource/new');
        return $vm;
    }

    public function editAction() {

        $id = $this->params('id');
        $form = new \Admin\Form\ResourceForm();
        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE resource_id=:id')
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

    private function generateForm() {

        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        $columns = $metadata->getTable($this->table)->getColumns();

        $form = new Form($this->controller);

        // Generate input
        foreach($columns as $column) {
            if (!$form->has($column->getName()) and !in_array($column->getName(), array('created','updated'))) {
                $element = new Element($column->getName());
                $element->setLabel($column->getName());
                $element->setAttributes(array(
                    'type'  => 'text'
                ));
                $form->add($element);
            }
        }

        return $form;
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE resource_id=:id')
                ->execute(array('id'=>$id))
                ->current();

        if (empty($entity)) {
            throw new \Exception("Could not find row {$id}");
        }

        $form    = $this->generateForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                
                $update_args = array();
                foreach($form as $element) {
                    if(!in_array($element->getName(),array($this->id, 'csrf', 'submit')))
                        $update_args[$element->getName()] = $request->getPost($element->getName());
                }
                $update_set = substr($update_set, 0, -1);
                $update_args['project_id'] = $this->user->project_id;
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

                if (!empty($id)) {
                    $this->flashMessenger()->addSuccessMessage('The item was updated successfully.');
                    return $this->redirect()->toRoute($this->module, array(
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
        $vm->setTemplate('admin/resource/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query('DELETE FROM Resource WHERE resource_id=:id')
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute('admin', array(
                                'controller' => 'resource'
                            ));;
    }

}

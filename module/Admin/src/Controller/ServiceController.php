<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ServiceController extends AbstractActionController {

    private $title      = 'Service';
    private $table      = 'Service';
    private $controller = 'Service';
    private $template   = 'admin/service';
    private $id         = 'id';
    private $select     = 'id, name, url';
    private $module     = 'admin';
    private $table_row  = 20;

    private function initAcl($method) {
        $allows = $this->db
            ->query('SELECT allow_id FROM Allow WHERE controller=:controller and privilege=:privilege and role_id=:role LIMIT 1')
            ->execute(array(
                'controller' => $this->controller,
                'privilege'  => $method,
                'role'       => $this->user->role,
                ))
            ->current();

        if (empty($allows)) {
            throw new \Exception("Access denied");
        }
    }

    private function defaultTemplateVars() {
        $result = array();
        $result['primary_id'] = $this->id;
        $result['controller'] = $this->controller;
        $result['module']     = $this->module;
        return $result;
    }

    public function indexAction() {
        $result = array();
        return $result;
    }

    public function newAction() {
        $this->initAcl('create');
        $result         = $this->defaultTemplateVars();
        $result['form'] = new \Admin\Form\ServiceForm();
        $result['id']   = $this->id;
        return $result;
    }
    
    public function editAction() {
        $this->initAcl('update');
        $result             = $this->defaultTemplateVars();
        $result['id']       = $this->params('id');
        $result['entity']   = $model->get($result['id']);
        $result['table_id'] = $this->id;
        $result['form']     = new \Admin\Form\ServiceForm();
        $result['form']->setData($result['entity']);
        return $result;
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\ServiceForm();
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
            $inputFilter->add($factory->createInput(array(
                'name'     => 'link',
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
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
            $form->setInputFilter($inputFilter);
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $insert = $this->db->query('INSERT INTO Service (name, url, created, updated, hide) VALUES (:name, :url, :created, :updated, 0)')
                        ->execute(array(
                            'name' => $request->getPost('name'),
                            'url' => $request->getPost('link'),
                            'created' => time(),
                            'updated' => time()
                            ));
                if ($insert) {
                    return $this->redirect()->toRoute('admin', array(
                        'controller' => 'service',
                        'action' => 'new'
                    ));
                }
            }
        }
        
        $ModelService = '\\Admin\\Model\\' . SGBD . '\\' . 'Service';
        $service = new $ModelService($this->db);
        
        $vm = new ViewModel(array(
                    'form' => $form,
                    'services' => $service->getAll(),
                ));
        $vm->setTemplate('admin/service/new');
        return $vm;
    }

    public function showAction($id) {

        $row = $this->db
            ->query('SELECT s.* FROM service s WHERE l.project_id=:project ORDER BY l.id DESC')
            ->execute(array('project' => $this->user->project_id, 'id' => $id))
            ->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;

    }
    
    public function delete() {
        if ($this->project['user_id'] != $this->user->id) {
            die('User error.');
        }
        $request = $this->getRequest();
        $ids = $request->getPost('ids', array());
        foreach($ids as $key=>$value) {
            $this->db->query('DELETE FROM Service WHERE id=:id')->execute(array('id'=>$key));
        }
        
    }

}

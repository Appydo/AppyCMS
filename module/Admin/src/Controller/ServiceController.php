<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ServiceController extends AbstractActionController {

    public function indexAction() {
        return array();
    }

    public function newAction() {
        $request = $this->getRequest();
        if ($request->getPost()->get('ids')!='') {
            $this->delete();
        }

        $ModelService = '\\Admin\\Model\\' . SGBD . '\\' . 'Service';
        $service = new $ModelService($this->db);

        $form = new \Admin\Form\ServiceForm();
        
        return array(
            'services' => $service->getAll(),
            'form' => $form
        );
    }
    
    public function editAction() {
        $request = $this->getRequest();
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('default', array(
                'controller' => 'service',
                'action' => 'new'
            ));
        }
        
        $ModelService = '\\Admin\\Model\\' . SGBD . '\\' . 'Service';
        $service = new $ModelService($this->db);
        
        // $entity = $this->db->query('SELECT * FROM Service s WHERE s.id=:id')->execute(array('id' => $id))->current();
        
        return array(
            'form' => new \Admin\Form\ServiceForm(),
            'entity' => $entity->get($id)
        );
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
                $insert = $this->db->query('INSERT INTO Service (name, url, type, created, updated, hide) VALUES (:name, :url, :type, :created, :updated, 0)')
                        ->execute(array(
                            'name' => $request->getPost('name'),
                            'url' => $request->getPost('link'),
                            'type' => $request->getPost('type'),
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
        $query = $this->db->query('SELECT s.*
            FROM service s
            WHERE l.project_id=:project ORDER BY l.id DESC'
        );

        return array(
            'entities' => $query->execute(array('project' => $this->user->project_id, 'id' => $id))->current()
        );
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

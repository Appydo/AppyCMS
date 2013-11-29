<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ProjectController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $project = $request->getPost('select');
            $entity  = $this->db->query('UPDATE users SET project_id=:project WHERE id=:id')->execute(array('project' => $project, 'id' => $this->user->id));
        }
        
        if (isset($_GET['select'])) {
            $project = $_GET['select'];
            $entity  = $this->db->query('UPDATE users SET project_id=:project WHERE id=:id')->execute(array('project' => $project, 'id' => $this->user->id));
        }

        $query   = $this->db->query('SELECT p.* FROM Project p WHERE p.user_id=:author');
        $current = $this->db->query('SELECT project_id FROM users WHERE id=:id')->execute(array('id' => $this->user->id))->current();

        $layout = $this->layout();
        $layout->project = $this->db->query('SELECT p.* FROM Project p LEFT JOIN users u ON p.id=u.project_id WHERE u.id=:user')->execute(array('user'=>$this->user->id))->current();
        
        return array(
            'current' => $current['project_id'],
            'projects' => $query->execute(array('author' => $this->user->id))
        );
    }

    public function showAction($id) {
        $query = $this->db->query('SELECT p.*
            FROM Project p
            WHERE p.id=:id', array('id' => $id)
                )->fetch();

        return array(
            'entity' => $query
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
        $request = $this->getRequest();
        $id = $this->params('id');
        $entity = $this->db->query('SELECT p.* FROM Project p WHERE p.user_id=:user and p.id=:id'
                )->execute(array('id' => $id, 'user' => $this->user->id))->current();

        if (empty($entity)) {
            die('Project not found.');
        }

        return array(
            'form' => new \Admin\Form\ProjectForm(),
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
                        'UPDATE Project SET name=:name, description=:description, keywords=:keywords, url=:url, updated=:updated, comment=:comment, hide=:hide WHERE id=:id', array(
                    'name' => $request->getPost('name'),
                    'description' => $request->getPost('description'),
                    'keywords' => $request->getPost('keywords'),
                    'url' => $request->getPost('url'),
                    'updated' => time(),
                    'comment' => ($request->getPost('comment') == 'on') ? 1 : 0,
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                    'id' => $id
                        ));
                if ($update) {
                    $this->flashMessenger()->addSuccessMessage('Project modified');
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'project',
                                'action' => 'edit',
                                'id' => $id
                            ));
                } else {
                    $this->flashMessenger()->addErrirMessage('Project edit error');
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

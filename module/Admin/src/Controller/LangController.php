<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Admin\Lib\SimpleImage as SimpleImage;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class LangController extends AbstractActionController {

    private $title      = 'Lang';
    private $table      = 'Lang';
    private $controller = 'Lang';
    private $template   = 'admin/lang';
    private $id         = 'lang_id';
    private $select     = 'lang_id, lang_key, lang_value';
    private $module     = 'admin';
    private $table_row  = 20;

    private function initAcl($method) {
        $acl = new Acl();
        $roles = $this->db
            ->query('SELECT * FROM Role')
            ->execute();

        $allows = $this->db
            ->query('SELECT * FROM Allow WHERE controller=:controller and privilege=:privilege')
            ->execute(array(
                'controller' => $this->controller,
                'privilege'  => $method
                ));
        $denies = $this->db
            ->query('SELECT * FROM Deny WHERE controller=:controller and privilege=:privilege')
            ->execute(array(
                'controller' => $this->controller,
                'privilege'  => $method
                ));
        foreach ($roles as $role) {
            if (!empty($role['role_parent_id'])) {
                $acl->addRole(new Role($role['role_id']), $role['role_parent_id']);
            } else {
                $acl->addRole(new Role($role['role_id']));
            }
        }
        $acl->addResource(new Resource($this->controller));
        foreach ($allows as $allow) {
            $acl->allow($allow['role_id'], $deny['controller'], $deny['privilege']);
        }
        foreach ($denies as $deny) {
            $acl->deny($deny['role_id'], $deny['controller'], $deny['privilege']);
        }
        if (!$acl->isAllowed($this->user->role_id, $this->controller, $method)) {
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
        $this->initAcl('index');
        $request = $this->getRequest();

        // Init result var for template
        $result = $this->defaultTemplateVars();
        // Init form
        $result['form']  = $this->generateForm();

        // Init lib class
        $p = new \Admin\Lib\SimplePaginate();
        $f = new \Admin\Lib\SimpleFilter();
        $s = new \Admin\Lib\SimpleSearch();

        $result['sort']  = $model->sort($request);
        $result['order'] = $model->order($request);

        $result['order'] = (isset($_GET['order'])) ? stripslashes($_GET['order']) : '';
        $result['sort']  = (isset($_GET['sort']) and $_GET['sort']=='ASC') ? 'ASC' : 'DESC';
        // $order_string    = (!empty($result['order'])) ? 'ORDER BY '.$result['order'].' '.$result['sort'] : '';

        $result['entities'] = $model->getAll(\PDO::FETCH_ASSOC);
        
        $result['thead'] = true;
        $result['id']    = $this->id;
        $result['title'] = $this->title;
        $result['page']  = $page;

        return $result;
    }

    public function showAction() {
        $this->initAcl('show');
        // Init result var for template
        $result           = $this->defaultTemplateVars();

        $result['id']     = $this->params('id');
        $model            = new \Admin\Model\LangModel($this->db);
        $result['entity'] = $model->get($result['id']);

        return $result;
    }
    
    private function generateForm() {

        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        $columns = $metadata->getTable($this->table)->getColumns();

        $form = new \Admin\Form\LangForm($this->controller);

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

    public function newAction() {
        $this->initAcl('create');
        $result = $this->defaultTemplateVars();
        $result['form'] = $this->generateForm();
        $result['id']   = $this->id;
        return $result;
    }

    public function createAction() {
        $this->initAcl('create');
        $request = $this->getRequest();
        $form    = $this->generateForm();

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

                $id = $model->insert($insert_args);

                if (!empty($id)) {
                    $this->flashMessenger()->addSuccessMessage('Insert ok.');
                    return $this->redirect()->toRoute($this->module, array(
                            'controller' => $this->controller,
                            'action' => 'edit',
                            'id' => $id
                        ));
                }
            }
        }

        // Display form with error(s)
        $result = $this->newAction();
        $result['form'] = $form;
        $vm = new ViewModel($result);
        return $vm;
    }

    public function editAction() {
        $this->initAcl('update');
        $result             = $this->defaultTemplateVars();
        $result['id']       = $this->params('id');
        $result['entity']   = $model->get($result['id']);
        $result['table_id'] = $this->id;
        $result['form']     = $this->generateForm();
        $result['form']->setData($result['entity']);

        return $result;

    }

    public function updateAction() {
        $this->initAcl('update');

        $request = $this->getRequest();
        $id      = $this->params('id');
        $entity  = $model->get($id);
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
                $update_args['updated'] = time();
                $update_args[$this->id] = $id;

                $id = $model->update($update_args);

                if (!empty($id)) {
                    $this->flashMessenger()->addSuccessMessage('Update ok.');
                    return $this->redirect()->toRoute($this->module, array(
                            'controller' => $this->controller,
                            'action' => 'edit',
                            'id' => $id
                        ));
                }
            }
        }

        // Display form with error(s)
        $result = $this->editAction();
        $result['form'] = $form;
        $vm = new ViewModel($result);
        return $vm;
    }

    public function deleteAction() {
        $this->initAcl('delete');

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($model->delete($request->getPost('action'))==0) {
                $this->flashMessenger()->addErrorMessage('Error : no row deleted.');
            } else {
                $this->flashMessenger()->addSuccessMessage($count.' row(s) deleted.');
            }
        } else {
            $this->flashMessenger()->addErrorMessage('Delete error : no data found.');
        }
        return $this->redirect()
            ->toRoute($this->module, array(
                'controller' => $this->controller,
            ));;
    }

}

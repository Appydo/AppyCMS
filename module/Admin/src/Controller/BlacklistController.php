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

class BlacklistEmailController extends AbstractActionController {

    private $title      = 'Blacklist Email';
    private $table      = 'BlacklistEmail';
    private $controller = 'BlacklistEmail';
    private $template   = 'admin/blacklist-email';
    private $id         = 'be_id';
    private $select     = 'be_id, be_email, created';
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

        // Init result var for template
        $result = $this->defaultTemplateVars();
        $result['id']    = $this->params('id');
        $result['form']  = $this->generateForm();

        if (isset($_GET['page']))
            $page = $_GET['page'];
        else
            $page  = 1;
        if (isset($_GET['move'])) {
            if ($_GET['move']=='next') {
                $page++;
            } elseif ($_GET['move']=='prev' and $page!=1) {
                $page--;
            }
        }
        if (empty($page)) $page = 0;
        $nb    = $this->table_row;
        $start = ($page * $nb) - $nb;

        $result['order'] = (isset($_GET['order'])) ? stripslashes($_GET['order']) : '';
        $result['sort']  = (isset($_GET['sort']) and $_GET['sort']=='ASC') ? 'ASC' : 'DESC';
        $order_string    = (!empty($result['order'])) ? 'ORDER BY '.$result['order'].' '.$result['sort'] : '';

        $where_string = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('action_submit') == '1') {
                if ($request->getPost('action_select') == 'delete') {
                    foreach ($request->getPost('action') as $action) {
                        $this->delete($action);
                    }
                }
                return $this->redirect()
		            ->toRoute($this->module, array(
		                'controller' => $this->controller,
		            ));;
            } elseif ($request->getPost('search_submit')) {
                $result['form']->setData($request->getPost());
                foreach($request->getPost() as $key=>$value) {
                    if($key!='search_submit' and !empty($value))
                        $where[] = $key.'="'.$value.'"';
                }
                if (!empty($where)) $where_string = 'WHERE '.implode(' and ',$where);
            }
        }

        $stmt = $this->db->createStatement('SELECT '.$this->select.' FROM '.$this->table.' '.$where_string.' '.$order_string.' LIMIT '.$start.','.$nb);

        $result['entities'] = $stmt
                ->execute(array())
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);
        
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

        $form = new \Admin\Form\BlacklistForm($this->controller);

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

                $insert = $this->db->query(
                    "INSERT INTO ".$this->table." (".implode(",", array_keys($insert_args)).")
                    VALUES (:".implode(",:", array_keys($insert_args)).")",
                        $insert_args
                        );
                
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
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
        $vm->setTemplate($this->template.'/new');
        return $vm;
    }

    public function editAction() {
        $this->initAcl('update');
        $result             = $this->defaultTemplateVars();
        $result['id']       = $this->params('id');
        $result['entity']   = $this->db
            ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
            ->execute(array('id'=>$result['id']))
            ->current();
        $result['table_id'] = $this->id;
        $result['form']     = $this->generateForm();
        if (empty($result['entity'])) {
            throw new \Exception("Could not find row {$result['id']}");
        }
        $result['form']->setData($result['entity']);

        return $result;

    }

    public function updateAction() {
        $this->initAcl('update');

        $request = $this->getRequest();
        $id      = $this->params('id');
        $entity  = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
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

        // Display form with error(s)
        $result = $this->editAction();
        $result['form'] = $form;
        $vm = new ViewModel($result);
        return $vm;
    }

    public function delete($id) {
        $this->initAcl('delete');

        if (!empty($id)) {
        	if (is_numeric($id)) { 
                $delete = $this->db
                    ->query('DELETE FROM '.$this->table.' WHERE '.$this->id.'=:id')
                    ->execute(array('id' => $id));
            } else {
            	$delete = 0;
            }
            if ($delete==0) {
                $this->flashMessenger()->addErrorMessage('Error : no row deleted.');
            } else {
                $this->flashMessenger()->addSuccessMessage('Row '.$id.' deleted.');
            }
        } else {
            $this->flashMessenger()->addErrorMessage('Delete error : no data found.');
        }
    }

}

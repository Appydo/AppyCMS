<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

use Zend\Form\Form;
use Zend\Form\Element;

class LogController extends AbstractActionController {

	private $title      = 'Log';
    private $table      = 'Log';
    private $controller = 'Log';
    private $template   = 'admin/log';
    private $id         = 'log_id';
    private $select     = 'log_id';
    private $module     = 'admin';
    private $table_row  = 20;

    private function defaultTemplateVars() {
        $result = array();
        $result['primary_id'] = $this->id;
        $result['controller'] = $this->controller;
        $result['module']     = $this->module;
        return $result;
    }

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

    public function indexAction() {
    	$this->initAcl('index');

    	$result = array();

        $result['entities'] = $this->db
        	->query('SELECT l.* FROM Log l ORDER BY l.id DESC')
        	->execute();

        return $result;
    }

    private function generateForm() {
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        $columns = $metadata->getTable($this->table)->getColumns();

        $form = new Form($this->controller);

        $element = new Element('description');
        $element->setLabel('Description');
        $element->setAttributes(array(
            'type'  => 'textarea'
        ));
        $form->add($element);

        $csrf = new Element\Csrf('csrf');
        $form->add($csrf);

        $send = new Element('submit');
        $send->setValue('Submit');
        $send->setAttributes(array(
            'type'  => 'submit'
        ));
        $form->add($send);

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

    public function editAction() {

        $result             = $this->defaultTemplateVars();
        $result['id']       = $this->params('id');
        $result['entity']   = $this->db->query('SELECT *
            FROM '.$this->table.'
            WHERE id=:id', array('id' => $result['id'])
            )->current();
        if (empty($result['entity'])) {
            throw new \Exception("Could not find row {$result['id']}");
        }
        $result['table_id'] = $this->id;
        $result['form']     = $this->generateForm();
        $result['form']->setData($result['entity']);

        return $result;

    }

    public function emptyAction() {
        $this->db
            ->query('DELETE FROM Log')
            ->execute();
        $this->flashMessenger()->addSuccessMessage('Empty Log.');
        return $this->redirect()->toRoute('admin', array(
                'controller' => 'log'
            ));
    }
}

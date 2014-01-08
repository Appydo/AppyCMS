<?php
namespace Admin\Lib;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Admin\Lib\SimpleImage as SimpleImage;

use Zend\Form\Form;
use Zend\Form\Element;

class BaseController extends AbstractActionController {

    private $title      = 'Base';
    private $table      = 'Base';
    private $controller = 'Base';
    private $form       = 'BaseForm';
    private $template   = 'admin/base';
    private $id         = 'id';
    private $select     = 'id';
    private $module     = 'admin';
    private $table_row  = 20;

    protected function initAcl($method) {
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

    protected function defaultTemplateVars() {
        $result = array();
        $result['primary_id'] = $this->id;
        $result['controller'] = $this->controller;
        $result['module']     = $this->module;
        return $result;
    }

    protected function generateForm() {
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        $columns  = $metadata->getTable($this->table)->getColumns();
        $form     = new \Admin\Form\{$this->form}($this->controller);

        // Generate text input
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

}

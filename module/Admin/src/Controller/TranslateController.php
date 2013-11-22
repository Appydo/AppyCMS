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

class TranslateController extends AbstractActionController {

    private $title      = 'Translate';
    private $table      = 'Translate';
    private $controller = 'Translate';
    private $template   = 'immobilier-en-reseau/translate';
    private $id         = 'translate_id';
    private $select     = 'translate_id, translate_key';
    private $module     = 'admin';
    private $table_row  = 20;

    private function defaultTemplateVars() {
        $result = array();
        $result['primary_id'] = $this->id;
        $result['controller'] = $this->controller;
        $result['module']     = $this->module;
        return $result;
    }    
    public function indexAction() {
        
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
                        return $this->deleteAction($action);
                    }
                }
            } elseif ($request->getPost('search_submit')) {
                $result['form']->setData($request->getPost());
                foreach($request->getPost() as $key=>$value) {
                    if($key!='search_submit' and !empty($value))
                        $where[] = $key.'="'.$value.'"';
                }
                if (!empty($where)) $where_string = 'WHERE '.implode(' and ',$where);
            }
        }

        if(!empty($result['id'])) {
            $where_string = 'WHERE lang_id='.$result['id'];
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
        // Init result var for template
        $result           = $this->defaultTemplateVars();
        $result['id']     = $this->params('id');
        $result['entities'] = $this->db
                ->query('SELECT * FROM '.$this->table)
                ->execute(array());
        return $result;
    }
    
    private function generateForm() {
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        $columns = $metadata->getTable($this->table)->getColumns();

        $form = new Form($this->controller);

        $element = new Element('translate_id');
        $element->setLabel('ID');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
        $form->add($element);

        $element = new Element('lang_id');
        $element->setLabel('Lang');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
        $form->add($element);

        $element = new Element('translate_key');
        $element->setLabel('Key');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
        $form->add($element);

        $element = new Element('translate_value');
        $element->setLabel('Value');
        $element->setAttributes(array(
            'type'  => 'text'
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

    public function newAction() {
        $result = $this->defaultTemplateVars();
        $result['form'] = $this->generateForm();
        $result['id']   = $this->id;
        return $result;
    }

    public function createAction() {
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

        $result = $this->newAction();
        $result['form'] = $form;
        $vm = new ViewModel($result);
        $vm->setTemplate('immobilier-en-reseau/partner/new');
        return $vm;
    }

    public function editAction() {

        $result              = $this->defaultTemplateVars();
        $result['entity_id'] = $this->params('id');
        $result['id']        = $this->id;
        $result['form']      = $this->generateForm();
        $result['entity']    = $this->db
            ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
            ->execute(array('id'=>$result['entity_id']))
            ->current();
        $result['form']->setData($result['entity']);
        if (empty($result['entity'])) {
            die($this->table.' not found.');
        }

        return $result;
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');
        
        $dir = ROOT_PATH . '/public/uploads/diagnostic/';

        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
                ->execute(array('id'=>$id))
                ->current();

        if (empty($entity)) {
            die('Entity not found.');
        }

        $form = $this->generateForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                if (!file_exists($dir . '/'))
                    mkdir($dir . '/');
                
                $imgs = array(
                    $_FILES['diagnostic_image']
                );
                foreach($imgs as $img) {
                    if (!empty($img)) {
                        move_uploaded_file($img['tmp_name'], $dir.$img['name']);

                        $image = new SimpleImage();
                        $image->load($dir.$img['name']);
                        if ($image->getWidth()) {
                            $image->resizeToWidth(250);
                            if ($image->getHeight() > 250) {
                                $image->resizeToHeight(250);
                            }
                            $image->save($dir.$img['name']);
                        }
                    }
                }
                
                $update_args = array();
                foreach($form as $element) {
                    if(!in_array($element->getName(),array($this->id, 'csrf', 'submit')))
                        $update_args[$element->getName()] = $request->getPost($element->getName());
                }
                $update_set = substr($update_set, 0, -1);
                $update_args['diagnostic_image'] = $_FILES['diagnostic_image']['name'];
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
                if ($update) {
                    return $this->redirect()->toRoute($this->module, array(
                                'controller' => $this->controller,
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $result = $this->editAction();
        $result['form'] = $form;
        $vm = new ViewModel($result);
        $vm->setTemplate('immobilier-en-reseau/partner/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                if (is_numeric($action) and $action!=0) { 
                    $this->db
                        ->query('DELETE FROM '.$this->table.' WHERE '.$this->id.'=:id')
                        ->execute(array('id' => $action));
                }
            }
        }
        return $this->redirect()->toRoute($this->module, array(
                'controller' => $this->controller,
            ));;
    }

}

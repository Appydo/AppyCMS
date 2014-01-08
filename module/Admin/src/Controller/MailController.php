<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Form\Form;
use Zend\Form\Element;

class MailController extends AbstractActionController {

    private $title      = 'Message';
    private $table      = 'Mail';
    private $controller = 'Mail';
    private $template   = 'admin/mail';
    private $id         = '';
    private $select     = '';
    private $module     = 'admin';
    private $table_row  = 20;

    private function defaultTemplateVars() {
        $result = array();
        $result['primary_id'] = $this->id;
        $result['controller'] = $this->controller;
        $result['module']     = $this->module;
        return $result;
    }
    
    public function indexAction()
    {

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

        $where_string = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('query') != '') {
                $where = array();
                $query = $request->getPost('query');
                $metadata = new \Zend\Db\Metadata\Metadata($this->db);
                $columns  = $metadata->getTable('Mail')->getColumns();
                foreach($columns as $column) {
                    // echo $column->getDataType();
                    if ($column->getDataType()=='text' or $column->getDataType()=='varchar') {
                        $where[] = 'm.'.$column->getName().' LIKE "%'.$query.'%"';
                    }
                }
                $where[] = 'u.username LIKE "%'.$query.'%"';
                $where[] = 'u.firstname LIKE "%'.$query.'%"';
                if (!empty($where)) $where_string = 'and '.implode(' or ',$where);
                $stmt = $this->db->createStatement('SELECT * FROM '.$this->table);
            } elseif ($request->getPost('action_submit') == '1') {
                if ($request->getPost('action_select') == 'delete') {
                    // die(var_dump($request->getPost('action')));
                    foreach ($request->getPost('action') as $action) {
                        return $this->deleteAction($action);
                    }
                }
            }
        }

        $query   = $this->db->query('SELECT m.*, u.username as author_lastname, u.id as author_id, f.id as from_id, u.firstname as author_firstname, f.username as from_lastname, f.firstname as from_firstname
            FROM Mail m
            LEFT JOIN users f ON m.from_id=f.id
            LEFT JOIN users u ON m.user_id=u.id
            WHERE m.project_id=:project '.$where_string.' ORDER BY m.id DESC LIMIT '.$start.','.$nb);

        return array(
            'entities' => $query->execute(array('project' => $this->project['id'])),
            'page' => $page,
            'sort' => $sort,
            'order' => $order
        );
    }

    public function trashAction()
    {
        $query   = $this->db->query('SELECT t.*, u.username as author
            FROM Mail m
            LEFT JOIN users u on t.user_id=u.id
            WHERE t.project_id=:current and t.user_id=:user and t.hide=1 ORDER BY t.id ASC',
            array('user' => $this->user->id, 'current' => $this->user->project_id)
        );
        
        return array(
            'topics' => $query->execute()
        );
    }

    private function generateForm() {
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        $columns = $metadata->getTable($this->table)->getColumns();

        $form = new Form($this->controller);
        foreach ($columns as $column) {
            if ($column->getName() == 'message') {
                $element = new Element($column->getName());
                $element->setLabel('Message');
                $element->setAttributes(array(
                    'type' => 'textarea',
                    'style' => 'width:400px;height: 100px;'
                ));
                $form->add($element);
            } elseif (!in_array($column->getName(), array('created', 'updated', 'from_id', 'user_id', 'hide', 'project_id', 'ip', 'name'))) {
                $element = new Element($column->getName());
                $element->setLabel($column->getName());
                $element->setAttributes(array(
                    'type' => 'text'
                ));
                $form->add($element);
            }
        }
        $csrf = new Element\Csrf('csrf');
        $form->add($csrf);

        $send = new Element('submit');
        $send->setValue('Submit');
        $send->setAttributes(array(
            'type' => 'submit'
        ));
        $form->add($send);
        return $form;
    }

    public function newAction() {
        // $this->initAcl('create');
        $result = $this->defaultTemplateVars();
        $result['form'] = $this->generateForm();
        $result['id']   = $this->id;
        return $result;
    }

    public function createAction() {
        $id = $this->params('id');
        $request = $this->getRequest();
        $form = $this->generateForm();

        if ($request->isPost()) {

            $form->setData($request->getPost());
            if ($form->isValid()) {
                $insert_args = array();
                foreach ($form as $element) {
                    if (!in_array($element->getName(), array($this->id, 'csrf', 'submit')))
                        $insert_args[$element->getName()] = $request->getPost($element->getName());
                }

                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }

                $insert_args['name']    = $this->user->username . ' ' . $this->user->firstname;
                $insert_args['from_id'] = $this->user->id;
                $insert_args['project_id'] = $this->user->project_id;
                $insert_args['created'] = time();
                $insert_args['updated'] = time();
                $insert_args['ip']      = $ip;
                $insert_args['hide']    = 0;
                $insert_args['view']    = 0;
                $insert_args['user_id'] = $id;

                $insert = $this->db->query(
                        "INSERT INTO " . $this->table . " (" . implode(",", array_keys($insert_args)) . ")
                    VALUES (:" . implode(",:", array_keys($insert_args)) . ")", $insert_args
                );

                if ($insert) {
                    /*
                    $message_id = $this->db->getDriver()->getLastGeneratedValue();
                    $entity = $this->db
                        ->query('SELECT * FROM users WHERE id=:id')
                        ->execute(array('id' => $id))
                        ->current();
                    $mail = new \Admin\Services\Sendmail($this->db);
                    $vars = array();
                    $vars['firstname'] = $entity['firstname'];
                    $vars['lastname'] = $entity['username'];
                    $vars['user_id'] = $this->user->id;
                    $vars['from_firstname'] = $this->user->firstname;
                    $vars['from_lastname'] = $this->user->username;
                    $vars['message'] = $request->getPost('message');
                    $mail->sendEmailId($id, $this->user->id, 24, $vars);
                    $vars['firstname'] = $this->user->firstname;
                    $vars['lastname'] = $this->user->username;
                    $mail->sendEmailId($this->user->id, 1, 25, $vars);
                    */
                    $this->flashMessenger()->addSuccessMessage('The message was sent successfully.');
                    return $this->redirect()->toRoute($this->module, array(
                                'controller' => $this->controller,
                                'action' => 'index',
                                // 'id' => $message_id
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

    public function editAction()
    {
        $request   = $this->getRequest();
        $id = $request->getParam('id');

        $this->view->entity = $this->db->query('SELECT * FROM Mail m WHERE m.id=:id and m.project_id=:project', array('id' => $id, 'project' => $this->user->project_id))->fetch();

        $dir = realpath(APPLICATION_PATH . '/../public/uploads/'.$this->user->project_id);

        if (!is_dir($dir)) {
            @mkdir($dir);
            $this->view->dir_exists = is_dir($dir);
        } else {
            $this->view->dir_exists = true;
        }
        
        $tab   = array();
        $sizes = array();
        if ($this->view->dir_exists) {

            if ($handle = opendir($dir)) {

                while (false !== ($entry = readdir($handle))) {
                    if (!in_array($entry, array(".", ".."))) {
                        array_push($tab, $entry);
                        array_push($sizes, round(filesize($dir . '/' . $entry) / 1024));
                    }
                }

                closedir($handle);
            }
        }

        $this->view->listFiles  = $tab;
        $this->view->sizes      = $sizes;
    }
    
    public function showAction()
    {
        $request = $this->getRequest();
        $id = $this->params('id');
        $result = $this->defaultTemplateVars();
        $result['form'] = $this->generateForm();
        $result['entity'] = $this->db->query('SELECT * FROM Mail m WHERE m.id=:id and m.project_id=:project')->execute(array('id' => $id, 'project' => $this->user->project_id))->current();
        return $result;
    }

    public function updateAction()
    {
        $request   = $this->getRequest();
        $id = $request->getParam('id');
        $this->view->entity = $this->db->query('SELECT * FROM Topic t WHERE t.id=:id and t.project_id=:project', array('id' => $id, 'project' => $this->user->project_id))->fetch();
        $this->view->topics = $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project', array('project' => $this->user->project_id))->fetchAll();
        
        if (!$this->view->entity) {
            die('Unable to find Topic entity.');
        }

        $request = $this->getRequest();
        $form = new Admin_Form_Topic($_POST);
        if ($request->isPost() and $form->isValid($request->getPost())) {
            $update = $this->db->update("Topic", array(
                'name'       => $request->getParam('name'),
                'content'    => $request->getParam('content'),
                'topic_id'   => ($request->getParam('parent')==0)?null:$request->getParam('parent'),
                'updated'    => time(),
                'user_id'    => $this->user->id,
                'project_id' => $this->user->project_id,
                'comment'    => ($request->getParam('comment')=='on') ? 1 : 0,
                'hide'       => ($request->getParam('hide')=='on') ? 1 : 0
            ),'id='.$id);
            if($update) {
                $this->_helper->redirector('edit', 'topic', 'admin', array('id'=>$id));
            }
        }

        $this->view->form   = $form;
        $this->render('edit', null, true);
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        $id      = $request->getParam('id');
        $form    = new Admin_Form_Topic($_POST);
        if ($request->isPost() and $form->isValid($request->getPost())) {
            
            $entity = $this->db->query('SELECT * FROM topic t WHERE t.id=:id and t.user_id=:user',array('id' => $id, 'user' => $this->user->id))->fetch();

            if (!$entity) {
                die('Unable to find Topic entity.');
            }

            $this->db->query('DELETE FROM bugs WHERE id=:id')->execute(array('id'=>$id));
            // $log = new Log("Delete topic", "Topic ".$entity->getId()." (".$entity->getName().")", $user);
        }

        $this->_helper->redirector('index', 'topic', 'admin');
    }

}

<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MailController extends AbstractActionController {
    
    public function indexAction()
    {
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
            WHERE m.project_id=:project '.$where_string.' ORDER BY m.id DESC'
            );
        return array(
            'entities' => $query->execute(array('project' => $this->project['id']))
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

    public function newAction()
    {
        // $this->view->form = new \Admin\Form\MailForm();
        return array();
    }

    public function createAction()
    {
        $request   = $this->getRequest();
        $form = new \Admin\Form\MailForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $insert = $this->db->query("INSERT INTO Mail (name, content, created, updated, user_id, project_id, topic_id, hide)
                    VALUES (:name, :content, :created, :updated, :user_id, :project_id, :topic_id, :hide)", array(
                    'name'    => $request->getParam('name'),
                    'content' => $request->getParam('content'),
                    'created' => time(),
                    'updated' => time(),
                    'user_id' => $this->user->id,
                    'project_id' => $this->user->project_id,
                    'topic_id'   => ($request->getParam('parent')==0)?null:$request->getParam('parent'),
                    'hide'    => ($request->getParam('hide')=='on') ? 1 : 0
                ));
                if($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    $this->_helper->redirector('edit', 'topic', 'admin', array('id'=>$id));
                }
            }
        }
        $this->view->form = $form;
        $this->render('new', null, true);
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

        $entity = $this->db->query('SELECT * FROM Mail m WHERE m.id=:id and m.project_id=:project')->execute(array('id' => $id, 'project' => $this->user->project_id))->current();
        return array(
            'entity' => $entity
        );
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

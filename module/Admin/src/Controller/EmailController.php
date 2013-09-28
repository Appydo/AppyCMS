<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class EmailController extends AbstractActionController {

    public function indexAction() {
        
        $query = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic t, Topic p
            LEFT JOIN users tu on t.user_id=tu.id
            WHERE t.lft BETWEEN p.lft AND p.rgt
            and t.hide=0 '.$parent.' and t.project_id=:project and p.project_id=:project
            GROUP BY t.id
            ORDER BY t.lft'
        );
        return array(
            'parent_id' => $parent_id,
            'topics' => $query->execute(array('project' => $this->project['id']))
        );
    }

    public function trashAction() {
        $query = $this->db->query('SELECT
            t.*, u.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments
            FROM Topic t
            LEFT JOIN users u on t.user_id=u.id
            WHERE t.project_id=u.project_id and t.user_id=:user and t.hide=1 ORDER BY t.id ASC'
        );

        return array(
            'topics' => $query->execute(array('user' => $this->user->id))
        );
    }

    public function linkAction() {
        return array(
            'form' => new \Admin\Form\LinkForm()
        );
    }
    
    private function isFree($title) {
        
        $test = $this->db
                ->query('SELECT count(t.id)
                    FROM Topic t LEFT JOIN users u on t.user_id=u.id
                    WHERE name=:title and t.project_id=u.project_id and t.user_id=:user')
                ->execute(array('title'=>$title, 'user' => $this->user->id))
                ->current();
        return ($test['count(t.id)']>0);
    }

    public function newAction() {

        $dir = __DIR__ . '/../../../../public/uploads/' . $this->user->project_id;

        $topics = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic t, Topic p
            LEFT JOIN users u on t.project_id=u.project_id
            LEFT JOIN users tu on t.user_id=tu.id
            WHERE t.lft BETWEEN p.lft AND p.rgt
            and u.id=:user and t.hide=0
            GROUP BY t.id
            ORDER BY t.lft'
        )->execute(array('user' => $this->user->id));

        if (!is_dir($dir)) {
            @mkdir($dir);
            $dir_exists = is_dir($dir);
        } else {
            $dir_exists = true;
        }

        $tab = array();
        $sizes = array();
        if ($dir_exists) {

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

        $prefix = 'page_';
        $i = 1;
        while ($this->isFree($prefix . $i)) {
            $i++;
        }
        
        // $this->view->form       = new Admin_Form_Topic();

        $vm = new ViewModel(array(
                    'title' => $prefix . $i,
                    'form' => new \Admin\Form\TopicForm(),
                    'size' => $sizes,
                    'listFiles' => $tab,
                    'dir_exists' => $dir_exists,
                    'topics' => $topics
                ));
        $vm->setTemplate('admin/topic/tinymce/new');
        return $vm;
    }

    public function createAction() {
        $topics = $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project')->execute(array('project' => $this->user->project_id));
        $request = $this->getRequest();
        $form = new \Admin\Form\TopicForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                
                if ($request->getPost('parent')) {
                    $position = $this->db->query('
                    SELECT rgt-1 as rgt FROM Topic WHERE id = :id
                    ')->execute(array('id'=>$request->getPost('parent')))->current();
                } else {
                    $position = $this->db->query('
                    SELECT MAX(rgt) FROM Topic
                    ')->execute()->current();
                }
                
                $this->db->query('
                    UPDATE Topic SET rgt = rgt + 2 WHERE rgt > :rgt
                    ')->execute(array('rgt' => $position['rgt']));
                $this->db->query('
                    UPDATE Topic SET lft = lft + 2 WHERE lft > :rgt
                    ')->execute(array('rgt' => $position['rgt']));
                
                
                $user   = $this->db->query('SELECT project_id FROM users WHERE id=:user_id')->execute(array('user_id'=>$this->user->id))->current();
                $insert = $this->db->query('
                    INSERT INTO Topic (name, content, created, updated, user_id, project_id, topic_id, lft, rgt, hide)
                    VALUES (:name, :content, :created, :updated, :user_id, :project_id, :topic_id, :lft, :rgt, :hide)
                    ')->execute(array(
                        'name' => $request->getPost('name'),
                        'content' => $request->getPost('content'),
                        'created' => time(),
                        'updated' => time(),
                        'user_id' => $this->user->id,
                        'project_id' => $user['project_id'],
                        'topic_id' => ($request->getPost('parent') == 0) ? null : $request->getPost('parent'),
                        'lft' => $position['rgt'] + 1,
                        'rgt' => $position['rgt'] + 2,
                        'hide' => ($request->getPost('hide') == 'on') ? 1 : 0
                        ));
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                        'controller' => 'topic',
                        'action' => 'edit',
                        'id' => $id
                    ));
                }
            }
        }
        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/topic/tinymce/new');
        return $vm;
    }

    public function linkCreateAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\LinkForm();
        if ($request->isPost() and $form->isValid($request->getPost())) {
            $insert = $this->db->insert("Link", array(
                'name' => $request->getPost('name'),
                'link' => $request->getParam('link'),
                'created' => time(),
                'updated' => time(),
                'user_id' => $this->user->id,
                'project_id' => $this->user->project_id,
                'hide' => ($request->getParam('hide') == 'on') ? 1 : 0
                    ));
            if ($insert) {
                $id = $this->db->getDriver()->getLastGeneratedValue();
                $this->_helper->redirector('edit', 'topic', 'admin', array('id' => $id));
            }
        }
        $this->view->form = $form;
        return array(
            'form' => $form
        );
    }

    public function editAction() {
        $request = $this->getRequest();
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $entity = $this->db->query('SELECT t.* FROM Topic t, users u WHERE t.id=:id and u.id=:user and t.project_id=u.project_id')->execute(array('id' => $id, 'user' => $this->user->id))->current();
        $topics = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic t, Topic p
            LEFT JOIN users tu on t.user_id=tu.id
            WHERE t.lft BETWEEN p.lft AND p.rgt
            and t.hide=0 and t.project_id=:project and p.project_id=:project
            GROUP BY t.id
            ORDER BY t.lft'
        )->execute(array('project' => $this->project['id']));

        $dir = realpath(__DIR__ . '/../../../../public/uploads/' . $this->user->project_id);

        if (!is_dir($dir)) {
            @mkdir($dir);
            $dir_exists = is_dir($dir);
        } else {
            $dir_exists = true;
        }

        $tab = array();
        $sizes = array();
        if ($dir_exists) {

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
        $vm = new ViewModel(array(
                    'form' => new \Admin\Form\TopicForm(),
                    'listFiles' => $tab,
                    'sizes' => $sizes,
                    'dir_exists' => $dir_exists,
                    'entity' => $entity,
                    'topics' => $topics
                ));
        $vm->setTemplate('admin/topic/tinymce/edit');
        return $vm;
    }

    public function updateAction() {
        $request = $this->getRequest();
        $id = $this->params('id');
        $user   = $this->db->query('SELECT project_id FROM users WHERE id=:user_id')->execute(array('user_id'=>$this->user->id))->current();
        $entity = $this->db->query('SELECT * FROM Topic t WHERE t.id=:id and t.project_id=:project')->execute(array('id' => $id, 'project' => $user['project_id']))->current();
        $topics = $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project')->execute(array('project' => $user['project_id']));

        if (!$entity) {
            die('Unable to find Topic entity.');
        }

        $form = new \Admin\Form\TopicForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update = $this->db->query('
                        UPDATE Topic SET
                        name=:name, content=:content, updated=:updated,
                        user_id=:user_id, project_id=:project_id, topic_id=:topic_id, hide=:hide,
                        comment=:comment
                        WHERE id=:id
                    ')->execute(array(
                        'name' => $request->getPost('name'),
                        'content' => $request->getPost('content'),
                        'topic_id' => ($request->getPost('parent') == 0) ? null : $request->getPost('parent'),
                        'updated' => time(),
                        'user_id' => $this->user->id,
                        'project_id' => $user['project_id'],
                        'comment' => ($request->getPost('comment') == 'on') ? 1 : 0,
                        'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                        'id' => $id
                    ));
                if ($update) {
                    $insert = $this->db->query('
                        INSERT INTO Topic_archive
                        SELECT * FROM Topic WHERE id=:id
                    ')->execute(array(
                        'id' => $id
                    ));
                    return $this->redirect()->toRoute('admin', array(
                        'controller' => 'topic',
                        'action' => 'edit',
                        'id' => $id
                    ));
                }
            }
        }

        $dir = realpath(__DIR__ . '/../../../../public/uploads/' . $user['project_id']);

        if (!is_dir($dir)) {
            @mkdir($dir);
            $dir_exists = is_dir($dir);
        } else {
            $dir_exists = true;
        }

        $tab = array();
        $sizes = array();
        if ($dir_exists) {

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
        $vm = new ViewModel(array(
            'dir_exists' => $dir_exists,
            'sizes' => $sizes,
            'listFiles' => $tab,
            'form' => $form,
            'entity' => $entity
        ));
        $vm->setTemplate('admin/topic/tinymce/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $form = new \Admin\Form\TopicForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {

                $entity = $this->db->query('SELECT * FROM topic t WHERE t.id=:id and t.user_id=:user', array('id' => $id, 'user' => $this->user->id))->fetch();

                if (!$entity) {
                    die('Unable to find Topic entity.');
                }

                $this->db->delete('bugs', 'id=' . $id);
                // $log = new Log("Delete topic", "Topic ".$entity->getId()." (".$entity->getName().")", $user);
            }
        }

        $this->_helper->redirector('index', 'topic', 'admin');
    }

}

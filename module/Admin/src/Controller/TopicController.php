<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Lib\Slug;

class TopicController extends AbstractActionController {

    public function indexAction() {

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
        $nb    = 20;
        $start = ($page * $nb) - $nb;
        if (isset($_GET['order']))
            $order = $_GET['order'];
        else
            $order = '';
        if(isset($_GET['sort']) and $_GET['sort']=='ASC')
            $sort  = 'ASC';
        else
            $sort  = 'DESC';
        
        if (!empty($order)) $order_string = 'ORDER BY u.'.$order.' '.$sort;
        else $order_string = '';

        // Sub category
        if($this->params('id')!='' and is_numeric($this->params('id'))) {
            $parent = 'and t.topic_id='.$this->params('id');
            $parent_id = $this->params('id');
            $parent_id = $this->db->query(
                    'SELECT t.id, t.name, topic_id, (SELECT COUNT(c.id)
                        FROM Comment as c
                        WHERE c.topic_id=t.id) as comments,
                        t.created, t.updated, language, content, user_id, tu.username as author
                     FROM Topic t
                     LEFT JOIN users u on t.project_id=u.project_id
                     LEFT JOIN users tu on t.user_id=tu.id
                     WHERE u.id=:user and t.hide=0 and t.id=:topic_id')
                    ->execute(array('topic_id'=>$parent_id, 'user' => $this->user->id))
                    ->current();
        } else {
            $parent = 'and t.topic_id is NULL';
            $parent_id = null;
        }
        
        if (isset($_GET['reindex'])) {
            $this->reIndexNested();
        }

        $where_string = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('query') != '') {
                $where = array();
                $query = $request->getPost('query');
                $metadata = new \Zend\Db\Metadata\Metadata($this->db);
                $columns  = $metadata->getTable('Topic')->getColumns();
                foreach($columns as $column) {
                    if ($column->getDataType()=='text' or $column->getDataType()=='varchar') {
                        $where[] = $column->getName().' LIKE "%'.$query.'%"';
                    }
                }
                if (!empty($where)) $where_string = 'and t.'.implode(' or t.',$where);
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
        
        $parent = '';
        
        $query = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic t, Topic p, users tu
            WHERE t.lft BETWEEN p.lft AND p.rgt AND t.user_id=tu.id '.$where_string.' 
            and t.hide=0 '.$parent.' and t.project_id=:project and p.project_id=:project
            GROUP BY t.id ORDER BY t.lft LIMIT '.$start.','.$nb
        );

        return array(
            'where' => (!empty($where_string)),
            'parent_id' => $parent_id,
            'topics' => $query->execute(array('project' => $this->project['id'])),
            'order' => $order,
            'sort' => $sort,
            'page' => $page,
        );
    }

    public function deleteAllAction() {
        $request = $this->getRequest();
        if ($request->getPost('delete_all',0)==1) {
            $this->flashMessenger()->addSuccessMessage('The trash is empty.');
            $this->db
                ->query('DELETE FROM Topic WHERE hide=1')
                ->execute();
        }
        return $this->redirect()->toRoute('admin', array(
                        'controller' => 'topic',
                        'action' => 'trash'
                    ));
    }

    public function trashAction() {
        $request = $this->getRequest();
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

	public function reAddNested($id) {
		$topic = $this->db->query('SELECT * FROM Topic WHERE id=:id')->execute(array('id'=>$id))->current();
		if ($topic['topic_id']>$topic['id']) {
			$this->reAddNested($topic['topic_id']);
		}
		if ($topic['lft']==0) {
			$position = $this->addNested($topic['topic_id'], $topic['project_id']);
			$this->db->query('UPDATE Topic SET lft=:lft, rgt=:rgt WHERE id=:id')->execute(array(
	            'id'  => $topic['id'],
	            'lft' => $position + 1,
	            'rgt' => $position + 2,
	            ));
		}
	}
    
    public function reIndexNested() {
        $topics = $this->db->query('SELECT id FROM Topic ORDER BY id')->execute();
        $project = array();
        $this->db->query('UPDATE Topic SET lft=0, rgt=0')->execute();
        foreach($topics as $topic) {
			$this->reAddNested($topic['id']);
        }  
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
            FROM Topic p, Topic t
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
    
    public function addNested($parent, $project) {
        if (!empty($parent)) {
            $position = $this->db->query('
            SELECT rgt-1 as rgt FROM Topic WHERE id=:id
            ')->execute(array('id'=>$parent))->current();
        } else {
            $position = $this->db->query('
            SELECT MAX(rgt) as rgt FROM Topic WHERE project_id=:project
            ')->execute(array('project'=>$project))->current();
        }
        $this->db->query('
            UPDATE Topic SET rgt = rgt + 2 WHERE rgt > :rgt and project_id=:project
            ')->execute(array('rgt' => $position['rgt'],'project'=>$project));
        $this->db->query('
            UPDATE Topic SET lft = lft + 2 WHERE lft >:rgt and project_id=:project
            ')->execute(array('rgt' => $position['rgt'],'project'=>$project));
        return $position['rgt'];
    }

    public function createAction() {
        $topics = $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project')->execute(array('project' => $this->user->project_id));
        $request = $this->getRequest();
        $form = new \Admin\Form\TopicForm();
        $slug = new Slug();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $position = $this->addNested($request->getPost('parent'), $this->user->project_id);
                if(get_magic_quotes_gpc())
                    $content = stripslashes($request->getPost('content'));
                else
                    $content = $request->getPost('content');
                $user   = $this->db->query('SELECT project_id FROM users WHERE id=:user_id')->execute(array('user_id'=>$this->user->id))->current();
                $insert = $this->db->query('
                    INSERT INTO Topic (name, slug, content, created, updated, user_id, project_id, topic_id, lft, rgt, hide)
                    VALUES (:name, :slug, :content, :created, :updated, :user_id, :project_id, :topic_id, :lft, :rgt, :hide)
                    ')->execute(array(
                        'name' => $request->getPost('name'),
                        'slug' => $slug->slugify($request->getPost('name')),
                        'content' => $content,
                        'created' => time(),
                        'updated' => time(),
                        'user_id' => $this->user->id,
                        'project_id' => $user['project_id'],
                        'topic_id' => ($request->getPost('parent') == 0) ? null : $request->getPost('parent'),
                        'lft' => $position + 1,
                        'rgt' => $position + 2,
                        'hide' => ($request->getPost('hide') == 'on') ? 1 : 0
                        ));
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    $this->log->info('The page '.$id.' was created successfully.');
                    $this->flashMessenger()->addSuccessMessage('The page was created successfully.');
                    return $this->redirect()->toRoute('admin', array(
                        'controller' => 'topic',
                        'action' => 'edit',
                        'id' => $id
                    ));
                } else {
                    $this->log->info('Error page creation.');
                    $this->flashMessenger()->addErrorMessage('Error page creation');
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
            FROM Topic t, Topic p, users tu
            WHERE t.lft BETWEEN p.lft AND p.rgt AND t.user_id=tu.id
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
            $slug = new Slug();
            if ($form->isValid()) {
                if(get_magic_quotes_gpc())
                    $content = stripslashes($request->getPost('content'));
                else
                    $content = $request->getPost('content');
                $update = $this->db->query('
                        UPDATE Topic SET
                        name=:name, slug=:slug, content=:content, updated=:updated,
                        user_id=:user_id, project_id=:project_id, topic_id=:topic_id, hide=:hide,
                        comment=:comment
                        WHERE id=:id
                    ')->execute(array(
                        'name'    => $request->getPost('name'),
                        'slug'    => $slug->slugify($request->getPost('name')),
                        'content' => $content,
                        'topic_id' => ($request->getPost('parent') == 0) ? null : $request->getPost('parent'),
                        'updated' => time(),
                        'user_id' => $this->user->id,
                        'project_id' => $user['project_id'],
                        'comment' => ($request->getPost('comment') == 'on') ? 1 : 0,
                        'hide'    => ($request->getPost('hide') == 'on') ? 1 : 0,
                        'id'      => $id
                    ));
                if ($update) {
                    $this->log->info('The page '.$id.' was updated successfully.');
                    $this->flashMessenger()->addSuccessMessage('The page was updated successfully.');
                    $insert = $this->db->query('
                        INSERT INTO TopicArchive
                        SELECT null, * FROM Topic WHERE id=:id
                    ')->execute(array(
                        'id' => $id
                    ));
                    return $this->redirect()->toRoute('admin', array(
                        'controller' => 'topic',
                        'action' => 'edit',
                        'id' => $id
                    ));
                } else {
                    $this->log->alert('The page '.$id.' was not updated successfully.');
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

    public function archiveAction() {
        $result  = array();
        $id      = $this->params('id');

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
        $nb    = 20;
        $start = ($page * $nb) - $nb;
        if (isset($_GET['order']))
            $order = $_GET['order'];
        else
            $order = '';
        if(isset($_GET['sort']) and $_GET['sort']=='ASC')
            $sort  = 'ASC';
        else
            $sort  = 'DESC';

        if (!empty($order)) $order_string = 'ORDER BY u.'.$order.' '.$sort;
        else $order_string = '';

        $result['topics'] = $this->db->query('SELECT
            t.*, tu.username as author
            FROM TopicArchive t
            LEFT JOIN users tu ON t.user_id=tu.id
            WHERE t.project_id=:project ORDER BY t.updated DESC
            LIMIT '.$start.','.$nb
        )->execute(array('project'=>$this->user->project_id));

        $result['page'] = $page;
        return $result;
    }

    public function archiveShowAction() {
        $request = $this->getRequest();
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $entity = $this->db->query('SELECT t.* FROM TopicArchive t, users u WHERE t.topicarchive_id=:id and u.id=:user and t.project_id=u.project_id')->execute(array('id' => $id, 'user' => $this->user->id))->current();
        $topics = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic t, Topic p, users tu
            WHERE t.lft BETWEEN p.lft AND p.rgt AND t.user_id=tu.id
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
        // $vm->setTemplate('admin/topic/tinymce/edit');
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
                $this->log->notice('The page '.$id.' was deleted successfully.');
                // $log = new Log("Delete topic", "Topic ".$entity->getId()." (".$entity->getName().")", $user);
            }
        }

        $this->_helper->redirector('index', 'topic', 'admin');
    }

}

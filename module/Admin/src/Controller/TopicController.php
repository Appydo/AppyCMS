<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TopicController extends AbstractActionController {

function remove_accent($str)
{
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
                'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã',
                'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ',
                'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', '.', '.', '.', '.', '.', '.', '.', '.', '.',
                '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.',
                '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.',
                '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.',
                '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.',
                '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', 
                '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', 
                '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.',
                '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.');

  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O',
                'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c',
                'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u',
                'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D',
                'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g',
                'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K',
                'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
                'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S',
                's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W',
                'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i',
                'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
  return str_replace($a, $b, $str);
}
function slug($str){
  return mb_strtolower(preg_replace(array('/[^a-zA-Z0-9 \'-]/', '/[ -\']+/', '/^-|-$/'),
  array('', '-', ''), $this->remove_accent($str)));
}

    public function indexAction() {
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
        
        $parent = '';
        
        $query = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic t, Topic p, users tu
            WHERE t.lft BETWEEN p.lft AND p.rgt AND t.user_id=tu.id
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
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $position = $this->addNested($request->getPost('parent'), $this->user->project_id);

                $user   = $this->db->query('SELECT project_id FROM users WHERE id=:user_id')->execute(array('user_id'=>$this->user->id))->current();
                $insert = $this->db->query('
                    INSERT INTO Topic (name, slug, content, created, updated, user_id, project_id, topic_id, lft, rgt, hide)
                    VALUES (:name, :slug, :content, :created, :updated, :user_id, :project_id, :topic_id, :lft, :rgt, :hide)
                    ')->execute(array(
                        'name' => $request->getPost('name'),
                        'slug' => $this->slug($request->getPost('name')),
                        'content' => $request->getPost('content'),
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
            if ($form->isValid()) {
                $update = $this->db->query('
                        UPDATE Topic SET
                        name=:name, slug=:slug, content=:content, updated=:updated,
                        user_id=:user_id, project_id=:project_id, topic_id=:topic_id, hide=:hide,
                        comment=:comment
                        WHERE id=:id
                    ')->execute(array(
                        'name' => $request->getPost('name'),
                        'slug' => $this->slug($request->getPost('name')),
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

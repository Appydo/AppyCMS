<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CommentController extends AbstractActionController {

    public function indexAction()
    {
        $query   = $this->db->query('
            SELECT c.*, t.name as topic_name, t.id as topic_id, u.username as author
            FROM Comment c
            LEFT JOIN Topic t ON c.topic_id=t.id
            LEFT JOIN users u ON c.user_id=u.id
            WHERE t.project_id=:project ORDER BY c.id DESC'
            );
        $topics = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic p, Topic t
            LEFT JOIN users tu on t.user_id=tu.id
            WHERE t.lft BETWEEN p.lft AND p.rgt
            and t.hide=0 and t.project_id=:project and p.project_id=:project
            GROUP BY t.id
            ORDER BY t.lft'
        )->execute(array('project' => $this->project['id']));
        return array(
            'entities' => $query->execute(array('project' => $this->project['id'])),
            'topics'   => $topics
        );
    }
    
    public function topicAction()
    {
        $id = $this->params('id');
        $query   = $this->db->query('
            SELECT c.*, t.name as name, t.id as topic_id, u.username as author
            FROM Comment c
            LEFT JOIN Topic t ON c.topic_id=t.id
            LEFT JOIN users u ON c.user_id=u.id
            WHERE t.id=:id and t.project_id=:project ORDER BY c.id ASC'
            );
        $topics = $this->db->query('SELECT *
            FROM Topic t
            WHERE t.project_id=:project ORDER BY t.id ASC'
            );
        return array(
            'entities' => $query->execute(array('id'=>$id,'project' => $this->project['id'])),
            'topics'   => $topics->execute(array('project' => $this->project['id']))
        );
    }

    public function trashAction()
    {
        $query   = $this->db->query('SELECT t.*, u.username as author
            FROM Mail m
            LEFT JOIN users u on t.user_id=u.id
            WHERE t.project_id=:current and t.user_id=:user and t.hide=1 ORDER BY t.id ASC'  
        );
        
        $this->view->topics = $query->execute(array('user' => $this->user->id, 'current' => $this->project['id']));
    }
    
    public function newAction()
    {
        $topics = $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project')->execute(array('project' => $this->project['id']));
        return array(
            'form' => new \Admin\Form\CommentForm(),
            'topics' => $topics
        );
        
    }

    public function createAction()
    {
        $request   = $this->getRequest();
        $form = new \Admin\Form\CommentForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
            $insert = $this->db->query("INSERT INTO Comment (content, created, updated, user_id, topic_id, hide)
                VALUES (:content, :created, :updated, :user_id, :topic_id, :hide)")->execute(array(
                'content'  => $request->getPost('content'),
                'created'  => time(),
                'updated'  => time(),
                'user_id'  => $this->user->id,
                'topic_id' => ($request->getPost('parent')==0) ? null : $request->getPost('parent'),
                'hide'     => ($request->getPost('hide')=='on') ? 1 : 0
            ));
            
            if($insert) {
                $id = $this->db->getDriver()->getLastGeneratedValue();
                return $this->redirect()->toRoute('admin', array(
                        'controller' => 'comment',
                        'action' => 'edit',
                        'id' => $id
                    ));
            }
        }
        }
        $topics = $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project')->execute(array('project' => $this->user->project_id));
        return array(
            'form' => $form,
            'topics' => $topics
        );
    }

    public function editAction()
    {
        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db->query('
            SELECT c.*, t.id as topic_id
            FROM Comment c, Topic t
            WHERE c.id=:id and c.topic_id=t.id and t.project_id=:project')
                ->execute(array('id' => $id, 'project' => $this->project['id']))
                ->current();

        $dir = __DIR__ . '/../../../../public/uploads/'.$this->project['id'];

        if (!is_dir($dir)) {
            @mkdir($dir);
            $dir_exists = is_dir($dir);
        } else {
            $dir_exists = true;
        }
        
        $tab   = array();
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
        $topics = $this->db->query('SELECT
            t.*, tu.username as author, (SELECT COUNT(c.id) FROM Comment as c WHERE c.topic_id=t.id) as comments, (COUNT(p.id) - 1) AS depth
            FROM Topic t, Topic p
            LEFT JOIN users tu on t.user_id=tu.id
            WHERE t.lft BETWEEN p.lft AND p.rgt
            and t.hide=0 '.$parent.' and t.project_id=:project and p.project_id=:project
            GROUP BY t.id
            ORDER BY t.lft'
        )->execute(array('project' => $this->project['id']));

        return array(
            'form'      => new \Admin\Form\CommentForm(),
            'topics'    => $topics,
            'listFiles' => $tab,
            'sizes'     => $sizes,
            'entity'    => $entity,
        );
    }

    public function updateAction()
    {
        $request = $this->getRequest();
        $id = $this->params('id');
        $user   = $this->db->query('
            SELECT project_id
            FROM users
            WHERE id=:user_id')
                ->execute(array('user_id'=>$this->user->id))
                ->current();
        $entity = $this->db->query('
            SELECT *
            FROM Comment c
            LEFT JOIN Topic t ON t.id=c.topic_id
            WHERE c.id=:id and t.project_id=:project')
                ->execute(array('id' => $id, 'project' => $this->project['id']))
                ->current();
        $topics = $this->db->query('SELECT * FROM Topic t WHERE t.project_id=:project')->execute(array('project' => $user['project_id']));

        if (!$entity) {
            die('Unable to find Comment entity.');
        }

        $form = new \Admin\Form\TopicForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update = $this->db->query('
                        UPDATE Comment SET
                        content=:content, updated=:updated,
                        user_id=:user_id, topic_id=:topic_id, hide=:hide
                        WHERE id=:id
                    ')->execute(array(
                        // 'name' => $request->getPost('name'),
                        'content' => $request->getPost('content'),
                        'topic_id' => ($request->getPost('parent') == 0) ? null : $request->getPost('parent'),
                        'updated' => time(),
                        'user_id' => $this->user->id,
                        'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                        'id' => $id
                    ));
                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                        'controller' => 'comment',
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
        $vm->setTemplate('admin/comment/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query('DELETE FROM Comment WHERE id=:id')
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute('admin', array(
                                'controller' => 'comment'
                            ));;
    }

}

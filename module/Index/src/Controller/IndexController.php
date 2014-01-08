<?php

namespace Index\Controller;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {

    public function indexAction() {

        $this->topic = $this->getServiceLocator()->get('topic');
        
        $topics = $this->topic->fetch($this->project['id']);

        $layout = $this->layout();
        $layout->topics = $topics;
        
        return new ViewModel(array(
                    'project'    => $this->project,
                    'topics'     => $topics
                ));
    }

    public function topicAction() {
        
        // $this->topic = $this->getServiceLocator()->get('topic');
        // $topics = $this->topic->fetch($this->project['id']);
        
        $id = $this->params('id');
        if (empty($id)) {
            $name = $this->params('name');
            $topic = $this->db->query('
                    SELECT t.*, u.username as author
                    FROM Topic t
                    LEFT JOIN users u on t.user_id=u.id
                    WHERE t.project_id=:project and t.name=:name'
                    )->execute(array('project' => 1, 'name' => $name))->current();
        } else {
            $topic = $this->db->query('SELECT t.*, u.username as author
                    FROM Topic t
                    LEFT JOIN users u on t.user_id=u.id
                    WHERE t.project_id=:project and t.id=:id'
                    )->execute(array('project' => 1, 'id' => $id))->current();
        }
        return new ViewModel(array(
                    'topic' => $topic,
                ));
    }
    
    public function topicbynameAction() {
        $topic = $this->db->query('SELECT t.*, u.username as author
                FROM Topic t
                LEFT JOIN users u on t.user_id=u.id
                WHERE t.project_id=:project and t.name=:name and t.hide=0')
                ->execute(array('project' => $this->project['id'], 'name' => $this->params('name')))->current();

        if ($topic['comment']) {
            $form = new \Index\Form\CommentForm();
            $request = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $this->db
                     ->query('
                         INSERT INTO Comment
                         (content, topic_id, created, updated)
                         VALUES
                         (:content, :topic_id, :created, :updated)')
                     ->execute(array(
                         'topic_id' => $topic['id'],
                         'content'  => $request->getPost('content'),
                         'created'  => time(),
                         'updated'  => time()
                         ));
                }
            }
            $comments = $this->db
                    ->query('SELECT c.* FROM Comment c, Topic t WHERE t.id=:id and c.topic_id=t.id ORDER BY c.created DESC')
                    ->execute(array('id' => $topic['id']));
        } else {
            $comments = null;
        }
        return array(
            'form' => $form,
            'topic' => $topic,
            'comments' => $comments,
        );
    }

    public function installAction() {
        $this->render('index');
    }

    public function projectAction() {
        $request = $this->getRequest();
        $name = $request->getParam('project');

        if (empty($name))
            return $this->indexAction();

        $name = strtolower($name);

        $project = $this->db->query('SELECT p.* FROM Project p WHERE LOWER(p.name)=:name and p.hide=0', array('name' => $name))->fetch();

        if (!isset($project)) {
            return $this->indexAction();
        }

        if ($project['hide'] == true && ($user == null || $user != $project['author_id'])) {
            return $this->indexAction();
        }

        // Select all the topics for the default website
        $this->view->topics = $this->db->query('SELECT t.* FROM Topic t WHERE t.project_id=:project and t.hide=0 and t.topic_id is null ORDER BY t.id DESC', array('project' => $project['id'])
                )->fetchAll();
        /*
          $this->view->menus  = $this->db->query('SELECT m.* FROM Menu m WHERE LOWER(m.name)=:name and m.hide=false ORDER BY M.id DESC',
          array('name' => $name, 'project' => $project['id'])
          )->fetchAll();
         */

        return new ViewModel(array(
                    'project' => $this->project,
                    'theme' => $this->project['theme']
                ));
    }

}

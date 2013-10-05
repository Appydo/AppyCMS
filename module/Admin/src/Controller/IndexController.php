<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {

    public function indexAction() {

        $request = $this->getRequest();
        $form = new \Admin\Form\NoteForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update = $this->db->query("UPDATE Project SET note=:note WHERE id=:id", array(
                    'note' => $request->getPost('note'),
                    'id' => $this->user->project_id
                        ));
            }
        }

        $note = $this->db->query('SELECT note FROM Project WHERE id=:id')->execute(array('id' => $this->user->project_id))->current();

        // List dir for repertory and image
        $dir = ROOT_PATH . '/public/uploads/' . $this->user->project_id;
        $sizes = 0;
        $count = 0;
        $design_count = 0;

        if (!is_dir($dir)) {
            @mkdir($dir);
            $uploadDirExit = is_dir($dir);
        } else {
            $uploadDirExit = true;
        }

        if ($uploadDirExit) {
            if ($handle = opendir($dir)) {
                while (false !== ($entry = readdir($handle))) {
                    if (!in_array($entry, array('.', '..', '.DS_STORE', '.svn', '.git'))) {
                        $count++;
                        $sizes += filesize($dir . '/' . $entry) / 1024;
                    }
                }
                closedir($handle);
            }
        }

        $themes = ROOT_PATH . '/public/themes/';
        if (is_dir($themes)) {
            if ($handle = opendir($themes)) {
                while (false !== ($entry = readdir($handle))) {
                    if (!in_array($entry, array('.', '..', '.DS_STORE', '.svn', '.git'))) {
                        $design_count++;
                        // $this->view->design_sizes += filesize($dir . '/' . $entry) / 1024;
                    }
                }
                closedir($handle);
            }
        }

        $auths = $this->db->query('SELECT a.* FROM Auth a, users u WHERE u.id=:id and u.email=a.identity ORDER BY id DESC LIMIT 10')->execute(array('id' => $this->user->id));
        $count_topics = $this->db->query('SELECT COUNT(t.id) FROM Topic t WHERE t.project_id=:id')->execute(array('id' => $this->user->project_id))->current();
        $count_users = $this->db->query('SELECT COUNT(u.id) FROM users u')->execute()->current();
        $count_comments = $this->db->query('SELECT COUNT(c.id) FROM Comment c LEFT JOIN Topic t ON c.topic_id=t.id WHERE t.project_id=:id')->execute(array('id' => $this->user->project_id))->current();
        $uptime = (time() - $this->project['created']) / 60 / 60 / 24;
        $root = __DIR__;
        $config = $this->getServiceLocator()->get('Config');
        return array(
            'auths' => $auths,
            'version' => (isset($config['version'])) ? $config['version'] : '',
            'driver' => $config['db']['driver'],
            'note' => $note['note'],
            'form' => $form,
            'count_topics' => $count_topics,
            'count_users' => $count_users,
            'count_comments' => $count_comments,
            'uptime' => $uptime,
            'root' => $root,
            'sizes' => $sizes,
            'count' => $count,
            'design_count' => $design_count,
        );
    }

    public function infoAction() {
        
    }

    public function projectAction() {

        $request = $this->getRequest();
        $name = $request->getParam('name');

        if (empty($name))
            return $this->indexAction();

        $name = strtolower($name);

        $query = $db->query('SELECT p FROM project p WHERE LOWER(p.name)=:name');
        $project = $query->execute(array('name' => $name))->current();
        unset($query);
        if (!isset($project)) {
            throw $this->createNotFoundException('Unable to find project ' . $name . '.');
        }

        if ($project->getHide() == true && ($user == null || $user != $project->getAuthor())) {
            throw $this->createNotFoundException('This project is not public.');
        }

        /*
          // hits + 1
          $project->setHits($project->getHits() + 1);

          $stat = new Stat($this->currentPageURL(), $project, $this->getIp());

          $em->flush();
         */

        // Select all the topics for the default website
        $query = $db->query('SELECT p FROM topics p WHERE project=:project and hide=false and LOWER(p.name)=:name ORDER BY id DESC');
        $topics = $query->execute(array('name' => $name, 'project' => $project->id));
        unset($query);
        $query = $db->query('SELECT p FROM menu p WHERE LOWER(p.name)=:name and hide=false ORDER BY id DESC');
        $menus = $query->execute(array('name' => $name, 'project' => $project->id));
        unset($query);

        return array(
            'menus' => $menus,
            'theme' => $theme,
            'topics' => $topics,
            'project' => $project
        );
    }
}


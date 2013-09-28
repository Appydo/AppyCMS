<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class LogController extends AbstractActionController {

    public function indexAction() {

        $entities = $this->db->query('SELECT l.*
            FROM Log l
            WHERE l.project_id=:project ORDER BY l.id DESC'
                )->execute(array('project' => $this->user->project_id));

        return array(
            'entities' => $entities
        );
    }

    public function showAction($id) {

        $entity = $em->getRepository('AppydoTestBundle:Log')->find($id);

        if (!$entity) {
            die('Unable to find Log entity.');
        }

        return array(
            'entity' => $query->execute()->current()
        );
    }

    public function emptyAction() {

        if ($project->getAuthor() != $user) {
            die('User is not admin.');
        }

        $entities = $em->getRepository('AppydoTestBundle:Log')->findBy(
                array('project' => $user->getCurrentId()), array('id' => 'DESC')
        );

        if (!$entities) {
            die('Unable to find Log entities.');
        }

        foreach ($entities as $entity) {
            $em->remove($entity);
        }

        $log = new Log("Empty logs", "Empty logs", $user);
        $em->persist($log);
        $em->flush();

        return $this->redirect($this->generateUrl('log'));
    }

}

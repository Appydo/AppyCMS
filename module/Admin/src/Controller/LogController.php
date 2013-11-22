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

}

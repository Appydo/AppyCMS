<?php

namespace Index\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class NewsletterController extends AbstractActionController {

    public function indexAction() {

        return array();

    }
    
    public function validAction() {
        
        $request = $this->getRequest();
        if ($request->isPost() and $request->getPost('email')!='') {
        
            if ($request->getPost('partner')) {
                $projects = $this->db->query('SELECT id FROM Project')->execute();
            } else {
                $projects = $this->db->query('SELECT id FROM Project WHERE id=:id')->execute(array('id'=>$this->project['id']));
            }
            
            // if (empty($organizations)) die('Organization not found.');
            
            $email = $this->db->query('SELECT id FROM email WHERE email=:email')->execute(array(
                'email' => $request->getPost('email')
            ))->current();


            if (empty($email)) {
                $insert  = $this->db->query('INSERT INTO email (project_id, email, created, updated) VALUES (:project_id, :email, :created, :updated)')->execute(array(
                    'project_id' => $this->project['id'],
                    'email'      => $request->getPost('email'),
                    'created'    => time(),
                    'updated'    => time()
                ));
                $email_id = $this->db->getDriver()->getLastGeneratedValue();
            } else {
                $email_id = $email['id'];
            }

            
            foreach($projects as $project) {
                $consent = $this->db->query('SELECT *
                    FROM consent
                    WHERE project_id=:project_id and email_id=:email_id')
                        ->execute(array('email_id' => $email_id, 'project_id' => $this->project['id']))->current();
                if (empty($consent)) {
                    $insert  = $this->db->query('INSERT INTO consent (project_id, email_id, created, updated) VALUES (:project_id, :email_id, :created, :updated)')->execute(array(
                            'project_id' => $project['id'],
                            'email_id'   => $email_id,
                            'created'    => time(),
                            'updated'    => time()
                        ));
                }
            }
        }
        
        return array(
            'exist' => 0,
            'email' => $request->getPost('email')
        );
    }

}
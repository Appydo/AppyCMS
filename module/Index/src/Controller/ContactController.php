<?php
namespace Index\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ContactController extends AbstractActionController {

    public function indexAction() {
        return array('form' => new \Index\Form\ContactForm());
    }

    public function sendAction() {
        $request = $this->getRequest();
        $form = new \Index\Form\ContactForm();
        $name = $request->getPost('contact_name');
        $email = $request->getPost('contact_email');
        $message = $request->getPost('contact_message');
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {

                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }

                $mail = "Name : {$name}\nIP : {$ip}\nEmail : {$email}\n\n" . $message;
                $user = $this->db->query('SELECT users.email FROM Project, users WHERE Project.id=1 and users.id=Project.user_id')->execute()->current();
                mail($user['email'], "[{$_SERVER['HTTP_HOST']}] Message", $mail);

                  $insert = $this->db->query("INSERT INTO Mail
                  (name, email, message, created, updated, ip, user_id, project_id, hide) VALUES
                  (:name, :email, :message, :created, :updated, :ip, :user_id, :project_id, :hide)", array(
                  'name'    => $name,
                  'email'   => $email,
                  'message' => $message,
                  'created' => time(),
                  'updated' => time(),
                  'ip'      => $ip,
                  'user_id' => ($this->user) ? $this->user->id : '',
                  'project_id' => ($this->user) ? $this->user->project_id : '',
                  'hide' => 0
                  ));

                return array(
                    'name' => $name,
                    'email' => $email,
                    'message' => $message,
                );
            } else {
                echo 'mail not send.';
            }
        }
        return array(
            'name' => $name,
            'email' => $email,
            'message' => $message,
        );
    }

}
<?php

namespace Index\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

class AuthController extends AbstractActionController {

    function generateSalt($max = 15) {
        $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*?";
        $i = 0;
        $salt = '';
        $cpt = strlen($characterList);
        for ($i=0;$i < $max;$i++) {
            $salt .= $characterList{mt_rand(0, ($cpt - 1))};
        }
        return $salt;
    }

    public function createUser($username, $password, $email) {
        $this->db->getDriver()->getConnection()->beginTransaction();
        $salt = $this->generateSalt();
        $user = $this->db->query("INSERT INTO users (username, email, salt, role, created, updated, password, is_active) VALUES (:username, :email, :salt, :role, :created, :updated, :password, :is_active)", array(
            "username" => $username,
            "email" => $email,
            "salt" => $salt,
            "role" => 'admin',
            "created" => time(),
            "updated" => time(),
            "password" => sha1($password . $salt),
            "is_active" => 1,
            ));
        if ($user) {
            // $user_id = $this->db->lastInsertId();
            $user_id = $this->db->query("SELECT max(id) FROM users")->execute()->current();
            $user_id = $user_id['max(id)'];
            $this->db->query("INSERT INTO Project (user_id, name, created, updated, hide, ban) VALUES (:user_id, :name, :created, :updated, :hide, :ban)", array(
                'user_id' => $user_id,
                'name' => $username,
                'created' => time(),
                'updated' => time(),
                'hide' => 0,
                'ban' => 0,
            ));
            $project_id = $this->db->query("SELECT max(id) FROM Project")->execute()->current();
            $project_id = $project_id['max(id)'];
            $this->db->query('UPDATE users SET project_id=:project WHERE id=:id', array('project' => $project_id, 'id' => $user_id));
            $this->db->getDriver()->getConnection()->commit();
        } else {
            $this->db->getDriver()->getConnection()->rollback();
        }
    }

    public function logoutAction() {
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        return $this->redirect()->toRoute('auth', array('action' => 'login'));
    }

    public function signupAction() {

        $auth = new AuthenticationService();

        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $request = $this->getRequest();
        $form = new \Index\Form\SignupForm();

        if ($request->isPost()) {

            $username   = $request->getPost()->get('username');
            $credential = $request->getPost()->get('password');
            $email      = $request->getPost()->get('email');

            $form->setData($request->getPost());

            if ($form->isValid()) {
            
                $this->createUser($username, $credential, $email);
            
                $authAdapter = new AuthAdapter($this->db,
                                'users',
                                'email',
                                'password'
                );

                $user = $this->db->query("SELECT id, salt FROM users WHERE email=:email")->execute(array('email'=>$email))->current();
                $authAdapter
                        ->setIdentity($email)
                        ->setCredential(sha1($credential . $user['salt']))
                ;

                $result = $authAdapter->authenticate($this->db);

                if ($result->isValid()) {
                    $this->db->query("INSERT INTO Auth (identity, date, success) VALUES (:identity, :date, :success)", array(
                        'identity' => $email,
                        'date' => time(),
                        'success' => 1,
                    ));
                    $storage = $auth->getStorage();
                    $storage->write($authAdapter->getResultRowObject(null, 'password'));
                    return $this->redirect()->toRoute('admin');
                } else {
                    $this->db->query("INSERT INTO Auth (identity, date, success) VALUES (:identity, :date, :success)", array(
                        'identity' => $email,
                        'date' => time(),
                        'success' => 0,
                    ));
                    return $this->redirect()->toRoute('auth');
                }
            }
        }

        return array('form' => $form);
    }

    public function profilAction() {
        $auth = new AuthenticationService();
        $user = ($auth->hasIdentity()) ? $auth->getIdentity() : die('User not found.');
        $profil = $this->db->query('SELECT u.*
            FROM users u WHERE u.id=:user')->execute(array('user' => $user->id))->current();
        return array(
            'profil' => $profil
        );
    }

    public function loginAction() {
        
        $this->layout('simple/layout');
        $auth = new AuthenticationService();

        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $form = new \Index\Form\LoginForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $identity = $request->getPost()->get('username');
            $credential = $request->getPost()->get('password');
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $authAdapter = new AuthAdapter($this->db,
                                'users',
                                'email',
                                'password'
                );
                $user = $this->db->query("SELECT id, salt FROM users WHERE email=:email")->execute(array('email'=>$identity))->current();
                $authAdapter
                        ->setIdentity($identity)
                        ->setCredential(sha1($credential . $user['salt']))
                ;

                $result = $authAdapter->authenticate($this->db);

                if ($result->isValid()) {
                    $this->db->query("INSERT INTO Auth (identity, date, success) VALUES (:identity, :date, :success)", array(
                        'identity' => $identity,
                        'date'     => time(),
                        'success'  => 1,
                    ));
                    $storage = $auth->getStorage();
                    $storage->write($authAdapter->getResultRowObject(null, 'password'));
                    return $this->redirect()->toRoute('admin');
                } else {
                    $this->db->query("INSERT INTO Auth (identity, date, success) VALUES (:identity, :date, :success)", array(
                        'identity' => $identity,
                        'date'     => time(),
                        'success'  => 0,
                    ));
                    return $this->redirect()->toRoute('auth');
                }
            }
        }

        return array(
            'form' => $form,
            'project' => $this->project
        );
    }

}

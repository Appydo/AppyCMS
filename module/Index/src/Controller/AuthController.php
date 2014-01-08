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

    public function confirmAction() {

        $auth = new AuthenticationService();

        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('ier', array('controller'=>'index', 'action' => 'profil'));
        }

        $secret = $_GET['secret'];
        if (empty($secret)) die();
        $select = $this->db->query('
            SELECT id, email, password
            FROM users
            WHERE secret=:secret and is_active=0')
        ->execute(array(
            'secret' => $secret
            ))->current();
        if (isset($select['id'])) {
            $result['confirm'] = 1;
            $this->db->query('
                UPDATE users SET is_active=1
                WHERE secret=:secret')
            ->execute(array('secret' => $secret));
            $authAdapter = new AuthAdapter($this->db,
                'users',
                'email',
                'password'
                );
            $authAdapter
                ->setIdentity($select['email'])
                ->setCredential($select['password']);

            // Auto authenticate
            $result = $authAdapter->authenticate($this->db);

            if ($result->isValid()) {
                $this->db->query("INSERT INTO Auth (identity, date, success) VALUES (:identity, :date, :success)", array(
                    'identity' => $select['email'],
                    'date' => time(),
                    'success' => 1,
                    ));

                $storage = $auth->getStorage();
                $storage->write($authAdapter->getResultRowObject(null, 'password'));
            }
            $this->flashMessenger()->addSuccessMessage('Votre compte est maintenant activé. Merci');
            return $this->redirect()->toRoute('breizhadonf', array('controller'=>'annonces', 'action' => 'index'));
        } else {
            $select = $this->db->query('
                SELECT id, email, password
                FROM users
                WHERE secret=:secret and is_active=1')
                ->execute(array(
                'secret' => $secret
                ))->current();
            if (isset($select['id'])) {
                $result['error'] = "Le code de confirmation à déjà été activé.";
            } else {
                $result['error'] = "Le code de confirmation est incorrecte.";
            }
            $result['confirm'] = 0;
        }
        return $result;
    }

    private function getEmailData($db, $email, $title, $context) {
        $email_data = $db->query('
            SELECT ed_title, ed_content
            FROM EmailData
            WHERE ed_hide!=1 and ed_title=:title')
                ->execute(array(
                    'title' => $title
                ))->current();
        if ($email_data) {
            foreach($context as $key=>$var) {
                $email_data['ed_content'] = str_replace('{{'.$key.'}}', $var, $email_data['ed_content']);
            }
            mail($email, '['.$website.'] '.$email_data['ed_title'] , $email_data['ed_content']);
        }
    }
    
    public function sendMailRecoveryAction() {
        $result = array();
        $website = 'http://www.breizhadonf.com';
        $layout = $this->layout();
        $layout->title = 'Changer mon mot de passe';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $profil = $this->db->query('
                SELECT u.id
                FROM users u
                WHERE u.email=:email')
                    ->execute(array(
                        'email' => $request->getPost('email')
                    ))->current();
            if ($profil) {
                $newpassword = $this->db->query('
                SELECT pr_id
                FROM passwordRecovery
                WHERE user_id=:user and created>:time')
                    ->execute(array(
                        'user' => $profil['id'],
                        'time' => (time() - 3600) // 1 hour
                    ))->current();
                if (!$newpassword) {
                    $password = $this->generateSalt(8);
                    $salt = $this->generateSalt();
                    $this->db->query('
                        INSERT INTO passwordRecovery (user_id, pr_password, pr_salt, created)
                        VALUES (:user_id, :password, :salt, :created)', array(
                                'user_id'  => $profil['id'],
                                'password' => sha1($password . $salt),
                                'salt'     => $salt,
                                'created'  => time(),
                            ));

                    $context = array();
                    $context['password'] = $password;

                    $this->getEmailData($this->db, $request->getPost('email'), 'Your new credentials', $context);
                    
                }
            }
        }
        return $result;
    }

    public function loginAction() {

        $this->layout('simple');
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
                    ->setCredential(sha1($credential . $user['salt']));

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
                    $this->flashMessenger()->addErrorMessage('Login error : wrong email or password');
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

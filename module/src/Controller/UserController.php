<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

// require_once (__DIR__ . '/../forms/UserForm.php');

class UserController extends AbstractActionController {
    
    private $table      = 'users';
    private $controller = 'User';
    private $id         = 'user_id';
    private $module     = 'admin';
    
    public function indexAction() {
        
        $acl = new Acl();
        $query = $this->db->createStatement('
            SELECT role_name, role_parent
            FROM Role r
            LEFT JOIN users u ON u.project_id = r.project_id
            WHERE u.id=:user_id'
        );
        $roles = $query->execute(array('user_id' => $this->user->id))->getResource()->fetchAll();
        
		$user_role = $this->db->query('
                SELECT r.role_name
                FROM users u
                LEFT JOIN Acl a ON (u.id=a.user_id and a.project_id=u.project_id)
                LEFT JOIN Role r USING(role_id) WHERE u.id=:user_id'
            )->execute(array('user_id' => $this->user->id))->current();

        $acl->addResource(new Resource('user'));

        foreach ($roles as $role) {
            $acl->addRole(new Role($role['role_name']), $role['role_parent']);
        }

        // $acl->allow('staff', array('topic', 'project', 'user'), array('list', 'read'));
        $acl->allow('admin');
        if (!$acl->isAllowed($user_role['role_name'], 'user', 'list')) {
            $this->getResponse()->setStatusCode(404); 
            return;
        }
        
        if (isset($_GET['page']))
            $page = $_GET['page'];
        else
            $page  = 1;
        if (isset($_GET['move'])) {
            if ($_GET['move']=='next') {
                $page++;
            } elseif ($_GET['move']=='prev' and $page!=1) {
                $page--;
            }
        }
        if (empty($page)) $page = 0;
        $nb    = 20;
        $start = ($page * $nb) - $nb;
        if (isset($_GET['order']))
            $order = $_GET['order'];
        else
            $order = '';
        if(isset($_GET['sort']) and $_GET['sort']=='ASC')
            $sort  = 'ASC';
        else
            $sort  = 'DESC';
        
        if (!empty($order)) $order_string = 'ORDER BY '.$order.' '.$sort;
        else $order_string = '';
        
        $where_string = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('query') != '') {
                $where = array();
                $query = $request->getPost('query');
                $metadata = new \Zend\Db\Metadata\Metadata($this->db);
                $columns  = $metadata->getTable($this->table)->getColumns();
                foreach($columns as $column) {
                    // echo $column->getDataType();
                    if ($column->getDataType()=='text' or $column->getDataType()=='varchar') {
                        $where[] = $column->getName().' LIKE "%'.$query.'%"';
                    }
                }
                if (!empty($where)) $where_string = 'WHERE '.implode(' or ',$where);
                $stmt = $this->db->createStatement('SELECT * FROM '.$this->table);
            } elseif ($request->getPost('action_submit') == '1') {
                if ($request->getPost('action_select') == 'delete') {
                    // die(var_dump($request->getPost('action')));
                    foreach ($request->getPost('action') as $action) {
                        return $this->deleteAction($action);
                    }
                }
            }
        }
        
        $entities = $this->db->query('
                SELECT u.*, r.role_name
                FROM users u
                LEFT JOIN Acl a ON (u.id=a.user_id and a.project_id=:project_id)
                LEFT JOIN Role r USING(role_id)
                '.$where_string.' '.$order_string.' LIMIT '.$start.','.$nb
            )->execute(array(
                    'project_id'=>$this->project['id']
                ));
        
        $count = $this->db->query('
                SELECT count(u.id) as count
                FROM users u
                LEFT JOIN Acl a ON (u.id=a.user_id and a.project_id=:project_id)
                LEFT JOIN Role r USING(role_id)
                '.$where_string.' '.$order_string.' LIMIT '.$start.','.$nb
            )->execute(array(
                    'project_id'=>$this->project['id']
                ))->current();

        return array(
            'entities' => $entities,
            'controller' => $this->controller,
            'id' => $this->id,
            'thead' => true, // display thead
            'module' => $this->module,
            'order' => $order,
            'sort' => $sort,
            'page' => $page,
            'query' => $request->getPost('query'),
            'count' => $count['count']
        );
    }

    public function profilAction() {

        $request = $this->getRequest();
        $form = new \Admin\Form\UserForm();
        if ($request->isPost()) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
            $inputFilter->add($factory->createInput(array(
                'name'     => 'username',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 30,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'useremail',
                'required' => true,
                'filters'    => array(
                    array(
                        'name'    => 'Zend\Filter\StripTags',
                    ),
                ),
                'validators' => array(
                    array(
                        'name'    => 'Zend\Validator\EmailAddress',
                    ),
                ),
            )));
            $form->setInputFilter($inputFilter);
            $form->setData($request->getPost());
            if ($form->isValid()) {
                if ($request->getPost('oldPassword') != '' and $request->getPost('password') == $request->getPost('confirmPassword')) {
                    $salt = $this->db->query("SELECT salt FROM users WHERE id=:id")->execute(array('id'=>$this->params('id')))->current();
                    $update = $this->db->query("UPDATE users SET username=:username, email=:email, password=:password, updated=:updated WHERE id=:id")->execute(array(
                        'username' => $request->getPost('username'),
                        'email' => $request->getPost('useremail'),
                        'password' => sha1($request->getPost('password') . $salt['salt']),
                        'updated' => time(),
                        'id' => $this->user->id
                        ));
                } else {
                    $update = $this->db->query("UPDATE users SET username=:username, email=:email, updated=:updated WHERE id=:id")->execute(array(
                        'username' => $request->getPost('username'),
                        'email' => $request->getPost('useremail'),
                        'updated' => time(),
                        'id' => $this->user->id
                        ));
                }
            }
        }

        $query = $this->db->query('SELECT u.*
            FROM users u
            WHERE u.id=:user'
        );

        if (!$query) {
            die('Unable to find User entity.');
        }

        return array(
            'entity' => $query->execute(array('user' => $this->user->id))->current(),
            'form' => $form
        );
    }

    public function showAction($id) {

        $entity = $this->db->query('SELECT u.*
            FROM users u
            WHERE u.id=:user'
                )->execute();

        return array(
            'entity' => $entity,
        );
    }

    public function signupAction($name) {

        $entity = new User();
        $form = $this->createForm(new UserSignup(), $entity);

        if (!empty($name)) {
            $query = $em->createQuery('SELECT p FROM AppydoTestBundle:Project p WHERE LOWER(p.name)=?1');
            $query->setParameter(1, $name);
            $project = $query->getSingleResult();
        } else {
            $project = null;
        }

        return $this->render('AppydoTestBundle:User:signup.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'project' => $project,
                    'theme' => (isset($project) and $project->getTheme() != '') ? $project->getTheme() : 'default',
                ));
    }

    public function newAction() {

        $query = $this->db->query('
            SELECT *
            FROM Role r
            LEFT JOIN users u ON u.project_id = r.project_id
            WHERE u.id=:user_id'
        );
        $roles = $query->execute(array('user_id' => $this->user->id));
        $form = new \Admin\Form\UserForm();
        return array(
            'roles' => $roles,
            'form'  => $form,
            );
    }

    public function createUser($username, $password, $email, $role_id) {
        $this->db->getDriver()->getConnection()->beginTransaction();
        $salt = $this->generateSalt();
        $user = $this->db->query("INSERT INTO users (username, email, salt, role, created, updated, password, is_active) VALUES (:username, :email, :salt, :role, :created, :updated, :password, :is_active)", array(
            "username" => $username,
            "email" => $email,
            "salt" => $salt,
            "role" => $role_id,
            "created" => time(),
            "updated" => time(),
            "password" => sha1($password . $salt),
            "is_active" => 1,
                ));
        if ($user) {
            $user_id = $this->db->getDriver()->getLastGeneratedValue();
            $project = $this->db->query("INSERT INTO Project (user_id, name, created, updated, hide, ban) VALUES (:user_id, :name, :created, :updated, :hide, :ban)", array(
                'user_id' => $user_id,
                'name' => $username,
                'created' => time(),
                'updated' => time(),
                'hide' => 0,
                'ban' => 0,
                ));
            $project_id = $this->db->getDriver()->getLastGeneratedValue();
            $this->db->query('UPDATE users SET project_id=:project WHERE id=:id', array('project' => $project_id, 'id' => $user_id));
            $this->db->getDriver()->getConnection()->commit();
            
            $project = $this->db->query("INSERT INTO Acl (user_id, project_id, role_id) VALUES (:user_id, :project_id, :role_id)", array(
                'user_id' => $user_id,
                'project_id' => $this->project['id'],
                'role_id' => $role_id
                ));
            
            return $user_id;
        } else {
            $this->db->getDriver()->getConnection()->rollback();
            return 0;
        }
    }

    function generateSalt($max = 15) {
        $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*?";
        $i = 0;
        $salt = '';
        $cpt = strlen($characterList);
        for ($i = 0; $i < $max; $i++) {
            $salt .= $characterList{mt_rand(0, ($cpt - 1))};
        }
        return $salt;
    }

    public function createSignupAction($name) {
        
        $entity = new User();

        $request = $this->getRequest();
        $form = $this->createForm(new UserSignup(), $entity);
        $form->bindRequest($request);
        $errors = $this->get('validator')->validate($entity, array('registration'));
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {

                $factory = $this->container->get('security.encoder_factory');
                $encoder = $factory->getEncoder($entity);
                $entity->setPassword($encoder->encodePassword($entity->getPassword(), $entity->getSalt()));
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);

                /*
                 * Create a project for the user
                 */
                $project = new Project();
                $project->setName($entity->getUsername());
                $project->setAuthor($entity);
                $project->setCreated(new \DateTime());
                $project->setUpdated(new \DateTime());
                $em->persist($project);

                $entity->setCurrent($project);
                $em->persist($entity);

                $em->flush();

                // create the authentication token
                $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                                $entity,
                                null,
                                'secured_area',
                                $entity->getRoles()
                );
                // give it to the security context
                // $this->get('session')->set('_security_'.'main', serialize($token));
                $this->container->get('security.context')->setToken($token);

                return $this->redirect($this->generateUrl('user_edit', array('id' => $entity->getId())));
            }
        }

        if (!empty($name)) {
            $query = $em->createQuery('SELECT p FROM AppydoTestBundle:Project p WHERE LOWER(p.name)=?1');
            $query->setParameter(1, $name);
            $project = $query->getSingleResult();
        } else {
            $project = null;
        }

        
        return $this->render('AppydoTestBundle:User:signup.html.twig', array(
                    'project' => $project,
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'theme' => (isset($project) and $project->getTheme() != '') ? $project->getTheme() : 'default'
                ));
    }

    public function createAction() {

        $request = $this->getRequest();
        $form = new \Admin\Form\UserForm();

        if ($request->isPost()) {

            $username = $request->getPost()->get('username');
            $credential = $request->getPost()->get('password');
            $email = $request->getPost()->get('email');
            $role  = $request->getPost()->get('role');

            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
            $inputFilter->add($factory->createInput(array(
                'name'     => 'username',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 30,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'password',
                'required' => true,
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 6,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'email',
                'required' => true,
                'filters'    => array(
                    array(
                        'name'    => 'Zend\Filter\StripTags',
                    ),
                ),
                'validators' => array(
                    array(
                        'name'    => 'Zend\Validator\EmailAddress',
                    ),
                ),
            )));
            $form->setInputFilter($inputFilter);
            
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $id = $this->createUser($username, $credential, $email, $role);
                if ($id > 0)
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'user',
                                'action' => 'edit',
                                'id' => $id
                            ));
                else
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'user',
                                'action' => 'new'
                            ));
            }
        }

        $query = $this->db->query('
            SELECT *
            FROM Role r
            LEFT JOIN users u ON u.project_id = r.project_id
            WHERE u.id=:user_id'
        );
        $roles = $query->execute(array('user_id' => $this->user->id));
        
        $vm = new ViewModel(array(
                    'roles' => $roles,
                    'form' => $form,
                ));
        $vm->setTemplate('admin/user/new');
        return $vm;
    }

    public function editAction() {
        $request = $this->getRequest();
        $id = $this->params('id');
        $query = $this->db->query('
            SELECT *
            FROM Role r
            LEFT JOIN users u ON u.project_id = r.project_id
            WHERE u.id=:user_id'
        );
        $roles = $query->execute(array('user_id' => $this->user->id));
        $form = new \Admin\Form\UserForm();

        if ($request->isPost()) {

            $form->setData($request->getPost());

            if ($form->isValid()) {

                $update = $this->db->query("UPDATE users SET username=:username, email=:email, note=:note, role=:role, is_active=:is_active WHERE id=:id", array(
                    'username' => $request->getPost('username'),
                    'email' => $request->getPost('useremail'),
                    'note' => $request->getPost('note'),
                    'role' => $request->getPost('role'),
                    'is_active' => ($request->getPost('is_active')=='on'),
                    'id' => $id
                    )
                );
                
                

                $role = $this->db
                    ->query('
                        SELECT *
                        FROM Acl
                        WHERE user_id=:id')
                    ->execute(array('id' => $id))
                    ->current();

                if ($role) {
                    $this->db->query('
                        UPDATE Acl
                        SET role_id=:role_id
                        WHERE project_id=:project_id and user_id=:user_id', array(
                            'user_id' => $id,
                            'project_id' => $this->project['id'],
                            'role_id' => $request->getPost('role')
                        ));
                } else {
                    $this->db->query('
                        INSERT INTO Acl
                        (role_id, project_id, user_id)
                        VALUES (:role_id, :project_id, :user_id)', array(
                            'user_id' => $id,
                            'project_id' => $this->project['id'],
                            'role_id' => $request->getPost('role')
                        ));
                }

                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'user',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $entity = $this->db
                ->query('
                    SELECT *, a.role_id
                    FROM users u
                    LEFT JOIN Acl a ON (a.project_id=:project_id and a.user_id=u.id)
                    WHERE u.id=:id')
                ->execute(array('id' => $id, 'project_id'=>$this->project['id']))
                ->current();

        if (empty($entity)) {
            die('User not found.');
        }

        return array(
            'roles'  => $roles,
            'entity' => $entity,
            'form'   => $form
        );
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query('DELETE FROM users WHERE id=:id')
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute('admin', array(
                                'controller' => 'user'
                            ));;
    }

}

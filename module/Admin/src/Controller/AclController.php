<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

/*
 *
 * example :
 * role : admin, staff, editor... / GROUP
 * ressource : topic, list, create... / PERMISSION
 * 
 * allow : ressource
 */

class AclController extends AbstractActionController {

    public function indexAction() {
        $acl = new Acl();

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
        
        if (!empty($order)) $order_string = 'ORDER BY u.'.$order.' '.$sort;
        else $order_string = '';

        $query = $this->db->createStatement('
            SELECT *
            FROM Role r
            LEFT JOIN users u ON u.project_id = r.project_id
            WHERE u.id=:user_id  LIMIT '.$start.','.$nb
        );
        $roles = $query->execute(array('user_id' => $this->user->id))->getResource()->fetchAll();
        
        foreach ($roles as $role) {
            $acl->addRole(new Role($role['role_name']), $role['role_parent']);
        }
        
        $query = $this->db->createStatement('
            SELECT *
            FROM Resource a'
        );

        $resources = $query->execute()->getResource()->fetchAll();

        $query = $this->db->query('
            SELECT *
            FROM Allow a'
        );
        $allows = $query->execute()->getResource()->fetchAll();
        if(!empty($allows)) {
            foreach ($allows as $allow) {
                $acl->allow($allow['role_name'], null, $role['privileges']);
            }
        }

        $query = $this->db->query('
            SELECT *
            FROM Deny d'
        );
        $denys = $query->execute()->getResource()->fetchAll();
        foreach ($denys as $deny) {
            $acl->deny($deny['role_name'], null, $deny['privileges']);
        }

        /*
          $acl->addRole(new Role('staff'), 'guest');
          $acl->addRole(new Role('editor'), 'staff');
          $acl->addRole(new Role('administrator'));
         */

        // Guest may only view content
        // $acl->allow('guest', null, 'view');

        // Staff inherits view privilege from guest, but also needs additional
        // privileges
        // $acl->allow('staff', null, array('edit', 'submit', 'revise'));

        // Editor inherits view, edit, submit, and revise privileges from
        // staff, but also needs additional privileges
        // $acl->allow('editor', null, array('publish', 'archive', 'delete'));

        // $acl->allow('admin');

        // $parents = array('guest', 'staff', 'admin');
        // $acl->addRole(new Role('someUser'), $parents);

        // $acl->allow('staff', null, array('edit', 'archive'));
        // $acl->allow('admin', null, array('publish', 'archive', 'delete'));

        $acl->addResource('someResource');

        /**
         * Défintions des resources
         */
        $acl->addResource(new Resource('list'));
        $acl->addResource(new Resource('create'));
        $acl->addResource(new Resource('read'));
        $acl->addResource(new Resource('update'));
        $acl->addResource(new Resource('delete'));

        $acl->addResource(new Resource('topic'));
        $acl->addResource(new Resource('project'));
        $acl->addResource(new Resource('user'));

        // $acl->allow('staff', array('topic', 'project'), array('list', 'read'));
        // $acl->allow('editor', array('topic', 'project'), array('publish', 'archive'));

        /*
         * Verifier les droits
         */
        // echo $acl->isAllowed('staff', 'topic', 'create');
        // echo $acl->isAllowed('staff', 'project', 'list');

        // $acl->allow('admin');

        return array(
            'roles' => $roles,
            'resources' => $resources,
            'order' => $order,
            'sort' => $sort,
            'page' => $page,
        );
    }

    public function newAction() {

        return array(
            'form' => new \Admin\Form\AclForm(),
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\AclForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid($request->getPost())) {
                $insert = $this->db->query("INSERT INTO Role (role_name, project_id)
                        VALUES (:name, :project)", array(
                        'name' => $request->getPost('role_name'),
                        'project' => $this->project['id']
                     ));

                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'acl',
                                'action' => 'index',
                                // 'id' => $id
                            ));
                }
            }
        }

        return array(
            'form' => $form
        );
    }

    public function editAction() {

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
        
        if (!empty($order)) $order_string = 'ORDER BY u.'.$order.' '.$sort;
        else $order_string = '';

        $id = $this->params('id');
        $form = new \Admin\Form\AclForm();
        $entity = $this->db
                ->query('SELECT * FROM Role WHERE role_id=:id')
                ->execute(array('id'=>$id))
                ->current();

        if (empty($entity)) {
            throw new \Exception("Could not find row $id");
        }

        $form->setData($entity);

        $allows = $this->db
            ->createStatement('SELECT * FROM Allow a')
            ->execute()
            ->getResource()
            ->fetchAll();

        $acl = array();
        foreach($allows as $allow) {
            $acl[$allow['controller']][$allow['privilege']] = 1;
        }
        
        $resources = $this->db
            ->createStatement('SELECT * FROM Resource r LIMIT '.$start.','.$nb)
            ->execute(array())
            ->getResource()
            ->fetchAll();

        $privileges = $this->db
            ->createStatement('SELECT * FROM Privilege p LIMIT '.$start.','.$nb)
            ->execute(array())
            ->getResource()
            ->fetchAll();

        return array(
            'form' => $form,
            'entity' => $entity,
            'resources' => $resources,
            'privileges' => $privileges,
            'acl'   => $acl,
            'order' => $order,
            'sort'  => $sort,
            'page'  => $page,
        );
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id      = $this->params('id');
        $entity  = $this->db
            ->query('SELECT * FROM Role WHERE role_id=:id')
            ->execute(array('id' => $id))
            ->current();

        if (empty($entity)) {
            throw new \Exception("Could not find row $id");
        }

        $form = new \Admin\Form\AclForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {

                $update = $this->db->query(
                    'UPDATE Role SET role_name=:name WHERE role_id=:id', array(
                    'name' => $request->getPost('role_name'),
                    'id' => $id
                     ));

                $this->db
                    ->query('DELETE FROM Allow WHERE role_id=:id and project_id=:project_id')
                    ->execute(array('id' => $id,'project_id'=>$this->user->project_id));

                foreach($request->getPost('acl') as $ressource=>$value) {
                    foreach($value as $privilege) {
                        $insert = $this->db->query("INSERT INTO Allow (role_id, project_id, controller, privilege)
                            VALUES (:role_id, :project_id, :controller, :privilege)", array(
                            'role_id'    => $id,
                            'project_id' => $this->user->project_id,
                            'controller' => $ressource,
                            'privilege'  => $privilege,
                         ));
                    }
                }

                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'acl',
                                'action' => 'index',
                                // 'id' => $id
                            ));
                }
            }
        }
        return array(
            'form' => $form,
        );
    }

    public function deleteAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                    ->query('DELETE FROM Role WHERE role_id=:id')
                    ->execute(array('id' => $action));
            }
        }

        return $this->redirect()
            ->toRoute('admin', array(
                    'controller' => 'acl'
                ));
    }
}

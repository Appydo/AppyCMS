<?php

namespace Admin;

define('SGBD', 'SQL');

use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\Event;
use Zend\Authentication\AuthenticationService;

class Module {


    public function onBootstrap(Event $e) {
        $translator = $e->getApplication()->getServiceManager()->get('translator');
        $translator
          ->setLocale('fr_FR')
          // ->setLocale(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']))
          ->setFallbackLocale('fr_FR');
        // \Locale::setDefault('fr_FR');
        // die(\Locale::getDefault());

        $app = $e->getParam('application');
        $sharedManager = $app->getEventManager()->getSharedManager();
        $sharedManager->attach(__NAMESPACE__, 'dispatch', function($e) {
            $serviceManager = $e->getApplication()->getServiceManager(); 
            $app = $e->getParam('application');
            $controller = $e->getTarget();
            $config = $app->getConfig();
            if ($config['install'] == 0 and $e->getRouteMatch()->getParam('controller', 'index') != 'install') {
                $controller->redirect()->toRoute('index', array('controller' => 'install', 'action' => 'index'));
            }

            $auth = new AuthenticationService();

            if ($auth->hasIdentity()) {
                $controller->user = $auth->getIdentity();
            } else {
                $controller->redirect()->toRoute('login');
            }
            
            $controller->db = $controller->getServiceLocator()->get('db');
            
            $controller->project  = $controller->db->query('
                SELECT p.*
                FROM Project p
                LEFT JOIN users u ON p.id=u.project_id
                WHERE u.id=:user
                ')->execute(array(
                    'user'=>$controller->user->id
                ))->current();

            $controller->projects = $controller->db->query('SELECT * FROM Project p WHERE p.user_id=:user')->execute(array('user'=>$controller->user->id));
            
            $controller->role     = $controller->db->query('
                SELECT r.role_id, r.role_name
                FROM Acl a
                LEFT JOIN Role r USING(role_id)
                WHERE a.user_id=:user and a.project_id=:project
                ')->execute(array(
                    'user'    => $controller->user->id,
                    'project' => $controller->project['id']
                ))->current();

            if($controller->user and $controller->role['role_name']!='admin' and $controller->role['role_name']!='staff') {
                $controller->redirect()->toRoute('home');
            }
            if (!empty($controller->project['theme'])) {
                $controller->layout = $controller->project['theme'];
            } else {
                if (empty($controller->layout)) $controller->layout = 'default';
            }

            $templatePathResolver = $serviceManager->get('Zend\View\Resolver\TemplatePathStack');
            $templatePathResolver->addPath(ROOT_PATH . '/public/themes/'.$controller->layout);

            if ($controller->project['zone']!='') {
                date_default_timezone_set($controller->project['zone']);
            } elseif($config['zone']!='') {
                date_default_timezone_set($config['zone']);
            } else {
                date_default_timezone_set('GMT');
            }
            
            if (isset($controller->user->id) and $controller->project['user_id']==$controller->user->id and $e->getRouteMatch()->getParam('design')!='') {
                $layout = $request->getParam('design');
            }

            // Log
            $controller->log = new \Admin\Lib\Log($controller->db, $controller->user->id);

            $viewModel = $app->getMvcEvent()->getViewModel();
            $viewModel->setVariable('user', $controller->user);
            $viewModel->setVariable('project', $controller->project);
            $viewModel->setVariable('role', $controller->role);
            $viewModel->controller = $e->getRouteMatch()->getParam('controller', 'index');
            $viewModel->action   = $e->getRouteMatch()->getParam('action', 'index');
            $viewModel->project  = $controller->project;
            $viewModel->projects = $controller->projects;
            $viewModel->services = $controller->db->query('SELECT s.*
                FROM Service s
                ORDER BY s.name')->execute();
            /*
            if (file_exists(__DIR__ . '/../../public/themes/' . $controller->layout . '/admin.phtml'))
                $controller->layout('admin');
            else
                $controller->layout('default/admin');
            */
            $controller->layout('admin');

        }, 100);
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'db' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    // $table     = new AlbumTable($dbAdapter);
                    return $dbAdapter;
                },
            ),
        );
    }

}

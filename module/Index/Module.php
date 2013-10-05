<?php

namespace Index;

use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\Event;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\ModuleRouteListener;
use Index\Model\Topic;

class Module {

    public function onBootstrap(Event $e) {

        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $app = $e->getParam('application');
        $sharedManager = $app->getEventManager()->getSharedManager();

        $sharedManager->attach(__NAMESPACE__, 'dispatch', function($e) {
            
                    $app = $e->getParam('application');
                    $controller = $e->getTarget();
                    $config = $app->getConfig();

                    $auth = new AuthenticationService();
                    if ($auth->hasIdentity()) {
                        $controller->user = $auth->getIdentity();
                    } else {
                        $controller->user = null;
                    }
                    
                    if (isset($config['install']) and $config['install'] == 0 and $e->getRouteMatch()->getParam('controller', 'index') != 'Index\Controller\Install') {
                        $controller->redirect()->toRoute('index', array('controller' => 'install', 'action' => 'index'));
                        // $e->stopPropagation();
                        return false;
                    } elseif ($e->getRouteMatch()->getParam('controller', 'index') != 'Index\Controller\Install') {
                        $controller->db = $controller->getServiceLocator()->get('db');
                        if ($controller->params('project', '') == '') {
                            $controller->project = $controller->db->query('
                                SELECT *
                                FROM Project p
                                WHERE id=1 and p.hide=0
                                ORDER BY p.id ASC
                                ')->execute()->current();
                        } else {
                            $controller->project = $controller->db->query('
                                SELECT *
                                FROM Project p
                                WHERE p.name=:name and p.hide=0
                                ')->execute(array('name' => $controller->params('project')))->current();
                        }
                    } else {
                        return false;
                    }

                    if (empty($controller->project) and $config['install'] == 1) {
                        // $controller->redirect()->toRoute('home');
                    }
                    
                    if (isset($controller->project['zone']) and $controller->project['zone'] != '') {
                        date_default_timezone_set($controller->project['zone']);
                    } elseif ($config['zone'] != '') {
                        date_default_timezone_set($config['zone']);
                    } else {
                        date_default_timezone_set('GMT');
                    }

                    if (isset($controller->project['theme']) and !empty($controller->project['theme'])) {
                        $controller->layout = $controller->project['theme'];
                    } elseif (isset($config['layout'])) {
                        $controller->layout = $config['layout'];
                        if (empty($controller->layout))
                            $controller->layout = 'default';
                    } else {
                        if (empty($controller->layout))
                            $controller->layout = 'default';
                    }

                    if (isset($controller->user->id) and isset($controller->project) and $controller->project['user_id'] == $controller->user->id and $e->getRouteMatch()->getParam('design') != '') {
                        $controller->layout = $controller->params('design');
                    }

                    $viewModel = $app->getMvcEvent()->getViewModel();
                    $viewModel->controller = $e->getRouteMatch()->getParam('controller', 'index');
                    $viewModel->action   = $e->getRouteMatch()->getParam('action', 'index');
                    $viewModel->user = $controller->user;
                    if (isset($viewModel->project))
                        $viewModel->project = $controller->project;

                    $controller->layout($controller->layout . '/layout');
                }, 100);
    }

    public function getAutoloaderConfig() {
        return array(
            /*
              'Zend\Loader\ClassMapAutoloader' => array(
              __DIR__ . '/autoload_classmap.php',
              ),
              'Zend\Loader\StandardAutoloader' => array(
              'namespaces' => array(
              __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
              ),
              ),
             
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
             */
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
        $mongo_factories = array(
            'db' => function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                return $dbAdapter;
            },
            'topic' => function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                return new Topic($dbAdapter);
            },
            'user' => function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                return new Topic($dbAdapter);
            },
        );
        $relationnal_factories = array(
            'db' => function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                return $dbAdapter;
            },
            'topic' => function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                return new Topic($dbAdapter);
            },
            'user' => function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                return new Topic($dbAdapter);
            },
        );
        return array(
            'factories' => $relationnal_factories
        );
    }

}

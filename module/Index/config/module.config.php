<?php
namespace Admin;

return array(
    'controllers' => array(
        'invokables' => array(
            'Index\Controller\Index'      => 'Index\Controller\IndexController',
            'Index\Controller\Auth'       => 'Index\Controller\AuthController',
            'Index\Controller\Contact'    => 'Index\Controller\ContactController',
            'Index\Controller\As'         => 'Index\Controller\AsController',
            'auth'    => 'Index\Controller\AuthController',
            'contact' => 'Index\Controller\ContactController',
            'Index\Controller\Install'    => 'Index\Controller\InstallController',
            'Index\Controller\Product'    => 'Index\Controller\ProductController',
            'Index\Controller\Basket'     => 'Index\Controller\BasketController',
            'Index\Controller\Newsletter' => 'Index\Controller\NewsletterController',
            'Index\Controller\Feed' => 'Index\Controller\FeedController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller' => 'Index\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'index' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/module[/:controller[/:action[/:id]]]',
                    'constraints' => array(
                        'module' => 'index',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller'    => 'Index\Controller\Index',
                        'action'        => 'index',
                    ),
                ),
            ),
            'product' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '[[/:category[/:name]_[:id].html]]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller'    => 'Index\Controller\Product',
                        'action'        => 'show',
                    ),
                ),
            ),
            'shop_category' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '[/:category]_[:id].html',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller'    => 'Index\Controller\Product',
                        'action'        => 'category',
                    ),
                ),
            ),
            'topicbyname' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '[/:project]/page[/:name].html',
                    'constraints' => array(
                        // 'name' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller' => 'Index\Controller\Index',
                        'action'     => 'topicbyname',
                    ),
                ),
            ),
            /*
            'topic' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/topic[/:name]',
                    'constraints' => array(
                        'name' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Index\Controller\Index',
                        'action'     => 'topic',
                    ),
                ),
            ),
             */
            'login' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/action/auth/login',
                    'defaults' => array(
                        'controller' => 'Index\Controller\Auth',
                        'action'     => 'login',
                    ),
                ),
            ),
            'signup' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/action/auth/signup',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller' => 'Index\Controller\Auth',
                        'action'     => 'signup',
                    ),
                ),
            ),
            
            'auth' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/action/auth[/:action[/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller' => 'Index\Controller\Auth',
                        'action'     => 'login',
                    ),
                ),
            ),
            
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
                'text_domain' => __NAMESPACE__,
            ),
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewFeedStrategy',
        ),
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'        => __DIR__ . '/../../../public/themes/default/layout.phtml',
            'index/index/index'    => __DIR__ . '/../view/index/index/index.phtml',
            'error/404'            => __DIR__ . '/../view/error/404.phtml',
            'error/index'          => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../../../public/themes/',
        ),
    ),
);
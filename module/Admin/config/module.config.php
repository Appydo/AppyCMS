<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index'      => 'Admin\Controller\IndexController',
            'Admin\Controller\Topic'      => 'Admin\Controller\TopicController',
            'Index\Controller\Index'      => 'Index\Controller\IndexController',
            'Admin\Controller\Comment'    => 'Admin\Controller\CommentController',
            'Admin\Controller\Css'        => 'Admin\Controller\CssController',
            'Admin\Controller\Design'     => 'Admin\Controller\DesignController',
            'Admin\Controller\Document'   => 'Admin\Controller\DocumentController',
            'Admin\Controller\Project'    => 'Admin\Controller\ProjectController',
            'Admin\Controller\Service'    => 'Admin\Controller\ServiceController',
            'Admin\Controller\Mail'       => 'Admin\Controller\MailController',
            'Admin\Controller\User'       => 'Admin\Controller\UserController',
            'Admin\Controller\Log'        => 'Admin\Controller\LogController',
            'Admin\Controller\Product'    => 'Admin\Controller\ProductController',
            'Admin\Controller\ShopAttribute' => 'Admin\Controller\ShopAttributeController',
            'Admin\Controller\ShopAttributeChoice' => 'Admin\Controller\ShopAttributeChoiceController',
            'Admin\Controller\Bill'        => 'Admin\Controller\BillController',
            'Admin\Controller\Ship'        => 'Admin\Controller\ShipController',
            'Admin\Controller\Devis'       => 'Admin\Controller\DevisController',
            'Admin\Controller\Delivery'    => 'Admin\Controller\DeliveryController',
            'Admin\Controller\Acl'         => 'Admin\Controller\AclController',
            'Admin\Controller\Auth'        => 'Admin\Controller\AuthController',
            'Admin\Controller\Database'    => 'Admin\Controller\DatabaseController',
            'Admin\Controller\GoogleShop'  => 'Admin\Controller\GoogleShopController',
            'Admin\Controller\Delay'       => 'Admin\Controller\DelayController',
            'Admin\Controller\Tax'         => 'Admin\Controller\TaxController',
            'Admin\Controller\EmailData'   => 'Admin\Controller\EmailDataController',
            'Admin\Controller\Resource'    => 'Admin\Controller\ResourceController',
            'Admin\Controller\ProductImageResize' => 'Admin\Controller\ProductImageResizeController',
            'Admin\Controller\Property'    => 'Admin\Controller\PropertyController',
            'Admin\Controller\Transaction' => 'Admin\Controller\TransactionController',
            'Admin\Controller\Translate'   => 'Admin\Controller\TranslateController',
            'Admin\Controller\Lang'        => 'Admin\Controller\LangController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'preview' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/[:project]',
                    'constraints' => array(
                        'project' => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller' => 'Index\Controller\Index',
                        'action'     => 'index'
                    ),
                ),
            ),
            
            'admin' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin[/:controller[/:action[/:id][/:dir]]]',
                    'constraints' => array(
                        'module' => 'admin',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'dir'     => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => 'Admin\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'image_show' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/document/show[/:path]',
                    'constraints' => array(
                        'module' => 'admin',
                        'dir'     => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => 'Admin\Controller\Document',
                        'action'     => 'show',
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
                // 'text_domain' => __NAMESPACE__,
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            // 'layout/layout'           => __DIR__ . '/../../../public/themes/default/admin.phtml',
            // 'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../../../public/themes/',
        ),
    ),
);
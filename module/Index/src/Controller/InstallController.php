<?php

namespace Index\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class InstallController extends AbstractActionController {

    public function indexAction() {
        $this->layout('simple/layout');
        // $this->layout('default/admin');
        $request = $this->getRequest();
        $layout = 'default';
        // $this->_helper->layout->setLayout($layout . '/layout');
        date_default_timezone_set('GMT');
        
        return array();
    }

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

    public function createAction() {
        $this->layout('simple/layout');
        $dir = __DIR__ . '/../../..';

        switch ($_POST['database']) {
            case 'sqlite':
                $pdo = 'pdo_sqlite';
                if (file_exists($dir . "/../data/{$_POST['dbname']}")) {
                    unlink($dir . "/../data/{$_POST['dbname']}");
                }
                $dbname = $dir . "/../data/{$_POST['dbname']}";
                $dsn = "'sqlite:' . getcwd() . '/data/{$_POST['dbname']}'";
                break;
            case 'postgresql':
                $pdo = 'pdo_pgsql';
                break;
            default:
                $pdo = 'pdo_mysql';
                $dbname = $_POST['dbname'];
                $dsn = "'mysql:dbname=$dbname;host={$_POST['host']}'";
                break;
        }

        $this->db = new \Zend\Db\Adapter\Adapter(array(
            'driver'   => $pdo,
            'hostname' => $_POST['host'],
            'database' => $dbname,
            'username' => $_POST['user'],
            'password' => $_POST['password']
         ));
       
        $sql_dir = 'data/sql/0.3.0/';
        $schemaSql = file_get_contents($dir . '/../'.$sql_dir.'/schema.' . $_POST['database'] . '.sql');
        $querys    = explode(";\n", $schemaSql);
        
        foreach($querys as $query) {
            if (trim($query)!='')
                $this->db->query($query)->execute();
        }

        $schemaSql = file_get_contents($dir . '/../'.$sql_dir.'/data.' . $_POST['database'] . '.sql');
        $querys    = explode(";\n", $schemaSql);
        
        foreach($querys as $query) {
            if (trim($query)!='')
                $this->db->query($query)->execute();
        }

        // $dataSql = file_get_contents(APPLICATION_PATH . '/../scripts/data.' . $_POST['database'] . '.sql');
        // $this->db->getConnection()->exec($dataSql);
        $this->createUser($_POST['username'], $_POST['userpassword'], $_POST['email']);

        if ($_POST['database'] == 'sqlite') {
            $dbname = 'APPLICATION_PATH "/../db/' . $_POST['dbname'] . '"';
        } else {
            $dbname = '"' . $_POST['zone'] . '"';
        }

        $config = "<?php
return array(
    'db' => array(
        'driver'         => '$pdo',
        'dsn'            => $dsn,
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
        'username' => '{$_POST['user']}',
        'password' => '{$_POST['password']}',
    ),
    'install' => 1,
    'version' => 0.3,
    'zone' => '{$_POST['zone']}',
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
);";
        /*
        $config = "[production]
install = 1
version = 0.2
layout  = \"default\"
phpSettings.display_startup_errors = 0
phpSettings.display_errors = 0
includePaths.library = APPLICATION_PATH \"/../library\"
bootstrap.path       = APPLICATION_PATH \"/Bootstrap.php\"
bootstrap.class      = \"Bootstrap\"
appnamespace         = \"Application\"
; resources.frontController.controllerDirectory = APPLICATION_PATH \"/controllers\"
resources.frontController.moduleDirectory = APPLICATION_PATH
resources.frontController.prefixDefaultModule = true
resources.frontController.defaultModule = \"default\"
resources.modules[] = \"\"
resources.frontController.params.displayExceptions = 0
resources.layout.layoutPath  = APPLICATION_PATH \"/../public/themes/\"
resources.db.adapter         = \"$pdo\"
resources.db.params.host     = \"{$_POST['host']}\"
resources.db.params.username = \"{$_POST['username']}\"
resources.db.params.password = \"{$_POST['password']}\"
resources.db.params.dbname   = {$dbname}
zone = \"{$_POST['zone']}\"
salt = \"9YYwGqQjNSrAiGa\"
resources.view.encoding = \"UTF-8\"
;resources.view[] =
;resources.view.scriptPath = \"/your/path/\"

[development : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1
resources.frontController.params.displayExceptions = 1";
*/
        return array(
            'config' => $config,
            'path'   => ROOT_PATH . 'config/localhost.php',
            'put'    => (@file_put_contents($this->view->path, $this->view->config)),
        );
    }

}


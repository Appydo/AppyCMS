<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DesignController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $design = $request->getPost('design');
            $user   = $this->db
                    ->query('SELECT project_id FROM users WHERE id=:user_id')
                    ->execute(array('user_id'=>$this->user->id))
                    ->current();
            $entity = $this->db
                    ->query('UPDATE Project SET theme=:design WHERE id=:id')
                    ->execute(array('design' => $design, 'id' => $user['project_id']));
            $this->project = $this->db
                    ->query('SELECT * FROM Project p WHERE p.id=:id and p.ban=0')
                    ->execute(array('id' => $user['project_id']))
                    ->current();
        }

        $dir = __DIR__ . '/../../../../public/themes';

        $tab = array();
        $descriptions = array();
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, array(".", "..", ".htaccess", '.DS_Store'))) {
                    array_push($tab, $entry);
                    if (file_exists($dir . '/' . $entry . '/description.txt')) {
                        $descriptions[$entry] = file_get_contents($dir . '/' . $entry . '/description.txt');
                    }
                }
            }
            closedir($handle);
        }

        return array(
            'listFiles' => $tab,
            'descriptions' => $descriptions,
            'project' => $this->project
        );
    }

    public function importAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\ImportThemeForm();
        $dir = __DIR__ . '/../../../../public/themes/' . $this->project['id'];

        // Create upload directory if not exist
        if (!is_dir($dir)) {
            @mkdir($dir);
            $uploadDirExit = is_dir($dir);
        } else {
            $uploadDirExit = true;
        }
        
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $path = __DIR__ . '/../../../../public/themes/' . $this->project['id'] . '/';
                move_uploaded_file($_FILES['document']['tmp_name'], $path.$_FILES['document']['name']);   
                // $log = new Log("Upload file", "File", $user);
                $zip = new \ZipArchive;
                $res = $zip->open($path.$_FILES['document']['name']);
                if ($res === TRUE) {
                    $zip->extractTo($dir);
                    $zip->close();
                    unlink($path.$_FILES['document']['name']);
                } else {

                }
            }
        }
        
        
        
        // List dir for repertory and image
        $tab = array();
        $sizes = array();
        if ($uploadDirExit) {
            if ($handle = opendir($dir)) {
                while (false !== ($entry = readdir($handle))) {
                    if (!in_array($entry, array('.', '..', '.DS_STORE'))) {
                        array_push($tab, $entry);
                        array_push($sizes, round(filesize($dir . '/' . $entry) / 1024));
                    }
                }
                closedir($handle);
            }
        }

        return array(
            'listFiles' => $tab,
            'form' => $form,
            'themeError' => false
        );
    }

}

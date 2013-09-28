<?php
namespace Admin\Controller;

use PHPImageWorkshop\ImageWorkshop;
use PHPImageWorkshop\Exception\ImageWorkshopBaseException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DocumentController extends AbstractActionController {

    public function indexAction() {

        $subdir = $this->params('dir');
        
        $request = $this->getRequest();
        $form = new \Admin\Form\DocumentForm();
        $form_dir = new \Admin\Form\DirForm();
        $dir = ROOT_PATH . '/public/uploads/'.$this->project['id'].'/';
        
        // if (!empty($_FILES)) {var_dump($_FILES);exit();}
        
        if ($request->isPost()) {
            
            $subdir = (isset($_POST['subdir'])) ? $_POST['subdir'] : $subdir ;
            
            if (strpos($subdir,'..')!==false) $subdir = '';
            
            // Create dir
            $form_dir->setData($request->getPost());
            if (!empty($_POST['dir_csrf']) and $form_dir->isValid()) {
                if (!file_exists($dir . $subdir . '/' . $_POST['directory']))
                    mkdir($dir . $subdir . '/' . $_POST['directory']);
            }

            // New upload form
            $form->setData($request->getPost());
            if (!empty($_POST['document_csrf']) and $form->isValid()) {
                if (!file_exists($dir . $subdir . '/'))
                    mkdir($dir . $subdir . '/');
                $path = $dir . $subdir . '/';     
                move_uploaded_file($_FILES['document']['tmp_name'], $path.$_FILES['document']['name']);   
                // $log = new Log("Upload file", "File", $user);
            }
        }

        // Create upload directory if not exist
        if (!is_dir($dir)) {
            @mkdir($dir);
            $uploadDirExit = is_dir($dir);
        } else {
            $uploadDirExit = true;
        }

        // List dir for repertory and image
        $tab = array();
        $sizes = array();
        if ($uploadDirExit) {
            if ($handle = opendir($dir . '/' . $subdir)) {
                while (false !== ($entry = readdir($handle))) {
                    if (!in_array($entry, array('.', '..', '.DS_STORE'))) {
                        array_push($tab, $entry);
                        array_push($sizes, round(filesize($dir . $subdir . '/' . $entry) / 1024));
                    }
                }
                closedir($handle);
            }
        }
        
        return array(
            'uploadDirExit' => $uploadDirExit,
            'form' => $form,
            'form_dir' => $form_dir,
            'listFiles' => $tab,
            'sizes' => $sizes,
            'subdir' => $subdir
        );
    }
    
    function showAction() {
        
        require_once ROOT_PATH . '/vendor/PHPImageWorkshop/ImageWorkshop.php';
        $result = array();
        $result['file'] = $this->params('path');
        $result['form'] = new \Admin\Form\ImageForm();
        
        $request = $this->getRequest();
        
        $dir = ROOT_PATH . '/public/uploads/'.$this->project['id'].'/';
        if ($request->getPost('resize')) {
            
            $width  = ($request->getPost('width')!='')  ? $request->getPost('width')  : null ;
            $height = ($request->getPost('height')!='') ? $request->getPost('height') : null ;
            if ($width!=null or $height!=null) {
                $img = ImageWorkshop::initFromPath($dir . $result['file']);
                $img->resizeInPixel($width, $height, true);
                $img->save(dirname($dir . $result['file']), basename($dir . $result['file']), true, null, 95);
            }
        }
        
        if ($request->getPost('reverse')) {
            $img = ImageWorkshop::initFromPath($dir . $result['file']);
            $img->applyFilter(IMG_FILTER_NEGATE);
            $img->save(dirname($dir . $result['file']), basename($dir . $result['file']), true, null, 95);
        }
        
        if ($request->getPost('filter')) {
            $img = ImageWorkshop::initFromPath($dir . $result['file']);
            $img->applyFilter(IMG_FILTER_CONTRAST, -15, null, null, null, true);
            $img->applyFilter(IMG_FILTER_BRIGHTNESS, 8, null, null, null, true);
            $img->save(dirname($dir . $result['file']), basename($dir . $result['file']), true, null, 95);
        }
        
        if ($request->getPost('heart')) {
            $img = ImageWorkshop::initFromPath($dir . $result['file']);
            $text = ImageWorkshop::initTextLayer('G & J', $dir . 'layer/angel.ttf', 48, 'B40404', 45);
            $heart = ImageWorkshop::initFromPath($dir . 'layer/heart.png');
            $heart->resizeInPixel(128, 128, true);
            $img->addLayerOnTop($heart, 20, 10, 'RB');
            $img->addLayerOnTop($text, 20, 10, 'TB');
            $img->save(dirname($dir . $result['file']), basename($dir . $result['file']), true, null, 95);
        }
        
        return $result;

    }

    function delete($filename) {
        
    }

    function doAction() {
        
        $errors = array();

        $request = $this->getRequest();
        $delete  = $request->getPost('delete');
        $subdir  = (isset($_POST['subdir'])) ? $_POST['subdir'] : '' ;
        $dir     = ROOT_PATH . '/public/uploads/'.$this->project['id'].'/'.$subdir.'/';
        
        
        if (!empty($delete)) {
            if (is_dir($dir . $delete))
                rmdir($dir . $delete);
            else {
                if (@unlink($dir . $delete)==false)
                    $errors['Suppression'] = 'Impossible de supprimer le fichier ' . $delete;
            } 
                
        }

        $rename  = $request->getPost('rename');
        $rename_text  = $request->getPost('rename-text');
        if (!empty($rename) and !empty($rename_text)) {
            rename($dir . $rename, $dir . '/' . $rename_text);
        }

        return $this->redirect()->toRoute('admin', array(
                    'controller' => 'document',
                    'action' => 'index',
                ));

    }

}

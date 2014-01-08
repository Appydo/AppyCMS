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
                $log = $this->log(__METHOD__, "File", $user);
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
        
        if ($request->getPost('rotateLeft')) {
            $degrees = 90;
            $source = imagecreatefromjpeg($dir . $result['file']);
            $rotate = imagerotate($source, $degrees, 0);
            imagejpeg($rotate, $dir . $result['file'], 95);
            imagedestroy($source);
            imagedestroy($rotate);
        }

        if ($request->getPost('rotateRight')) {
            $degrees = 270;
            $source = imagecreatefromjpeg($dir . $result['file']);
            $rotate = imagerotate($source, $degrees, 0);
            imagejpeg($rotate, $dir . $result['file'], 95);
            imagedestroy($source);
            imagedestroy($rotate);
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

    function uploadAction() {
        /**
         * upload.php
         *
         * Copyright 2013, Moxiecode Systems AB
         * Released under GPL License.
         *
         * License: http://www.plupload.com/license
         * Contributing: http://www.plupload.com/contributing
         */

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        /* 
        // Support CORS
        header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }
        */

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Settings
        $targetDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $this->project['id'];
        //$targetDir = 'uploads';
        $cleanupTargetDir = false; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }   


        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {    
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);
        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off 
            rename("{$filePath}.part", $filePath);
        }
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
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

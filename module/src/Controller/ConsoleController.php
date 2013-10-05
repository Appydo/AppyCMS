<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use RuntimeException;
use Zend\View\Helper\AbstractHelper,
    Zend\View\Helper\Url;

class ConsoleController extends AbstractActionController {

    public function generateAction() {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        $columns = $metadata->getTable($this->params('table'))->getColumns();
        
        $title = '';
        foreach($columns as $key=>$value) {
            $title .= "\t\t<th>" . $value->getName() . "</th>\n";
        }
        
        $row = '';
        foreach($columns as $key=>$value) {
            $row .= "\t\t<tr><?php echo \$entity['" . $value->getName() . "']; ?></tr>\n";
        }
        
        $index = <<<EOT
<table class="table table-striped">
   
    <thead>
$title
    </thead>
    
    <tbody>
$row
    </tbody>
</table>

<form method="post" action="">
    <button type="submit" class="btn btn-primary">
        <i class="icon-plus icon-white"></i>
        Ajouter une commande
    </button>
</form>";
EOT;
        
$content = '';
foreach($columns as $key=>$value) {
    $content .= "\t<p><label>{$value->getName()}</label> <?php echo \$entity['" . $value->getName() . "']; ?></p>\n";
}
        
$index = <<<EOT

$content

<form method="post" action="">
    <button type="submit" class="btn btn-primary">
        <i class="icon-plus icon-white"></i>
        Ajouter une commande
    </button>
</form>";
EOT;

$content = '';
foreach($columns as $key=>$value) {
    $content .= "\t<p>\n\t\t<label>{$value->getName()}</label>\n\t\t<input name='{$value->getName()}' type='text' value='<?php echo \$entity['" . $value->getName() . "']; ?>' />\n\t</p>\n";
}
        
$index = <<<EOT

$content

<form method="post" action="">
    <button type="submit" class="btn btn-primary">
        <i class="icon-plus icon-white"></i>
        Ajouter une commande
    </button>
</form>";
EOT;

        // {$this->view->url('admin', array('controller'=>$this->params('table'),'action'=>'index'))}
        return $index;
    }

}


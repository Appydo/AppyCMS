<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CssController extends AbstractActionController {
    
    public function indexAction()
    {
        $request = $this->getRequest();
        $form = new \Admin\Form\CssForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update = $this->db
							->query('UPDATE Project SET css=:css WHERE id=:id')
							->execute(array(
                    'css' => $request->getPost('css'),
                    'id' => $this->project['id']
                    ));
                $this->project = $this->db
					->query('SELECT * FROM Project p WHERE p.id=:id and p.hide=0')
					->execute(array('id' => $this->project['id']))
					->current();
            }
        }
        
        

        return array(
            'form' => $form,
            'project' => $this->project
        );

    }

}

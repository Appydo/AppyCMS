<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class DirForm extends Form {
    public function __construct($name = null)
    {
        parent::__construct('document');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'directory',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Directory',
            ),
        ));
        $this->add(
                new Element\Csrf('dir_csrf')
        );
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Valider',
                'id' => 'submitbutton',
                'class' => 'btn'
            ),
        ));
    }

}
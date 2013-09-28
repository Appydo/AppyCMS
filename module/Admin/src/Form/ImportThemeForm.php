<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ImportThemeForm extends Form {
    
    public function __construct($name = null) {
        parent::__construct('design');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'file',
            'attributes' => array(
                'type'  => 'document',
            ),
            'options' => array(
                'label' => 'Theme',
            ),
        ));
        $this->add(
                new Element\Csrf('csrf')
        );
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Login',
                'id' => 'submitbutton',
                'class' => 'btn'
            ),
        ));
    }

}
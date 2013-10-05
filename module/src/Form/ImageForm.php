<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ImageForm extends Form {
    
    public function __construct($name = null)
    {
        parent::__construct('document');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        
        $this->add(array(
            'name' => 'width',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Width',
            ),
        ));
        
        $this->add(array(
            'name' => 'height',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Height',
            ),
        ));

        $this->add(
                new Element\Csrf('document_csrf')
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
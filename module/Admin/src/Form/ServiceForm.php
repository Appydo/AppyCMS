<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ServiceForm extends Form {
    
    public function __construct($name = null)
    {
        parent::__construct('service');
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
            'name' => 'link',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Link',
            ),
        ));
        $this->add(array(
            'name' => 'type',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Type',
            ),
        ));
        $this->add(
                new Element\Csrf('service_csrf')
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
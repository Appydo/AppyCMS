<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ResourceForm extends Form {
    public function __construct($name = null)
    {
        parent::__construct('resource');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'resource_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));

        $this->add(
                new Element\Csrf('csrf')
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
<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class CssForm extends Form {

    public function __construct($name = null)
    {
        parent::__construct('css');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'css',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Css',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 10000
                )
            )
        ));
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
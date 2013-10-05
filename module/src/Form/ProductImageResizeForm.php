<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ProductImageResizeForm extends Form {

    public function __construct($name = null) {
        parent::__construct('productimageresize');
        $this->setAttribute('method', 'post');
        $prefix = 'pir_';

        $this->add(array(
            'name' => $prefix . 'id',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'id',
            ),
        ));
        $this->add(array(
            'name' => $prefix . 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        $this->add(array(
            'name' => $prefix . 'width',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Width',
            ),
        ));
        $this->add(array(
            'name' => $prefix . 'height',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Height',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => $prefix . 'hide',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Hide',
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
                'value' => 'Submit',
                'id' => 'submitbutton',
                'class' => 'btn'
            ),
        ));
    }

}
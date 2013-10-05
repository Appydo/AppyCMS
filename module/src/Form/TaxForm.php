<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class TaxForm extends Form {

    public function __construct($name = null) {
        parent::__construct('tax');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'tax_id',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Tax id',
            ),
        ));

        $this->add(array(
            'name' => 'tax_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        $this->add(array(
            'name' => 'tax_value',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Value',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'hide',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'useHiddenElement' => true,
            'options' => array(
                'label' => 'Hide',
                'checkedValue'   => true,
                'uncheckedValue' => false,
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
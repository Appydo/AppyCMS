<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class DeliveryForm extends Form {

    public function __construct($name = null) {
        parent::__construct('project');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'delivery_id',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Delivery id',
            ),
        ));
        $this->add(array(
            'name' => 'delivery_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        $this->add(array(
            'name' => 'price',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Price',
            ),
        ));
        $this->add(array(
            'name' => 'delivery_delay',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Delay',
            ),
        ));
        $this->add(array(
            'name' => 'delivery_rule',
            'attributes' => array(
                'type'  => 'textarea',
            ),
            'options' => array(
                'label' => 'Rule',
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
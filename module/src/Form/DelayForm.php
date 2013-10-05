<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class DelayForm extends Form {

    public function __construct($name = null) {
        parent::__construct('project');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'delay_id',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Delay id',
            ),
        ));
        $this->add(array(
            'name' => 'delay_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        $this->add(array(
            'name' => 'delay',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Delay',
            ),
        ));
        $this->add(array(
            'name' => 'delay_rule',
            'attributes' => array(
                'type'  => 'textarea',
            ),
            'options' => array(
                'label' => 'Rule',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'hide',
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
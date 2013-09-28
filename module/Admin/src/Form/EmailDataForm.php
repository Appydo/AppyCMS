<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class EmailDataForm extends Form {

    public function __construct($name = null) {
        parent::__construct('EmailData');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'ed_id',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Email Data id',
            ),
        ));

        $this->add(array(
            'name' => 'ed_title',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Title',
            ),
        ));
        $this->add(array(
            'name' => 'ed_content',
            'attributes' => array(
                'type'  => 'textarea',
            ),
            'options' => array(
                'label' => 'Content',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'ed_hide',
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
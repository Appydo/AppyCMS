<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class LinkForm extends Form {

    public function __construct($name = null) {
        parent::__construct('topic');
        $this->setAttribute('method', 'post');

        $this->addElement('text', 'name', array(
            'required' => true,
            'filters' => array('StringTrim'),
        ));
        $this->addElement('password', 'link', array(
            'required' => true,
        ));
        $this->addElement('checkbox', 'hide', array(
        ));
        $this->addElement('select', 'parent', array(
        ));
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Login',
        ));
    }

}
<?php

class LoginForm extends Zend {

    public function __construct($name = null)
    {
        parent::__construct('users');
        $this->setAttribute('method', 'post');
        $this->addElement('text', 'name', array(
            'label' => 'Username:',
            'required' => true,
            'filters' => array('StringTrim'),
        ));
        $this->addElement('password', 'content', array(
            'label' => 'Password:',
            'required' => true,
        ));
        $this->addElement('checkbox', 'hide', array(
            'label' => 'Hide:'
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
<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ContactForm extends Form {

    public function __construct($name = null) {
        parent::__construct($name);
        
        $this->setAttribute('method', 'post');

        $element = new Element('blacklist_id');
        $element->setLabel('ID');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($element);

        $element = new Element('blacklist_name');
        $element->setLabel('Name');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($element);

        $element = new Element('blacklist_email');
        $element->setLabel('Email');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($element);

        $element = new Element('blacklist_description');
        $element->setLabel('Description');
        $element->setAttributes(array(
            'type'  => 'textarea'
        ));
        $this->add($element);

        $element = new Element\Checkbox('hide');
        $element->setLabel('Hide');
        $this->add($element);

        $csrf = new Element\Csrf('csrf');
        $this->add($csrf);

        $send = new Element('submit');
        $send->setValue('Submit');
        $send->setAttributes(array(
            'type'  => 'submit'
        ));
        $this->add($send);
    }

}
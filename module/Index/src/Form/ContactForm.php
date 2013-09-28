<?php
namespace Index\Form;

use Zend\Form\Form;
use Zend\Form\Element;


class ContactForm extends Form {
    
    public function __construct($name = null)
    {
        parent::__construct('contact');
        $this->setAttribute('method', 'post');
        
        $element = new Element\Text('contact_name');
        $element->setLabel('Name');
        $this->add($element);
        
        $element = new Element\Text('contact_email');
        $element->setLabel('Email');
        $this->add($element);

        $element = new Element\Text('contact_phone');
        $element->setLabel('Phone');
        $this->add($element);
        
        $element = new Element\Textarea('contact_message');
        $element->setLabel('Message');
        $this->add($element);
        
        $this->add(
                new Element\Csrf('csrf')
        );
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Send',
                'id' => 'submitbutton',
                'class' => 'btn'
            ),
        ));
    }

}
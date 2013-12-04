<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class LangForm extends Form {

    public function __construct($name = null) {
        parent::__construct($name);
        
        $this->setAttribute('method', 'post');

        $element = new Element('lang_id');
        $element->setLabel('ID');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($element);

        $element = new Element('lang_key');
        $element->setLabel('Key');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($element);

        $element = new Element('lang_value');
        $element->setLabel('Value');
        $element->setAttributes(array(
            'type'  => 'text'
        ));
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
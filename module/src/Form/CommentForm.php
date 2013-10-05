<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class CommentForm extends Form {

     public function __construct($name = null)
    {
        parent::__construct('comment');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'content',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Content',
            ),
        ));
        /*
        $this->addElement('text', 'parent', array(
            'label' => 'Parent:',
            'filters' => array('Int', 'Null'),
        ));
         * 
         */
        $this->add(array(
            'name' => 'hide',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Hide',
            ),
        ));
        $this->add(array(
            'name' => 'config',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Config',
            ),
        ));
        $this->add(
                new Element\Csrf('csrf')
        );
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Valider',
                'id' => 'submitbutton',
                'class' => 'btn'
            ),
        ));
    }

}
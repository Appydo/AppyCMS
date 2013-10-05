<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class NoteForm extends Form {

    public function __construct($name = null) {
        parent::__construct('project');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'note',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Note :',
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
<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class BillForm extends Form {
    public function __construct($name = null)
    {
        parent::__construct('bill');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'count',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Count',
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Description',
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
            'name' => 'payment',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Payment',
            ),
        ));
        
        $this->add(array(
            'name' => 'created',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Created',
            ),
        ));
        
        $this->add(array(
            'name' => 'updated',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Updated',
            ),
        ));
        
        $this->add(array(
            'name' => 'hide',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Hide',
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
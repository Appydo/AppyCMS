<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ProductForm extends Form {

    public function __construct($name = null)
    {
        parent::__construct('product');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
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
            'name' => 'stock',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Stock',
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
            'name' => 'discount',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Discount',
            ),
        ));

        $this->add(array(
            'name' => 'weight',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Weight',
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
            'name' => 'hide',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Hide',
            ),
        ));
        
        $this->add(array(
            'name' => 'comment',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Comment',
            ),
        ));
        
        $this->add(array(
            'name' => 'parent',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Parent',
            ),
        ));
        
        $this->add(array(
            'name' => 'file',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'File',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 2000
                )
            )
        ));
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
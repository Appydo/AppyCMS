<?php
namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ProjectForm extends Form {
    public function __construct($name = null)
    {
        parent::__construct('project');
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
                'type'  => 'textarea',
            ),
            'options' => array(
                'label' => 'Description',
            ),
        ));
        $this->add(array(
            'name' => 'url',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Url',
            ),
        ));
        $this->add(array(
            'name' => 'keywords',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Keywords',
            ),
        ));
        $this->add(array(
            'name' => 'banner',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Banner',
            ),
        ));
        $this->add(array(
            'name' => 'footer',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Footer',
            ),
        ));
        $this->add(array(
            'name' => 'subtitle',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Subtitle',
            ),
        ));
        $this->add(array(
            'name' => 'maintenance',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Maintenance',
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
            'name' => 'menu',
            'attributes' => array(
                'type'  => 'checkbox',
            ),
            'options' => array(
                'label' => 'Menu',
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
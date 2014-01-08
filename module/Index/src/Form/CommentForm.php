<?php
namespace Index\Form;

use Zend\Form\Element;

use Zend\Form\Form,
    Zend\Form\Element\Captcha,
    Zend\Captcha\Image as CaptchaImage;

class CommentForm extends Form {

    public function __construct($name = null)
    {
        parent::__construct('comment');
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
            'name' => 'content',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Content',
            ),
        ));
		/*
		$dirdata = './public/uploads';

		//pass captcha image options
		$captchaImage = new CaptchaImage(  array(
			'font' => './public/themes/default/fonts/arial.ttf',
			'width' => 250,
			'height' => 100,
			'dotNoiseLevel' => 40,
			'lineNoiseLevel' => 3)
		);
		$captchaImage->setImgDir($dirdata.'/captcha');
		$captchaImage->setImgUrl($urlcaptcha);

		//add captcha element...
		$this->add(array(
		'type' => 'Zend\Form\Element\Captcha',
		'name' => 'captcha',
		'options' => array(
		'label' => 'Please verify you are human',
		'captcha' => $captchaImage,
		),
		));
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
                'value' => 'Valider',
                'id' => 'submitbutton',
                'class' => 'btn'
            ),
        ));
    }
}

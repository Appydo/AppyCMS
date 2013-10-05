<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class MailjetController extends AbstractActionController {

    private $table = 'EmailData';
    
    public function sendAction() {
        $result = array();
        $result['emails']  = $this->db->query('SELECT u.* FROM users u')
                    ->execute(array(
                        'project' => $this->project['id'],
                        ));
        return $result;
    }
    
    public function indexAction() {
        
        // Include Mailjet's API Class
		include_once(ROOT_PATH . '/module/Admin/src/Vendor/php-mailjet.class-mailjet-0.1.php');
		 
		// Create a new Object
		$mj = new \Mailjet('9dd2d2d3231c124c88271b553a958edf','41cce68dc59aa9a7e4610ac61b2850eb');
		$response = $mj->messageList();
		$messages = $response->result;
		

		foreach($messages as $key=>$message) {
			$params = array(
			    'id' => $message->id
			);
			$response = $mj->messageContacts($params);
			foreach($response->result as $item) {
			   $mc = $item;
			   break; // or exit or whatever exits a foreach loop...
			} 
			$messages[$key]->email = $mc->email;
			$messages[$key]->send_at = $mc->send_at;
			$messages[$key]->status = $mc->status;
		}

		$response = $mj->reportEmailStatistics();

        return array(
        	'$statistics' => $response->stats,
            'messages' => $messages,
        );

    }

}

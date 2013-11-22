<?php
namespace Index\Services;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class Sendmail
{
    function getIP() {
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "UNKNOWN";
        return $ip;
    }
    
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function sendEmailId($to, $from, $id, $vars, $notification = true) {
        $message = $this->db->query('SELECT ed_title, ed_content FROM EmailData WHERE ed_id=:id')->execute(array('id'=>$id))->current();
        // email template engine
        foreach($vars as $key=>$var) {
            $message['ed_content'] = str_replace('{{'.$key.'}}', $var, $message['ed_content']);
        }
        $this->send($to, $from, $message['ed_title'], $message['ed_content'], $notification);
    }    
    
    public function send($user, $from, $subject, $message, $notification = true) {
        if ($notification) {
            $insert_args = array();
            $insert_args['project_id'] = 1;
            $insert_args['user_id'] = $user;
            $insert_args['from_id'] = $from;
            $insert_args['name'] = $subject;
            $insert_args['message'] = $message;
            $insert_args['hide'] = 0;
            $insert_args['ip'] = $this->getIp();
            $insert_args['created'] = time();
            $insert_args['updated'] = time();
            $insert = $this->db->query(
            "INSERT INTO Mail (".implode(",", array_keys($insert_args)).")
            VALUES (:".implode(",:", array_keys($insert_args)).")",
                $insert_args
                );
        }
        $email = $this->db->query('SELECT email FROM users WHERE id=:id')
                ->execute(array('id'=>$user))
                ->current();
        $this->mail($email['email'], $subject, $message);
    }
    
	public function mail($to, $subject, $html) {

		$email = $this->db->query('SELECT name, email FROM Project WHERE id=:id')
				->execute(array('id'=>1))
                ->current();
        
        $htmlPart = new MimePart(
            '<html>'
            .'<body style="line-height:1.2em;background-color:white;font:15x HelveticaNeue-Light, "Helvetica Neue Light", "Helvetica Neue", "Helvetica Light", Helvetica, Arial, "Lucida Grande", sans-serif!important;">'
            .'<div style="background:#dfdfdf;padding-left:10px;padding-right:10px;padding-top:10px;border-radius: 20px 20px 0 0;border:solid 1px #dedede;">'
            .'<div style="text-align:center;margin-bottom: 2em;float:left;"><img src="http://avisiter.fr/themes/immobilier-en-reseau.fr/img/logo_avisiter.png" alt="logo avisiter.fr" /></div>'
            .'<div style="float:right;padding-top:25px;"><a style="color:black;text-decoration:none;" href="http://avisiter.fr">Accueil</a> &nbsp;|&nbsp; <a style="color:black;text-decoration:none;" href="http://avisiter.fr/plugin/annonces/new">Déposer mon bien</a> &nbsp;|&nbsp; <a style="color:black;text-decoration:none;" href="http://avisiter.fr/plugin/annonces/network">Déposer ma recherche</a></div>'
            .'<div style="clear:both;"></div>'
            .'</div><div style="border:solid 1px #dedede;background:#fbfbfb;padding:20px;border-radius: 0 0 20px 20px;border:solid 1px #dedede;">'
            .'<div style="background:white;padding:20px;border-radius:20px;border:solid 1px #dedede;">'
            .$html
            .'</div>'
            .'</div></body></html>');

        $htmlPart->type = "text/html";

        $textPart = new MimePart(strip_tags($html));
        $textPart->type = "text/plain";

        $body = new MimeMessage();
        // $body->setParts(array($htmlPart, $textPart));
        $body->setParts(array($htmlPart));

        $message = new Message();
        $message->setBody($body);
        $message->setEncoding('UTF-8');
        $message->addFrom($email['email'], $email['name']);
        $message->addTo($to);
        $message->setSubject($subject);
        // $message->getHeaders()->get('content-type')->setType('text/html');

        // $transport = new \Zend\Mail\Transport\Sendmail();
		$transport = new \Zend\Mail\Transport\Smtp();

		// Setup SMTP transport using LOGIN authentication
		// $transport = new SmtpTransport();
		$options   = new \Zend\Mail\Transport\SmtpOptions(array(
		    'name'              => 'avisiter.fr',
		    'host'              => 'in.mailjet.com',
			'port'              => 587,
		    'connection_class'  => 'plain',
		    'connection_config' => array(
		        'username' => '9dd2d2d3231c124c88271b553a958edf',
		        'password' => '41cce68dc59aa9a7e4610ac61b2850eb',
				'ssl'      => 'tls',
		    ),
		));
		$transport->setOptions($options);

        try {
            $transport->send($message);
        } catch (Exception $exc) {
            return false;
        }

    }

}
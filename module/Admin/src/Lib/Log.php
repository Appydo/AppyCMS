<?php
namespace Admin\Lib;

class Log {

	/**
     * Error severity, from low to high. From BSD syslog RFC, secion 4.1.1
     * @link http://www.faqs.org/rfcs/rfc3164.html
     */
    const EMERG  = 0;  // Emergency: system is unusable
    const ALERT  = 1;  // Alert: action must be taken immediately
    const CRIT   = 2;  // Critical: critical conditions
    const ERR    = 3;  // Error: error conditions
    const WARN   = 4;  // Warning: warning conditions
    const NOTICE = 5;  // Notice: normal but significant condition
    const INFO   = 6;  // Informational: informational messages
    const DEBUG  = 7;  // Debug: debug messages

    const NO_ARGUMENTS = '';

	private $db;
	private $user;
	private $output;

	public function slugify($str){
		return mb_strtolower(preg_replace(array('/[^a-zA-Z0-9 \'-\.]/', '/[ -\']+/', '/^-|-$/'),
			array('', '-', ''), $this->remove_accent($str)));
	}

	public function __construct($db, $user = null, $output = 'db') {
		$this->db = $db;
		$this->user = $user;
		$this->output = $output;
	}

	public function debug($line, $args = self::NO_ARGUMENTS) {
		$this->log($line, self::DEBUG, $args);
	}

	public function info($line, $args = self::NO_ARGUMENTS) {
		$this->log($line, self::INFO, $args);
	}

	public function notice($line, $args = self::NO_ARGUMENTS) {
		$this->log($line, self::NOTICE, $args);
	}

	public function warning($line, $args = self::NO_ARGUMENTS) {
		$this->log($line, self::WARN, $args);
	}

	public function error($line, $args = self::NO_ARGUMENTS) {
		$this->log($line, self::ERR, $args);
	}

	public function critique($line, $args = self::NO_ARGUMENTS) {
		$this->log($line, self::CRIT, $args);
	}

	public function alert($line, $args = self::NO_ARGUMENTS) {
		$this->log($line, self::ALERT, $args);
	}

	public function emergency($line, $args = self::NO_ARGUMENTS) {
		$this->log($line, self::EMERG, $args);
	}

	public function log($line, $severity = self::DEBUG, $args = self::NO_ARGUMENTS)
    {
		$insert_args = array();
		$insert_args['line']     = $line;
		$insert_args['severity'] = $severity;
		$insert_args['user_id']  = $this->user;
		$insert_args['args']     = implode($args);
		$this->insert($insert_args);
    }

	private function insert($insert_args) {
		if ($this->output == 'db') {
		    $insert_args['created']	= time();

		    return $this->db->query(
		        "INSERT INTO Log (".implode(",", array_keys($insert_args)).")
		        VALUES (:".implode(",:", array_keys($insert_args)).")",
		            $insert_args
		            );
		}
	}
}

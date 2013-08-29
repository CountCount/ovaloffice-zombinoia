<?php
include_once 'dal.php';
$db = new Database();

// functions
include_once 'lang.inc.php';
include_once 'functions.php';

/** 
 * Logging class: 
 * - contains lopen and lwrite methods 
 * - lwrite will write message to the log file 
 * - first call of the lwrite will open log file implicitly 
 * - message is written with the following format: hh:mm:ss (script name) message 
 */  
class Logging{  
  // define log file  
  private $log_file = 'error/oo_error.txt';  
  // define file pointer  
  private $fp = null;  
  // write message to the log file  
  public function lwrite($message){  
    // if file pointer doesn't exist, then open log file  
    if (!$this->fp) $this->lopen();  
    // define script name  
    $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME) . '.' . pathinfo($_SERVER['PHP_SELF'], PATHINFO_EXTENSION);  
    // define current time  
    $time = date('H:i:s');  
    // write current time, script name and message to the log file  
    #fwrite($this->fp, "$time ($script_name) $message\n");
		fwrite($this->fp, "$time ($script_name) $message\n".debug_print_backtrace()."\n\n");  		
  }  
  // open log file  
  private function lopen(){  
    // define log file path and name  
    $lfile = $this->log_file;  
    // define the current date (it will be appended to the log file name)  
    $today = date('Y-m-d',time());  
    // open log file for writing only; place the file pointer at the end of the file  
    // if the file does not exist, attempt to create it  
    $this->fp = fopen($lfile . '_' . $today, 'a') or exit("Can't open $lfile!");  
  }  
	public function set_log_file($f) {
		$this->log_file = $f;
	}
} 

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	// Logging class initialization  
	$log = new Logging();  
	// write message to the log file  
	$log->lwrite(' #' . $errno . ': ' . $errstr . ' IN FILE ' . $errfile . ' AT LINE ' . $errline);
  #throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	#print 'Fehler in der Anwendung';
	exit;
}
#set_error_handler("exception_error_handler");

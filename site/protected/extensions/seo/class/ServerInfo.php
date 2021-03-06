<?php

require_once SEO_PATH_HELPERS.'Utility.php';

class ServerInfo{
	/**
	 * @ignore
	 * @var unknown
	 */
	protected $url;
	
	/**
	 * @ignore
	 * @var unknown
	 */
	public $rawHeader;
	
	/**
	 * @ignore
	 * @var unknown
	 */
	private $response;
	
	/**
	 * @ignore
	 * @var unknown
	 */
	public $header;
	
	/**
	 * @ignore
	 * @var unknown
	 */
	private $loadTime;
	
	/**
	 * @ignore
	 * @param unknown $url
	 */
	public function __construct($url){
		$this->url = $url;	
		$this->doRequest();
		$this->parseHeader();
	} 
	
	//TODO:check for robots.txt
	
	/**
	 * Just wraps making curl requests
	 * @ignore
	 */
	private function doRequest(){
		
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept-Encoding: gzip, deflate'
		));
		
		$start = Utility::getTime();
		
		$this->rawHeader = curl_exec($ch);
		
		$this->loadTime = Utility::getEnd($start);
		
		curl_close($ch);
	}
	
	/**
	 * Get the page load time in seconds
	 */
	public function getLoadTime(){
		return $this->loadTime;
	}
	
	/**
	 * Parse header, first line is put $this->request and
	 * other headers are stored in $this->header as key:value
	 * pairs where the key has been mad lowercase.
	 *    - strtolower(<field name>) => <value>
	 *    
	 * @ignore
	 */
	private function parseHeader(){
		$lines = explode("\n", $this->rawHeader);
		$this->response = rtrim(array_shift($lines),"\r");
		
		foreach($lines as $line){
			$line = trim($line);
			$parts = explode(':', $line);
			if(count($parts) > 0){
				$key = strtolower(array_shift($parts));
				if(!empty($key) && count($parts) > 0)
					$this->header[$key] = trim(implode(':', $parts));
			}
		}
	}
	
	/**
	 * Check if the server supports gzip compression
	 * @return boolean True if is does, false otherwise
	 */
	public function isGzip(){
		if(!isset($this->header['content-encoding']))
			return false;
		else
			return (preg_match('/gzip/i',$this->header['content-encoding']));
	}
	
	/**
	 * Return the HTTP "Server" field name or NULL if none existed.
	 * 
	 * @return String|NULL
	 */
	public function getServer(){
		return $this->getHeaderField('server');
	}
	
	
	/**
	 * Get any header field returned by server
	 * @param unknown $field
	 * @return String|NULL
	 */
	public function getHeaderField($field){
		$field = strtolower($field);
		return (isset($this->header[$field]) ? $this->header['server'] : null);
	}
	
	/**
	 * Get the first line of http response header
	 */
	public function getHeaderResponseLine(){
		return $this->response;
	}
	
	/**
	 * Check if site has robots.txt file
	 * @return boolean
	 */
	public function checkRobots(){
		$info = parse_url($this->url);
        $result = false;
        try{
		    $result = file_get_contents('http://'.$info['host'].'/robots.txt');
        }catch(Exception $e){
            //do nothing
        }

		return ($result === false) ? false : true;
	}
}

?>
<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-10 18:08:43
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-08-10 18:33:19
 */
class Curl {

	private $request_url;

	private $operation;

	private $cookie_file;

	private $config_arr;

	public function __construct()
	{
		$this->cookie_file = 'curl_cookie.txt';
		$this->config_arr  = require_once 'config.php';
	}

	public function request($method, $url, $fields = array(), $before_url = '')
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIE, $this->config_arr['user_cookie']);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if ($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		}
		$result = curl_exec($ch);
		return $result;
	}

	public function setOption()
	{

	}

	public function __destruct()
	{

	}

}
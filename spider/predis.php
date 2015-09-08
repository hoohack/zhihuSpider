<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-09-07 19:12:04
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-09-08 17:36:07
 */
class PRedis {
	
	public static function getInstance()
	{
		static $instances = array();
		$key = getmypid();
		if (empty($instances[$key]))
		{
			$instances[$key] = new Redis();

			$instances[$key]->connect('127.0.0.1', '6379');
		}
		return $instances[$key];
	}
}

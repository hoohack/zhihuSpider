<?php
/**
 * 错误信息托管器
 * 用于收集管理程序运行中的信息
*/
class Log
{
	/**
	 * 最近一次错误编码
	 *
	 * @var int
	 */
	public static $code = 0;

	/**
	 * 最近一次错误消息
	 *
	 * @var string
	 */
	public static $msg = '';

	/**
	 * 记录日志的级别设置
	 *
	 * @var int		0=>tmp,1=>debug,2=>info,3=>warn,4=>err
	 */
	public static $writeLogLevel = 1; //默认记录debug级别和以上的记录

	//是否直接输出
	private static $direct_output = false;

	/**
	 * 错误集队列
	 *
	 * @var string
	 */
	private static $_queue = array();

	private static $_cache_enable = true;

	/**
	 * 错误级别对应表
	 *
	 * @var array
	 */
	private static $_levelMap = array(
		0 => 'tmp',
		1 => 'debug',
		2 => 'info',
		3 => 'warn',
		4 => 'err',
		5 => 'phperr',
	);
	private static $_log_filename = null;

	/**
	 * 错误级别对应表
	 *
	 * @var array
	 */
	private static $logPath = null;
	
	/**
	 * [disableCache 取消cache]
	 * @return [type] [description]
	 */
	public static function disableCache()
	{
		self::$_cache_enable = false;		
	}

	/**
	 * [setDirectOutput 设置直接输出]
	 */
	public static function setDirectOutput()
	{
		self::$direct_output = true;
	}

	/**
	 * 追加临时级别记录
	 * 这里的消息只用于传送，不记录到文件
	 *
	 * @param string $msg
	 *
	 * @return void
	 */
	public static function tmp($msg)
	{
		self::$msg = $msg;
		self::$_queue[] = array($msg);

		//write
		if (self::$_cache_enable == false)
		{
			self::flush();
		}
	}

	/**
	 * 追加调试级别记录，调试信息
	 *
	 * @param string $msg
	 * @param string $loginName 记录错误的文件名称
	 * @param int $backtraceLevel 错误跟踪的层级，默认为0，小于0时不跟踪
	 *
	 * @return void
	 */
	public static function debug($msg, $logName = '', $backtraceLevel = 0)
	{
		//获取所在文件和行号
		$file = '';
		$line = 0;
		if ($backtraceLevel >= 0)
		{
			$debug_info = debug_backtrace();
			$file = $debug_info[$backtraceLevel]['file'];
			$line = $debug_info[$backtraceLevel]['line'];
		}

		self::$_queue[] = array($msg, $file, $line, 1, date('Y-m-d H:i:s'), $logName);		
		self::$msg = $msg;

		//write
		if (self::$_cache_enable == false)
		{
			self::flush();
		}
	}


	/**
	 * 追加普通级别记录
	 *
	 * @param string $msg
	 * @param string $loginName 记录错误的文件名称
	 * @param int $backtraceLevel 错误跟踪的层级，默认为0，小于0时不跟踪
	 *
	 * @return void
	 */
	public static function unshift($msg, $logName = '', $backtraceLevel = 0)
	{
		//获取所在文件和行号
		$file = '';
		$line = 0;
		if ($backtraceLevel >= 0)
		{
			$debug_info = debug_backtrace();
			$file = $debug_info[$backtraceLevel]['file'];
			$line = $debug_info[$backtraceLevel]['line'];
		}

		array_unshift(self::$_queue, array($msg, $file, $line, 2, date('Y-m-d H:i:s'), $logName));		
		self::$msg = $msg;

		//write
		if (self::$_cache_enable == false)
		{
			self::flush();
		}
	}

	/**
	 * 追加普通级别记录，info信息
	 *
	 * @param string $msg
	 * @param string $loginName 记录错误的文件名称
	 * @param int $backtraceLevel 错误跟踪的层级，默认为0，小于0时不跟踪
	 *
	 * @return void
	 */
	public static function info($msg, $logName = '', $backtraceLevel = 0)
	{
		//获取所在文件和行号
		$file = '';
		$line = 0;
		if ($backtraceLevel >= 0)
		{
			$debug_info = debug_backtrace();
			$file = $debug_info[$backtraceLevel]['file'];
			$line = $debug_info[$backtraceLevel]['line'];
		}

		self::$_queue[] = array($msg, $file, $line, 2, date('Y-m-d H:i:s'), $logName);		
		self::$msg = $msg;

		//write
		if (self::$_cache_enable == false)
		{
			self::flush();
		}
	}

	/**
	 * 追加警告级别记录，警告
	 *
	 *
	 * @param string $msg
	 * @param string $loginName 记录错误的文件名称
	 * @param int $backtraceLevel 错误跟踪的层级，默认为0，小于0时不跟踪
	 *
	 * @return void
	 */
	public static function warn($msg, $logName = '', $backtraceLevel = 0)
	{
		//获取所在文件和行号
		$file = '';
		$line = 0;
		if ($backtraceLevel >= 0)
		{
			$debug_info = debug_backtrace();
			$file = $debug_info[$backtraceLevel]['file'];
			$line = $debug_info[$backtraceLevel]['line'];
		}

		self::$_queue[] = array($msg, $file, $line, 3, date('Y-m-d H:i:s'), $logName);		
		self::$msg = $msg;
		
		//write
		if (self::$_cache_enable == false)
		{
			self::flush();
		}
	}

	/**
	 * 追加错误级别记录，php错误
	 *
	 *
	 * @param string $msg
	 * @param string $loginName 记录错误的文件名称
	 * @param int $backtraceLevel 错误跟踪的层级，默认为0，小于0时不跟踪
	 * @param int $uin 当前的qq号码 主要可以用于染色
	 * @return void
	 */
	public static function phperr($errno, $errstr, $file, $line)
	{
		switch ($errno) {
			case E_USER_ERROR:
				$msg = "PHP ERROR $errstr";        
				break;

			case E_USER_WARNING:
			case E_WARNING:
				$msg = "PHP WARNING $errstr";   
				break;

			case E_USER_NOTICE:
			case E_NOTICE:
				$msg = "PHP NOTICE $errstr";
				break;

			default:
				$msg = "PHP Unknown error type: [$errno] $errstr<br />\n";
				break;
		}

		self::$_queue[] = array($msg, $file, $line, 5, date('Y-m-d H:i:s'));
		self::$msg = $msg;

		//write
		if (self::$_cache_enable == false)
		{
			self::flush();
		}
		return true;
	}

	/**
	 * 追加错误级别记录，错误
	 *
	 *
	 * @param string $msg
	 * @param string $loginName 记录错误的文件名称
	 * @param int $backtraceLevel 错误跟踪的层级，默认为0，小于0时不跟踪
	 * @param int $uin 当前的qq号码 主要可以用于染色
	 * @return void
	 */
	public static function err($msg, $logName = '', $backtraceLevel = 0,$uin=0)
	{
		//染色逻辑start
		$color_trace = false;//染色的号码集
		if( (!empty($uin)) && self::isColorUser($uin))
		{
			$color_trace = true;
		}
		//染色逻辑end
		

		//获取所在文件和行号
		$file = '';
		$line = 0;
		if ($backtraceLevel >= 0)
		{
			$debug_info = debug_backtrace();
			$file = $debug_info[$backtraceLevel]['file'];
			$line = $debug_info[$backtraceLevel]['line'];
		}

		self::$_queue[] = array($msg, $file, $line, 4, date('Y-m-d H:i:s'), $logName);
		self::$msg = $msg;

		//write
		if (self::$_cache_enable == false)
		{
			self::flush();
		}
	}

	/**
	 * 获取最后一条消息，此条消息将仍被自动写入日志
	 * @return array array(code, msg)
	 */
	public static function last()
	{
		$item = end(self::$_queue);
		if ($item)
		{
			return array('code'=>$item[0], 'msg'=>$item[1]);
		}

		return array('code'=>0, 'msg'=>'');
	}

	/**
	 * 弹出最后一条消息，此条消息将不再被自动写入日志
	 * @return array array(code, msg)
	 */
	public static function pop()
	{
		$item = array_pop(self::$_queue);
		if ($item)
		{
			return array('msg'=>$item[1]);
		}

		return array('msg'=>'');
	}

	/**
	 * 清空当前记录集
	 *
	 * @return void
	 */
	public static function clear()
	{
		self::$_queue = array();
	}

	/**
	 * 将错误记录集写入日志并清空当前记录集
	 *
	 * @return void
	 */
	public static function flush($ret_as_str = false)
	{
		if (empty(self::$_queue))
		{
			return ;
		}
		$default_log_name = "default";
		$tmp = array();
		$tmp_date = '';
		foreach (self::$_queue as $item)
		{
			//级别低于需要写日志的级别时忽略
			if (empty($item[3]) || $item[3] < self::$writeLogLevel)
			{
				continue;
			}

			//取日期用于保存到该日期目录
			$date = substr($item[4], 0, 10);
			if ($tmp_date == '')
			{
				$tmp_date = $date;
			}

			//这里保证日志不放到错误的日期目录中
			if ($tmp_date != $date)
			{
				foreach ($tmp as $k => $v) {
					self::writeFile($k, $v);
				}
				$tmp = array();
				$tmp_date = $date;
			}

			$log_name = !empty($item[5]) ? $item[5] : (!empty(self::$_log_filename) ? self::$_log_filename : $default_log_name);
			$log_name = self::$logPath . "/" .  str_replace('-', '', $date) . '/'.  $log_name . ".log";
			if (!isset($tmp[$log_name]))
			{
				$tmp[$log_name] = '';
			}
			
			$level = self::$_levelMap[$item[3]];
			$item[0] = str_replace(array("\r", "\n"), "", $item[0]);
			$tmp[$log_name] .= "{$item[4]}\t{$level}\t{$item[1]}:{$item[2]}\t{$item[0]}\n";
		}

		//按需写入不同文件
		if(empty($ret_as_str))
		{
			foreach ($tmp as $k => $v)
			{
				self::writeFile($k, $v);
			}
			self::$_queue = array();
			return true;
		}
		else
		{
			foreach ($tmp as $k => $v)
			{
				$tmp[$k] = explode("\n", $v);
			}
			return $tmp;
		}
	}

	/**
	 * 设置写日志文件目录
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public static function setLogPath($path)
	{
		self::$logPath = $path;
	}

	/**
	 * 写入文件
	 *
	 * @param string $logName 名称
	 * @param string $date 日期，用以生成该日期的目录
	 * @param string $content 内容
	 *
	 * @return void
	 */
	private static function writeFile($logName, $content)
	{		
		if(self::$direct_output === true)
		{
			echo $logName . "\t" . $content . "\n";
			return ;
		}
		if (empty(self::$logPath))
		{
			trigger_error('Log path not be setted.');
			return ;
		}

		umask(0);
		$dir = dirname($logName);
		if (file_exists($dir))
		{
			if (!is_writeable($dir) && !chmod($dir, 0777))
			{		
				trigger_error('Log dir (' . self::$logPath . ') unwriteable.');
				return ;
			}
		}
		else if(mkdir($dir, 0777, true) === false)
		{
			trigger_error('mkdir dir ' . $dir . ' false');
			return ;
		}

		$file_path = $logName;
		if (file_put_contents($file_path, $content, FILE_APPEND) === false )
		{
			trigger_error('cannot write log to file:' . $dir . '/' . $logName);
		}

		if (filesize($file_path)  > 209715200) //200M
		{
			rename($file_path, $file_path . '.' . date('His'));
		}
	}
	/*
	*在server中可能会需要一个进程（PSF）中的所有未指明的log汇集到一个文件中
	*@params string $filename 文件log名字, 为空时清除掉这个设置
	*/
	public static function setLogFile($filename)
	{
		self::$_log_filename = $filename;
	}
	/*
	* 当前输入用户或者当前登录用户是否为染色用户 如果是则上报所有错误log到监控端.也可在此处加入染色逻辑
	*/
	public static function isColorUser($uin=0)
	{
		if(in_array($uin,$color_uins))
		{
			//在染色用户群中
			return true;
		}
		/*
			加其他染色逻辑
		*/
		//不在染色群体中
		return false;
	}
}

register_shutdown_function(array('Log', 'flush'));
//end of script

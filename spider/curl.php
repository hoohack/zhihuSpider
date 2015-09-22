<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-10 18:08:43
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-09-17 11:39:05
 */
require_once './function.php';
class Curl {

	private static $user_cookie = '_za=9940ad75-d123-421d-bba5-4e247da577a0;q_cl=e6713b82d4284d16a5c8373af3e76e65|1442399751000|1442399751000;_xsrf=fcecded8440d03b53b3935b566ec741b;cap_id="ZTA3ZjNlOTk3Y2ZjNDJjNmIwM2E2N2IzYTIxYTBjMTE=|1442399751|8447d695435144053a49c4069eb49f3e0dccbf20";z_c0="QUFEQTRZbzZBQUFYQUFBQVlRSlZUUnJUSUZZX1UyNUpkampQRElzSENQSzlhQ2s5UDNyOUF3PT0=|1442399770|ed42f54477ac6a770f4ceb7bb4da8e8afdcd11d6";__utma=51854390.1336096764.1442399738.1442399738.1442400123.2;__utmb=51854390.4.10.1442399738;__utmc=51854390;__utmt=1;__utmv=51854390.100-1|2=registration_date=20141017=1^3=entry_date=20141017=1;__utmz=51854390.1442399738.1.1.utmcsr=zhihu.com|utmccn=(referral)|utmcmd=referral|utmcct=/people/disinfeqt;unlock_ticket="QUFEQTRZbzZBQUFYQUFBQVlRSlZUU0pOLVZYMnlmSGFwVlpjQWNZUHpCc0xXNXljajBkdnpRPT0=|1442399770|46e23553f528de4b2b97c31cf5f5cdd9ddf3a77d"';

	/**
	 * [request 执行一次curl请求]
	 * @param  [string] $method     [请求方法]
	 * @param  [string] $url        [请求的URL]
	 * @param  array  $fields     [执行POST请求时的数据]
	 * @return [stirng]             [请求结果]
	 */
	public static function request($method, $url, $fields = array())
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIE, self::$user_cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		if ($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		}
		$result = curl_exec($ch);
		return $result;
	}

	/**
	 * [getMultiUser 多进程获取用户数据]
	 * @param  [type] $user_list [description]
	 * @return [type]            [description]
	 */
	public static function getMultiUser($user_list)
	{
		$ch_arr = array();
		$text = array();
		$len = count($user_list);
		$max_size = ($len > 5) ? 5 : $len;
		$requestMap = array();

		$mh = curl_multi_init();
		for ($i = 0; $i < $max_size; $i++)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, 'http://www.zhihu.com/people/' . $user_list[$i] . '/about');
			curl_setopt($ch, CURLOPT_COOKIE, self::$user_cookie);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$requestMap[$i] = $ch;
			curl_multi_add_handle($mh, $ch);
		}

		$user_arr = array();
		do {
			while (($cme = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM);
			
			if ($cme != CURLM_OK) {break;}

			while ($done = curl_multi_info_read($mh))
			{
				$info = curl_getinfo($done['handle']);
				$tmp_result = curl_multi_getcontent($done['handle']);
				$error = curl_error($done['handle']);

				$user_arr[] = array_values(getUserInfo($tmp_result));

				//保证同时有$max_size个请求在处理
				if ($i < sizeof($user_list) && isset($user_list[$i]) && $i < count($user_list))
                {
                	$ch = curl_init();
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_URL, 'http://www.zhihu.com/people/' . $user_list[$i] . '/about');
					curl_setopt($ch, CURLOPT_COOKIE, self::$user_cookie);
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					$requestMap[$i] = $ch;
					curl_multi_add_handle($mh, $ch);

                    $i++;
                }

                curl_multi_remove_handle($mh, $done['handle']);
			}

			if ($active)
                curl_multi_select($mh, 10);
		} while ($active);

		curl_multi_close($mh);
		return $user_arr;
	}

}
<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-10 18:08:43
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2016-04-19 18:12:58
 */
require_once './function.php';
class Curl {

	private static $user_cookie = '_zap=06c40d9b-e783-45c3-875b-b5def3690777;d_c0="AGAAuXZTsAmPTiftYaWT02M1JeAkw0ewo9w=|1459231862";l_cap_id="NjE4YmY5ZGQ3NjU2NDhjNTlhMjgzOTU1OGU0MGQ4MGU=|1459325472|e1d55d466d4c3dc9d05f87cf3560f8181536a634";_za=a2889ef9-c598-4e96-8ab5-9ca0a9f42e7e;q_cl=21fd5f4d6c3541aa873163af7517ab8d|1459231862000|1459231862000;_xsrf=2954cb4a20c90fd5d8827725902192ae;cap_id="ZTgxZjk5MTJkNDc4NGYwY2E4OWRiYzllZGUxMDcxMDE=|1459325472|44db7054701fc0b9653cfa63d7692305dd1605e2";z_c0="QUFEQTRZbzZBQUFYQUFBQVlRSlZUVElYSTFjVmkxZGpUR19IWkJJZFBpQk5jZkgybU13bGRnPT0=|1459325490|82fb5c6295ee4f3802dd9cba337f2660743f5422";__utma=51854390.1528321902.1460969239.1461053456.1461059182.4;__utmb=51854390.2.10.1461059182;__utmc=51854390;__utmt=1;__utmv=51854390.100-1|2=registration_date=20141017=1^3=entry_date=20141017=1;__utmz=51854390.1460969239.1.1.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided);login="Y2ExZTc4OTYwMDkxNDI5MWFiNmY3NTlkNmI1MDhmNTk=|1459325490|2956a0f521ea9e6337fcb110e266f9d1a558492b";';

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
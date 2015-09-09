<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-26 11:38:18
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-09-09 18:17:54
 */
require_once './spider/user.php';
require_once './function.php';
require_once './spider/curl.php';
require_once './spider/pdo_mysql.php';
require_once './spider/predis.php';
$user_cookie = '_za=a41e1b8b-517a-4fea-9465-88e8c80ba17e;q_cl=3198dbc291fa40d7b717f9a4dd5ec90e|1439792872000|1439792872000;_xsrf=981ffd949fbc70e73cc4bb2559243ac8;cap_id="YmViMDk0YTdjMjUyNDc4MjhmOWU5MDkyMTg3NWRlNGY=|1439792872|7eb10c44aead609ab6e63f3eb2b5856149076942";z_c0="QUFEQTRZbzZBQUFYQUFBQVlRSlZUZjhMLVZYNnBhUDBYYzJIOFJtUGs2aFlianFRU3NRR3hRPT0=|1439792895|4f033f6e2f99a39b152a59c32496dfc954cbe6fd";__utma=51854390.888606616.1439792875.1439792875.1439891906.2;__utmb=51854390.2.10.1439891906;__utmc=51854390;__utmt=1;__utmv=51854390.100-1|2=registration_date=20141017=1^3=entry_date=20141017=1__utmz=51854390.1439891906.2.2.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided)';

//redis instance
$redis = PRedis::getInstance();
$redis->flushdb();
$redis->lpush('request_queue', 'mora-hu');

//进程数
$max_connect = 8;
while (1)
{
	echo "begin get user info...\n";
	$total = $redis->llen('request_queue');
	if ($total == 0)
	{
		echo "-----done...-----\n";
		break;
	}
	else if ($total <= $max_connect)
	{
		$current_count = $total;
	}
	else
	{
		$current_count = $max_connect;
	}
	for ($i = 1; $i <= $current_count; ++$i)
	{
		$pid = pcntl_fork();
		if ($pid == -1)
		{
			echo "-----fork child process failed...-----\n";
			exit(0);
		}
		if (!$pid)
		{
			$tmp_redis = PRedis::getInstance();
			$tmp_u_id = $tmp_redis->lpop('request_queue');
			echo "getting {$tmp_u_id} follower list\n";
			$result = Curl::request('GET', 'http://www.zhihu.com/people/' . $tmp_u_id . '/followees');
			
			//如果获取失败，继续循环请求5次，如果还是失败则放弃
			if (empty($result))
			{
				$i = 0;
				while(empty($result))
				{
					$result = Curl::request('GET', 'http://www.zhihu.com/people/' . $tmp_u_id . '/followees');
					if (++$i == 5)
					{
						exit($i);
					}
				}
			}
			$params = array(
				'where' => array(
					'u_id' => $tmp_u_id
				)
			);
			if (!User::existed($params, 'user'))
			{
				$current_user = getUserInfo($result);
				User::add($current_user);
			}

			$user_info = User::info($tmp_u_id);
			$user_followees_count = User::getFollowCount($tmp_u_id);

			if ($user_info['followees_count'] != $user_followees_count)
			{
				$followee_users = getUserList($result, $tmp_u_id, 'followees', $user_info['followees_count']);
				foreach ($followee_users as $user)
				{
					$tmp_redis->lpush('request_queue', $user[2]);
				}
				User::addFollowList($followee_users);
			}
			echo "add new " . $user_info['followees_count'] . " users\n";
			$tmp_redis->close();
			exit($i);
		}
		usleep(1);
	}
	while (pcntl_waitpid(0, $status) != -1)
	{
		$status = pcntl_wexitstatus($status);
		if (pcntl_wifexited($status))
		{
			echo "yes";
		}
		echo "$status finished\n";
	}
}
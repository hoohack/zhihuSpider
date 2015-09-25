<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-09-17 11:16:05
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-09-17 11:19:24
 */
//获取用户信息脚本

require_once './spider/user.php';
require_once './function.php';
require_once './spider/curl.php';
require_once './spider/pdo_mysql.php';
require_once './spider/predis.php';
require_once './spider/log.php';
$user_cookie = '_za=9940ad75-d123-421d-bba5-4e247da577a0;q_cl=e6713b82d4284d16a5c8373af3e76e65|1442399751000|1442399751000;_xsrf=fcecded8440d03b53b3935b566ec741b;cap_id="ZTA3ZjNlOTk3Y2ZjNDJjNmIwM2E2N2IzYTIxYTBjMTE=|1442399751|8447d695435144053a49c4069eb49f3e0dccbf20";z_c0="QUFEQTRZbzZBQUFYQUFBQVlRSlZUUnJUSUZZX1UyNUpkampQRElzSENQSzlhQ2s5UDNyOUF3PT0=|1442399770|ed42f54477ac6a770f4ceb7bb4da8e8afdcd11d6";__utma=51854390.1336096764.1442399738.1442399738.1442400123.2;__utmb=51854390.4.10.1442399738;__utmc=51854390;__utmt=1;__utmv=51854390.100-1|2=registration_date=20141017=1^3=entry_date=20141017=1;__utmz=51854390.1442399738.1.1.utmcsr=zhihu.com|utmccn=(referral)|utmcmd=referral|utmcct=/people/disinfeqt;unlock_ticket="QUFEQTRZbzZBQUFYQUFBQVlRSlZUU0pOLVZYMnlmSGFwVlpjQWNZUHpCc0xXNXljajBkdnpRPT0=|1442399770|46e23553f528de4b2b97c31cf5f5cdd9ddf3a77d"';

//redis instance
$redis = PRedis::getInstance();
// $redis->flushdb();
if ($redis->llen('request_queue') == 0)
{
	$redis->lpush('request_queue', 'mora-hu');
}
//最大进程数
$max_connect = 2;

//设置log文件目录
Log::setLogPath('./log');

while (1)
{
	echo "--------begin get user info--------\n";
	$total = $redis->llen('request_queue');
	if ($total == 0)
	{
		echo "--------done--------\n";
		break;
	}
	$current_count = ($total <= $max_connect) ? $total : $max_connect;

	for ($i = 1; $i <= $current_count; ++$i)
	{
		$pid = pcntl_fork();
		if ($pid == -1)
		{
			echo "--------fork child process failed--------\n";
			exit(0);
		}
		if (!$pid)
		{
			$startTime = microtime();
			$tmp_redis = PRedis::getInstance();
			$tmp_u_id = $tmp_redis->lpop('request_queue');
			if (empty($tmp_redis->zscore('already_get_queue', $tmp_u_id)))
			{
				saveUserInfo($tmp_u_id);
				$user_info = User::info($tmp_u_id);

				$user_followees_count = $tmp_redis->hget($tmp_u_id, 'followees_count');
				$user_followers_count = $tmp_redis->hget($tmp_u_id, 'followers_count');
				
				if ($user_info['followees_count'] != $user_followees_count)
				{
					updateUserInfo($tmp_u_id);
					echo "--------start getting {$tmp_u_id}'s " . $user_info['followees_count'] . " followees user list--------\n";
					$followee_users = getUserList($tmp_u_id, 'followees', $user_info['followees_count'], 1);

					$tmp_redis->set($tmp_u_id, 'followees_count', count($followee_users));

					if (!empty($followee_users))
					{
						foreach ($followee_users as $user)
						{
							$tmp_redis->lpush('request_queue', $user[3]);
						}
					}
					Log::info('empty followee_users u_id' . $tmp_u_id);

					echo "--------get " . count($followee_users) . " followees users done--------\n";
				}

				if ($user_info['followers_count'] != $user_followers_count)
				{
					updateUserInfo($tmp_u_id);
					echo "--------start getting {$tmp_u_id}'s " . $user_info['followers_count'] . " followers user list--------\n";
					$follower_users = getUserList($tmp_u_id, 'followers', $user_info['followers_count'], 1);
					$tmp_redis->set($tmp_u_id, 'follower_users', count($follower_users));
					if (!empty($follower_users))
					{
						foreach ($follower_users as $user)
						{
							$tmp_redis->lpush('request_queue', $user[1]);
						}
					}
					Log::info('empty follower_users u_id' . $tmp_u_id);

					echo "--------get " . count($follower_users) . " followers users done--------\n";
				}

				$tmp_redis->zadd('already_get_queue', 1, $tmp_u_id);
				$tmp_redis->close();
				$endTime = microtime();
				$startTime = explode(' ', $startTime);
		        $endTime = explode(' ', $endTime);
		        $total_time = $endTime[0] - $startTime[0] + $endTime[1] - $startTime[1];
		        $timecost = sprintf("%.2f",$total_time);
		        echo "--------const  " . $timecost . " second on $tmp_u_id--------\n";
			}
			else
			{
				echo "--------user $tmp_u_id info and followee and follower already get--------\n";
			}
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
		echo "--------$status finished--------\n";
	}
}
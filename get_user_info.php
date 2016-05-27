<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-09-17 11:16:05
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2016-05-27 18:42:56
 */
//获取用户信息脚本

require_once './spider/user.php';
require_once './function.php';
require_once './spider/curl.php';
require_once './spider/pdo_mysql.php';
require_once './spider/predis.php';
require_once './spider/log.php';
//redis instance
$redis = PRedis::getInstance();
$redis->flushdb();
if ($redis->llen('request_queue') == 0)
{
	$redis->lpush('request_queue', 'hector-hu');
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
			$tmp_size = $tmp_redis->zscore('already_get_queue', $tmp_u_id);
			if (empty($tmp_size))
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
<?php
	ini_set('default_socket_timeout', -1);
	require 'predis.php';
	$redis = PRedis::getInstance();
	$redis->flushdb();
	$redis->lpush('user_queue', 'mora-hu', 1, 2, 3, 4, 5, 6);
	$redis->lpush('mora-hu', 1, 2);
	$redis->lpush('1', 3, 4);
	$redis->lpush('2', 5, 6);
	$redis->lpush('3', 7, 8);
	$redis->lpush('4', 9);
	$redis->lpush('5', 10, 11);
	$redis->lpush('6', 12, 13);
	$max = 7;
	while(1) {
		$total = $redis->llen('user_queue');
		if ($total == 0) {echo "done...\n";break;}
		else if ($total <= $max){$this_count = $total;}
		else {$this_count = $max;}
		for ($i = 1; $i <= $this_count; $i++)
		{	
			$pid = pcntl_fork();	
			if ($pid == -1)
			{
				echo "Could not fork!\n";
				exit(0);		
			}
			if (!$pid)
			{
				$tmp_redis = PRedis::getInstance();
				$user = $tmp_redis->lpop('user_queue');
				echo "getting {$user} follower list...\n";
				$tmp_end = $tmp_redis->llen("$user")-1;
				$follow_list = $tmp_redis->lrange("$user", 0, $tmp_end);
				foreach ($follow_list as $follow) {
					$tmp_redis->lpush('follow_list', $follow);	
				}
				$tmp_redis->close();
				exit($i);
			}
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
	$f_redis = PRedis::getInstance();
	while($f_redis->llen('follow_list') != 0)
	{
		echo $f_redis->lpop('follow_list') . "\n";
	}


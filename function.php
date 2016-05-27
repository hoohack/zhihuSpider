<?php
/**
 * @Author: hector
 * @Date:   2015-08-22 10:19:54
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2016-05-27 18:43:03
 */
/**
 * [getUserInfo 获取用户]
 * @param  [type] $result [description]
 * @return [type]         [description]
 */
function getUserInfo($result)
{
	$user = array();

	preg_match_all('#<a class="name" href="/people\/(.*)">(.*)</a>#', $result, $out);
	$user['u_id'] = empty($out[1]) ? '' : $out[1][0];
	$user['u_name'] = empty($out[2]) ? '' : $out[2][0];

	preg_match('#<span class="location item" title=["|\'](.*?)["|\']>#', $result, $out);
	$user['address'] = empty($out[1]) ? '' : $out[1];

	preg_match('#<img class="Avatar Avatar--l" src="(.*?)" srcset=".*?" alt=".*?" />#', $result, $out);
	$img_url_tmp = empty($out[1]) ? '' : $out[1];
	// $user['img_url'] = getImg($img_url_tmp, $user['u_id']);
	$user['img_url'] = $img_url_tmp;

	preg_match('#<span class="business item" title=["|\'](.*?)["|\']>#', $result, $out);
	$user['business'] = empty($out[1]) ? '' : $out[1];

	preg_match('#<i class="icon icon-profile-(.*?)male"></i>#', $result, $out);
	$user['gender'] = empty($out[1]) ? 'male' : 'female';

	preg_match('#<span class="education item" title=["|\'](.*?)["|\']>#', $result, $out);
	$user['education'] = empty($out[1]) ? '' : $out[1];

	preg_match('#<span class="education-extra item" title=["|\'](.*?)["|\']>#', $result, $out);
	$user['major'] = empty($out[1]) ? '' : $out[1];

	preg_match('#<span class="content">\s(.*?)\s</span>#s', $result, $out);
	$user['description'] = empty($out[1]) ? '' : trim(strip_tags($out[1]));

	preg_match('#<span class="zg-gray-normal">关注了</span><br>\s<strong>(.*?)</strong><label> 人</label>#', $result, $out);
	$user['followees_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#<span class="zg-gray-normal">关注者</span><br>\s<strong>(.*?)</strong><label> 人</label>#', $result, $out);
	$user['followers_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#<strong>(.*?) 个专栏</strong>#', $result, $out);
	$user['special_count'] = empty($out[1]) ? 0 : intval($out[1]);

	preg_match('#<strong>(.*?) 个话题</strong>#', $result, $out);
	$user['follow_topic_count'] = empty($out[1]) ? 0 : intval($out[1]);

	preg_match('#<span class="zm-profile-header-user-agree"><span class="zm-profile-header-icon"></span><strong>(.*?)</strong>赞同</span>#', $result, $out);
	$user['approval_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#<span class="zm-profile-header-user-thanks"><span class="zm-profile-header-icon"></span><strong>(.*?)</strong>感谢</span>#', $result, $out);
	$user['thank_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#提问\s<span class="num">(.*?)</span>#', $result, $out);
	$user['ask_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#回答\s<span class="num">(.*?)</span>#', $result, $out);
	$user['answer_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#文章\s<span class="num">(.*?)</span>#', $result, $out);
	$user['article_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#个人主页被 <strong>(.*?)</strong> 人浏览#', $result, $out);
	$user['pv_count'] = empty($out[1]) ? 0 : intval($out[1]);

	preg_match('#收藏\s<span class="num">(.*?)</span>#', $result, $out);
	$user['started_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#公共编辑\s<span class="num">(.*?)</span>#', $result, $out);
	$user['public_edit_count'] = empty($out[1]) ? 0 : $out[1];

	$user['duplicate_count'] = 1;
	return $user;
}

/**
 * [getImg 处理防盗链图片]
 * @param  [type] $url  [description]
 * @param  [type] $u_id [description]
 * @return [type]       [description]
 */
function getImg($url, $u_id)
{
	if (file_exists('./images/' . $u_id . ".jpg"))
	{
		return "images/$u_id" . '.jpg';
	}
	if (empty($url))
	{
		return '';
	}
    $context_options = array(  
		'http' =>  
		array(
			'header' => "Referer:https://www.zhihu.com",  
	));
	  
	$context = stream_context_create($context_options);  
	$img = file_get_contents('http:' . $url, FALSE, $context);
	file_put_contents('./images/' . $u_id . ".jpg", $img);
	return "images/$u_id" . '.jpg';
}

/**
 * [dealUserInfo 返回用户名和昵称列表]
 * @param  [type] $user_list [description]
 * @return [type]            [description]
 */
function dealUserInfo($user_list, $u_id, $user_type = 'followees', $u_name)
{
	$info_list = array();
	$new_user_id_list = array();
	$new_user_list = array();
	if (empty($user_list))
	{
		return array();
	}
	foreach ($user_list as $user)
	{
		preg_match('#<h2 class="zm-list-content-title"><a data-tip=".*?" href="https://www.zhihu.com/people/(.*?)" class="zg-link" title="(.*?)">#', $user, $out);
		$params = array(
			'where' => array(
				'u_id' => $out[1]
			)
		);
		if (!User::existed($params, 'user'))
		{
			$new_user_id_list[] = $out[1];
		}
		if ($user_type == 'followees')
		{
			$info = array('', $u_id, $u_name, empty($out[1]) ? '' : $out[1], empty($out[2]) ? '' : $out[2]);
		}
		else
		{
			$info = array('', empty($out[1]) ? '' : $out[1], $u_id, empty($out[2]) ? '' : $out[2], $u_name);	
		}
		array_push($info_list, $info);
	}
	if (!empty($new_user_id_list))
	{
		$new_user_list = Curl::getMultiUser($new_user_id_list);
	}
	$result = array($new_user_list, $info_list);
	usleep(1);
	return $result;
}

/**
 * [getOnePageUserList 如果关注的用户只有一页的处理]
 * @param  [type] $result    [description]
 * @param  [type] $u_id      [description]
 * @param  string $user_type [description]
 * @param  [type] $count     [description]
 * @return [type]            [description]
 */
function getOnePageUserList($result, $u_id, $user_type = 'followees', $count, $u_name, $op_type)
{
	$follow_user_list = array();
	$user_list = array();
	preg_match_all('#<h2 class="zm-list-content-title"><a data-tip=".*?" href="https://www.zhihu.com/people/(.*?)" class="zg-link" title="(.*?)">#', $result, $out);

	$user_list = Curl::getMultiUser($out[1]);
	for ($i = 0; $i < $count; $i++)
	{
		if ($user_type == 'followees')
		{
			$user = array('', $u_id, $u_name, empty($out[1][$i]) ? '' : $out[1][$i], empty($out[2][$i]) ? '' : $out[2][$i]);
		}
		else
		{
			$user = array('', empty($out[1][$i]) ? '' : $out[1][$i], empty($out[2][$i]) ? '' : $out[2][$i],  $u_id, $u_name);
		}
		array_push($follow_user_list, $user);
	}
	User::addMulti($user_list);
	if (!empty($follow_user_list) && ($op_type == 2))
	{
		echo "--------adding " . count($follow_user_list) . " {$u_id}'s $user_type user--------\n";
		User::addFollowList($follow_user_list);
		echo "--------adding " . count($follow_user_list) . " {$u_id}'s $user_type user done--------\n";
	}
	return $follow_user_list;
}

/**
 * [getUserList 返回用户列表]
 * @param  [type]  $result    [description]
 * @param  [type]  $u_id  	  [description]
 * @param  string  $user_type [description]
 * @param  integer $count     [description]
 * @return [type]             [description]
 */
function getUserList($u_id, $user_type = 'followees', $count, $op_type)
{
	$following_users = array();
	$more_user_list = array();
	$tmp_following_users = array();
	$result = Curl::request('GET', 'https://www.zhihu.com/people/' . $u_id . '/' . $user_type);
	preg_match('#<a class="name" href="/people/(.*?)">(.*?)</a>#', $result, $u_out);
	$u_name = empty($u_out[2]) ? '' : $u_out[2];

	if ($count <= 20)
	{
		$following_users = getOnePageUserList($result, $u_id, $user_type, $count, $u_name, $op_type);
	}
	else
	{
		preg_match('#<input type="hidden" name="_xsrf" value="(.*?)"/>#', $result, $out);
    	$_xsrf = empty($out[1]) ? '' : trim($out[1]);
    	preg_match('#<div class="zh-general-list clearfix" data-init="(.*?)">#', $result, $out);
    	$url_params = empty($out[1]) ? '' : json_decode(html_entity_decode($out[1]), true);

    	echo "--------start requesting $u_id more $count user--------\n";
    	if (!empty($_xsrf) && !empty($url_params) && is_array($url_params))
    	{
			$params = $url_params['params'];
			$total_page = ceil($count/20);
			for ($page = 1; $page <= $total_page; ++$page)
			{
				$params['offset'] = ($page - 1 ) * 20;
				$post_fields = array(
					'method' => 'next',
					'params' =>  json_encode($params),
					'_xsrf' => $_xsrf
				);
				$more_user = Curl::request('POST', 'https://www.zhihu.com/node/' . $url_params['nodename'], $post_fields);
				$more_user_result = json_decode($more_user, true);
				if (empty($more_user_result['msg']) || !is_array($more_user_result['msg']))
				{
					echo "--------get $u_id $user_type page $page failed--------\n";
					continue;
				}
				$more_user_tmp_list = $more_user_result['msg'];
				$result = dealUserInfo($more_user_tmp_list, $u_id, $user_type, $u_name);
				if (empty($result))
				{
					echo "--------empty more user {$url_params['nodename']} with u_id  $u_id--------\n";
					continue;
				}
				$more_user_list = array_merge($more_user_list, $result[0]);
				$tmp_following_users = array_merge($tmp_following_users, $result[1]);
				//每获取到200条插入一次
				if ($page%10 == 0)
				{
					if (!empty($more_user_list))
					{
						$tmp_count = count($more_user_list);
						echo "--------start adding more new $tmp_count user with u_id  $u_id--------\n";
						User::addMulti($more_user_list);
						echo "--------add more new {$tmp_count} user done with u_id $u_id--------\n";
					}
					if (!empty($tmp_following_users) && ($op_type == 2))
					{
						echo "--------start adding " . count($tmp_following_users) . " $user_type user  with u_id $u_id--------\n";
						User::addFollowList($tmp_following_users);
						echo "--------add " . count($tmp_following_users) . " $user_type user done  with u_id $u_id--------\n";
					}
					$more_user_list = array();
					$tmp_following_users = array();
				}
				$following_users = array_merge($following_users, $result[1]);
			}
			if (!empty($more_user_list))
			{
				echo "--------start adding rest " . count($more_user_list) . " user with u_id $u_id--------\n";
				$last_id = User::addMulti($more_user_list);
				echo "--------add rest" . count($more_user_list) . " user done with u_id $u_id and last_id $last_id--------\n";
			}
			if (!empty($tmp_following_users) && ($op_type == 2))
			{
				echo "--------start adding rest " . count($tmp_following_users) . " {$u_id}'s $user_type user--------\n";
				User::addFollowList($tmp_following_users);
				echo "--------add " . count($tmp_following_users) . " {$u_id}'s $user_type user done--------\n";
			}
			echo "--------request more $count user done with u_id $u_id--------\n";
		}
		else
		{
			return array();
		}
	}

	return $following_users;
}


/**
 * [saveUserInfo 保存用户信息]
 * @param  [type]  $tmp_u_id    [用户ID]
 * @return [type]             [description]
 */
function saveUserInfo($tmp_u_id)
{
	$params = array(
		'where' => array(
			'u_id' => $tmp_u_id
		)
	);
	if (!User::existed($params, 'user'))
	{
		echo "--------found new user {$tmp_u_id}--------\n";
		echo "--------start getting {$tmp_u_id} info--------\n";
		$result = Curl::request('GET', 'https://www.zhihu.com/people/' . $tmp_u_id . '/followees');
		if (empty($result))
		{
			$i = 0;
			while(empty($result))
			{
				echo "--------empty result.try get $i time--------\n";
				$result = Curl::request('GET', 'https://www.zhihu.com/people/' . $tmp_u_id);
				if (++$i == 5)
				{
					exit($i);
				}
			}
		}
		$current_user = getUserInfo($result);
		User::add($current_user);
		echo "--------get {$tmp_u_id} info done--------\n";
	}
}

/**
 * [saveUserInfo 更新用户信息]
 * @param  [type]  $tmp_u_id    [用户ID]
 * @return [type]             [description]
 */
function updateUserInfo($tmp_u_id)
{
	echo "--------update user {$tmp_u_id}--------\n";
	echo "--------start updating {$tmp_u_id} info--------\n";
	$result = Curl::request('GET', 'https://www.zhihu.com/people/' . $tmp_u_id . '/followees');
	if (empty($result))
	{
		$i = 0;
		while(empty($result))
		{
			echo "--------empty result.try get $i time--------\n";
			$result = Curl::request('GET', 'https://www.zhihu.com/people/' . $tmp_u_id);
			if (++$i == 5)
			{
				exit($i);
			}
		}
	}
	$current_user = getUserInfo($result);
	unset($current_user['u_id']);
	User::update($current_user, $tmp_u_id);
	echo "--------update {$tmp_u_id} info done--------\n";
}
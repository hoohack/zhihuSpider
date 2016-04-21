<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-06-08 17:45:18
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2016-04-20 10:03:15
 */
class PDO_MySQL {
	private $pdo;

	//表名
	private static $table = null;
	//操作符
	private static $allow_operator = array(
		'=',
		'>',
		'>=',
		'<',
		'<=',
		'!=',
		'in',
		'not in',
		'like',
		'not like',
	);
	//join类型
	private static $allow_join_type = array(
		'join',
		'left join',
		'right join',
		'inner join',
	);
	//配置文件路径
	private static $config_file_path = 'db_config.php';

	public function __construct()
	{
		$config = require self::$config_file_path;
		$dsn = $config['mysql']['dsn'];
		$username = $config['mysql']['username'];
		$password = $config['mysql']['password'];
		$option = $config['mysql']['option'];
		try {
			$this->pdo = new PDO($dsn, $username, $password, $option);
			return $this->pdo;
		} catch(Exception $e) {
			echo 'catch connection exception, info : ' . $e->__toString();
			return false;
		}
	}

	//程序对外统一入口
	public static function getInstance()
	{
		$key = getmypid();
		$instances = array();
		//初始化连接
		if (empty($instances[$key]))
		{
			$instances[$key] = new self();
		}
		return $instances[$key];
	}

	/**
	* 返回一行数据
	* @param 	$table 			string 		表名
	* @param 	$conditions 	array 		检索条件
	* @param 	$fields 		string 		检索字段
	* @return  					array 		检索结果组成的一维数组
	*/
	public function getOneRow($table, $conditions = array(), $field = '*')
	{
		$params = array();
		$where = empty($conditions) ? '' : self::biuldMultiWhere($conditions, $params);
		$select_sql = implode(' ', array(
			'SELECT',
			$field,
			' FROM ',
			'zh_' . $table,
			$where,
			'LIMIT 1',
		));
		$stmt = $this->pdo->prepare($select_sql);
		$this->bind($params, $stmt);
		$result = $stmt->execute();
		if ($result === false)
		{
			var_dump('get one error ' . json_encode($select_sql . func_get_args()));
			return false;
		}
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	/**
	* 返回多行数据
	* @param 	$table 		string  	数据表名
	* @param 	$conditions array 		检索条件
	* @return   			array 		返回数据组成的数组
	*/
	public function getAll($table, $conditions)
	{
		$fields = empty($conditions['fields']) ? '*' : $conditions['fields'];
		$join = '';
		if (!empty($conditions['join']))
		{
			empty($conditions['join']['type']) && $conditions['join']['type'] = 'join';
			if (in_array(strtolower($conditions['join']['type']), self::$allow_join_type))
			{
				$join = $conditions['join']['type'] . " " . $conditions['join']['table'] . " on " . $conditions['join']['on'];
			}
		}
		$where = "";
		if (!empty($conditions['where']))
		{
			$tmp_where = self::buildWhere($conditions['where'], $params);
			$where = " where " . implode(' and ', $tmp_where);
		}
		$or_where = "";
		if (!empty($conditions['or_where']))
		{
			$tmp_or_where = self::buildWhere($conditions['or_where'], $params);
			$prefix = empty($where) ? " where " : " ";
			$or_where = $prefix . implode(' OR ', $tmp_or_where);
		}
		$group_by = "";
		if (!empty($conditions['group_by']))
		{
			$group_by = "GROUP BY " . $conditions['group_by'];
		}
		$having = "";
		if (!empty($conditions['having']))
		{
			$tmp_having = self::buildWhere($conditions['having'], $params);
			$having = " HAVING " . implode(' AND ', $tmp_having);
		}
		$sort = "";
		if (!empty($conditions['sort']))
		{
			foreach ($conditions['sort'] as $tmp_field => $sort_way)
			{
				$sort_way = ($sort_way == 1) ? " ASC " : " DESC ";
				$tmp_sort[] = $tmp_field . $sort_way;
			}
			$sort = " ORDER BY " . implode(',', $tmp_sort);
		}
		$limit = "";
		if (!empty($conditions['limit']))
		{
			$limit = " LIMIT " . intval($conditions['limit']);
		}
		$offset = "";
		if (!empty($conditions['offset']))
		{
			$offset = ' OFFSET ' . intval($conditions['offset']);
		}
		$select_sql = implode(" ", array(
			'SELECT',
			$fields,
			'FROM',
			'zh_' . $table,
			$join,
			$where,
			$or_where,
			$group_by,
			$having,
			$sort,
			$limit,
			$offset,
		));
		$stmt = $this->pdo->prepare($select_sql);
		$this->bind($params, $stmt);
		$result = $stmt->execute();
		if ($result === false)
		{
			var_dump('select error, args' . json_encode(func_get_args()));
			return false;
		}
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	* 根据条件返回数据的数量
	* @param 	$table 			string 	数据表名
	* @param 	$conditions 	array 	检索条件
	* @return 					int 	数量的结果
	*/
	public function count($table, $conditions = array())
	{
		$key = getmypid();
		$params = array();
		$where = empty($conditions) ? '' : self::biuldMultiWhere($conditions, $params);
		$count_sql = implode(" ", array(
			'SELECT COUNT(*) AS total_num FROM',
			'zh_' . $table,
			$where
		));
		// echo "count_sql" . $count_sql . "\n";
		$stmt = $this->pdo->prepare($count_sql);
		$this->bind($params, $stmt);
		$result = $stmt->execute();
		if($result === false)
		{
			var_dump("count error, args " . json_encode(func_get_args()));
			return false;
		}
		$count = $stmt->fetch(PDO::FETCH_ASSOC);
		$count = isset($count['total_num']) ? $count['total_num'] : 0;
		return intval($count);
	}
	/**
	* 插入一行数据到数据库中
	* @param 	$table 	string 	数据表名
	* @param 	$data 	array 	需要插入的数据
	* @return 			int 	插入新行的ID
	*/
	public function insert($table, $data)
	{
		$key = getmypid();
		$columns = array();
		$places = array();
		$params = array();
		foreach ($data as $tmp_field => $value)
		{
			$columns[] = "`" . $tmp_field . "`";
			$places[] = ":" . $tmp_field;
			$params[":" . $tmp_field] = $value;
		}
		$columns = '(' . implode(',', $columns). ')';
		$places = '(' . implode(',', $places) . ')';
		$insert_sql = implode(" ", array(
			'REPLACE INTO',
			'zh_' . $table,
			$columns,
			'VALUES',
			$places
		));
		$stmt = $this->pdo->prepare($insert_sql);
		$this->bind($params, $stmt);
		$result = $stmt->execute();
		if ($result !== true)
		{
			var_dump("Insert error, args" . json_encode(func_get_args()));
			return false;
		}
		return $this->pdo->lastInsertId();
	}
	/**
	* 插入多行数据到数据库中
	* @param 	$table 		string 	数据表名
	* @param 	$fields 	array 	插入的字段
	* @param 	$datas 		array 	插入的数据
	* @return 				int 	插入的最后一行的ID
	*/
	public function insertAll($table, $fields, $datas, $check_duplicate = 0)
	{
		$columns = array();
		foreach ($fields as $field)
		{
			$columns[] = "`" . $field . "`";
		}
		$columns = '(' . implode(',', $columns) . ')';
		$places = array();
		$params = array();
		$i = 0;
		foreach ($datas as $data)
		{
			$tmp_places = array();
			$tmp_params = array();
			foreach ($data as $key => $value)
			{
				$tmp_places[] = ":" . $fields[$key] . "_$i";
				$tmp_params[":" . $fields[$key] . "_$i"] = $value;
			}
			$places[] = "(" . implode(',', $tmp_places) . ")";
			$params[] = $tmp_params;
			++$i;
		}
		$places = implode(',', $places);
		$insert_sql = implode(" ", array(
			'INSERT IGNORE INTO',
			'zh_' . $table,
			$columns,
			'VALUES',
			$places
		));
		$stmt = $this->pdo->prepare($insert_sql);
		$this->bindMulti($params, $stmt);
		$result = $stmt->execute();
		if ($result !== true)
		{
			var_dump("Insert error, args" . json_encode(func_get_args()));
			return false;
		}
		return $this->multiLastInsertId($stmt);
	}
	/**
	* 删除操作
	* @param 	$table 			string 	数据表名
	* @param 	$conditions 	array 	执行条件
	* @return 					int 	影响行数
	*/
	public function delete($table, $conditions)
	{
		$key = getmypid();
		$params = array();
		$where = empty($conditions) ? '' : self::biuldMultiWhere($conditions, $params);
		$delete_sql = implode(' ', array(
			'DELETE FROM ',
			'zh_' . $table,
			$where,
		));
		$stmt = $this->pdo->prepare($delete_sql);
		$this->bind($params, $stmt);
		$result = $stmt->execute();
		if ($result === false)
		{
			var_dump('delete error ' . json_encode($delete_sql . func_get_args()));
			return false;
		}
		return $stmt->rowCount();
	}
	/**
	* 更新操作
	* @param 	$table 			string 	数据表名
	* @param 	$conditions 	array 	执行条件
	* @param 	$data 			array 	更新的数据
	* @return 					int 	影响行数
	*/
	public function update($table, $conditions, $data)
	{
		$key = getmypid();
		$columns = array();
		$params = array();
		foreach ($data as $tmp_field => $value)
		{
			$columns[] = "`" . $tmp_field . "` = :" . $tmp_field;
			$params[":" . $tmp_field] = $value;
		}
		$columns = implode(' , ', $columns);
		$where = self::biuldMultiWhere($conditions, $params);
		$update_sql = implode(' ', array(
			'UPDATE',
			'zh_' . $table,
			'SET',
			$columns,
			$where
		));
		$stmt = $this->pdo->prepare($update_sql);
		$this->bind($params, $stmt);
		$result = $stmt->execute();
		if ($result === false)
		{
			var_dump("update error" . json_encode($update_sql . func_get_args()));
			return false;
		}
		return $stmt->rowCount();
	}
	/**
	* 为一个PDO占位符绑定值
	* @param 	$params 	array 	一个占位符与值的键值对数组
	* @param 	$stmt 		object 	PDOStatement对象
	*/
	private function bind($params, &$stmt)
	{
		foreach ($params as $field => $value)
		{
			$stmt->bindValue($field, $value);
		}
	}
	/**
	* 为多个PDO占位符绑定值
	* @param 	$params 	array 	多个占位符与值的键值对数组
	* @param 	$stmt 		object 	PDOStatement对象
	*/
	private function bindMulti($params_array, &$stmt)
	{
		foreach ($params_array as $params)
		{
			$this->bind($params, $stmt);
		}
	}
	/**
	* 返回执行多行插入的最后插入行的ID
	* @param 	$stmt 			object 	PDOStatement对象
	* @return 	$lastInsertedId	int 	多行插入的最后插入行的ID
	*/
	private function multiLastInsertId($stmt)
	{
		$firstInsertedId = $this->pdo->lastInsertId();
		$lastInsertedId = $firstInsertedId + ($stmt->rowCount() - 1);
		return $lastInsertedId;
	}
	/**
	* 根据参数构造where条件
	* @param 	$conditions 	array 	构造条件
	* @param 	$param 			array 	构造后的占位符与值的键值对数组
	* @return 	$ret 			array 	构造后的where条件组成的数组
	*/
	private function buildWhere($conditions, &$params)
	{
		$ret = array();
		foreach ($conditions as $field => $express)
		{
			if (is_scalar($express))
			{
				$ret[] = $field . " = :" . $field;
				$params[":" . $field] = $express;
			}
			elseif (is_array($express))
			{
				foreach ($express as $opeartor => $tmp_val)
				{
					$opeartor = strtoupper($opeartor);
					if (in_array($opeartor, self::$allow_operator))
					{
						$ret[] = $field . " " . $opeartor . " :" . $field;
						$params[":" . $field] = $tmp_val;
					}
				}
			}
		}
		return $ret;
	}
	/*
	$condition = arrray(
		'id' => array('>=' => 3),
		"name" => 'test',
		'desc' => array('like' => "%123")
	);
	$condition = array(
		"where" => array(
			"id" => 1,
		),
		"or_where" => array(
			'id' > array('>=' => 3),
			"name" => 'test',
			'desc' => array('like' => "%123")			
		),
	);
	*/
	/**
	* 根据参数构造多个where条件
	* @param 	$conditions 	array 	构造条件
	* @param 	$param 			array 	构造后的占位符与值的键值对数组
	* @return 	$ret 			string 	构造后的where条件
	*/
	private function biuldMultiWhere($conditions, &$params)
	{
		$where = "";
		$or_where = "";
		if (!empty($conditions['where']) || !empty($conditions['or_where']))
		{
			if (!empty($conditions['where']))
			{
				$tmp_where = $this->buildWhere($conditions['where'], $params);
				$where = " where " . implode(' and ', $tmp_where);
			}
			if (!empty($conditions['or_where']))
			{
				$tmp_or_where = $this->buildWhere($conditions['or_where'], $params);
				$prefix = empty($where) ? " where " : " ";
				$or_where = $prefix . implode(' and ', $tmp_where);
			}
		}
		else
		{
			$tmp_where = $this->buildWhere($conditions, $params);
			$where = ' where ' . implode(' and ', $tmp_where);
		}
		return $where . $or_where;
	}
}
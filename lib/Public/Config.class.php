<?php
class Config{
	/**
	 *@ ÔËÐÐ»·¾³  testÎª²âÊÔ»·¾³£¬onlineÎªÏßÉÏ»·¾³
	 **/
	public static $env = 'test'; 

	/**
	 *@ 数据库配置
	 **/
	public static $db_config = array( 
		'test'=>array(
			'master'=>array( //召唤师主库	 各服务器不同
				'host' => '192.168.0.202',
				'port' => 3306,
				'username' => 'root',
				'password' => 'root',
				'dbname' => 'summon',
				'charset' => 'utf8'
			),
			'slave'=>array( //召唤师从库	 各服务器不同
				'host' => '192.168.0.202',
				'port' => 3306,
				'username' => 'cooldou',
				'password' => 'cooldou159357',
				'dbname' => 'summon',
				'charset' => 'utf8'
			),
			'stats'=>array( //统计用数据库         通用
				'host' => '192.168.0.202',
				'port' => 3306,
				'username' => 'root',
				'password' => 'root',
				'dbname' => 'summon',
				'charset' => 'utf8'
			),
			'config'=>array( //公共配置信息库 + 所有服活动配置库         通用
				'host' => '192.168.0.202',
				'port' => 3306,
				'username' => 'root',
				'password' => 'root',
				'dbname' => 'summon',
				'charset' => 'utf8'
			),
			'action'=>array( //所有服活动配置库                                   与config同一库
				'host' => '192.168.0.202',
				'port' => 3306,
				'username' => 'root',
				'password' => 'root',
				'dbname' => 'summon',
				'charset' => 'utf8'
			),
			'admin'=>array( //管理后台公用库  接口信息         	   与config同一库
				'host' => '192.168.0.202',
				'port' => 3306,
				'username' => 'cooldou',
				'password' => 'cooldou159357',
				'dbname' => 'public',
				'charset' => 'utf8'
			),
			'login'=>array( //公共登录库         通用
				'host' => '192.168.0.202',
				'port' => 3306,
				'username' => 'root',
				'password' => 'root',
				'dbname' => 'summon',
				'charset' => 'utf8'
			)
		),
		'online'=>array(
			'login'=>array( //公共登录库         通用登录接口使用   配置必须写在代码中  
				'host' => '127.0.0.1',
				'port' => 3306,
				'username' => 'summon',
				'password' => 'coolplay159357',
				'dbname' => 'summon',
				'charset' => 'utf8'
			),
			'config'=>array( //公共配置信息库 + 所有服活动配置库         通用  配置必须写在代码中  
				'host' => '127.0.0.1',
				'port' => 3306,
				'username' => 'summon',
				'password' => 'coolplay159357',
				'dbname' => 'summon',
				'charset' => 'utf8'
			),
			'admin'=>array( //管理后台公用库  接口信息         	   		与config同一库   配置必须写在代码中  
				'host' => '127.0.0.1',
				'port' => 3306,
				'username' => 'summon',
				'password' => 'coolplay159357',
				'dbname' => 'public',
				'charset' => 'utf8'
			)
		)
	);
	/**
	 *@ redis ÅäÖÃÐÅÏ¢   grant all privileges on *.* to 'summon'@'%' identified by 'coolplay159357' with grant option;
	 **/
	public static $redis_config = array(
		'test'=>array(
			'redis0' => array('host' => '192.168.0.202', 'port' => 20000, 'pass' => 'coolplay159357'),
			'redis1' => array('host' => '192.168.0.202', 'port' => 20001, 'pass' => 'coolplay159357'),
			'redis2' => array('host' => '192.168.0.202', 'port' => 20002, 'pass' => 'coolplay159357'),
			'redis3' => array('host' => '192.168.0.202', 'port' => 20003, 'pass' => 'coolplay159357'),
			'redis4' => array('host' => '192.168.0.202', 'port' => 20004, 'pass' => 'coolplay159357'),
			'redis5' => array('host' => '192.168.0.202', 'port' => 20005, 'pass' => 'coolplay159357'),
			'redis6' => array('host' => '192.168.0.202', 'port' => 20006, 'pass' => 'coolplay159357'),
			'redis7' => array('host' => '192.168.0.202', 'port' => 20007, 'pass' => 'coolplay159357'),
			'redis8' => array('host' => '192.168.0.202', 'port' => 20008, 'pass' => 'coolplay159357'),
			'redis9' => array('host' => '192.168.0.202', 'port' => 20009, 'pass' => 'coolplay159357'),
			'Cond_0' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_1' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_2' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_3' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_4' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_5' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_6' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_7' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_8' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Cond_9' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_0' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_1' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_2' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_3' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_4' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_5' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_6' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_7' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_8' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Friend_9' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_0' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_1' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_2' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_3' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_4' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_5' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_6' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_7' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_8' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'Login_9' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'uniqPay_0' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_1' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_2' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_3' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_4' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_5' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_6' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_7' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_8' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'uniqPay_9' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),          				#充值订单记录  通用
			'default_1' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'default_2' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
			'default_3' => array('host' => '192.168.0.202', 'port' => 20010, 'pass' => 'coolplay159357'),
		),
		'online'=>array( //ÐÅÏ¢´ýÉÏÏßÈ·ÈÏºóÔÙ½øÐÐÅäÖÃ
			'sync_db' => array('host' => '127.0.0.1', 'port' => 20000, 'pass' => 'coolplay159357'),          					#保存同步sql语句的redis
			'default_1' => array('host' => '127.0.0.1', 'port' => 20000, 'pass' => 'coolplay159357'),         					#当前区公共配置 通用   三个端口分流用   default_1为 主redis
			'default_2' => array('host' => '127.0.0.1', 'port' => 20000, 'pass' => 'coolplay159357'),         					#当前区公共配置 通用
			'default_3' => array('host' => '127.0.0.1', 'port' => 20000, 'pass' => 'coolplay159357'),         					#当前区公共配置 通用
		));



	function __construct( $type='' ){
		global $serverId;
		empty( $serverId ) && $serverId = 2;
		if( empty( $type ) || $type=='default' ){  #默认分流
			$rand = mt_rand( 1, MAX_DEFAULT_REDIS_PORT_NUMS );
			$type = 'default_'.$rand;
		}
		$this->type = $type;
		$this->sid = $serverId;
	}
/**
 *@ 获取指定tag的Db配置信息
 **/
	function getDbConfig(){
		if( !empty( $this->type ) ){
			if( !isset( self::$db_config[self::$env][$this->type] ) ){
				$ser = new Server($this->sid);
				$dbList = $ser->getDbList();
				foreach( $dbList as $k=>$v ){
					self::$db_config[self::$env][ $k ] = $v;
				}
			}
		}
		return isset( self::$db_config[self::$env][$this->type] ) ? self::$db_config[self::$env][$this->type] : self::$db_config[self::$env]['slave'];
	}
/**
 *@ 获取指定tag的redis配置信息
 * return: 默认返回当前区的公共数据端口 20010
 **/
	function getRedisList(){
		if( !empty( $this->type ) ){
			if( is_numeric($this->type) ){
				$this->type = 'redis'.( $this->type%USER_TAG );
			}
			if( !isset( self::$redis_config[self::$env][$this->type] ) ){
				$ser = new Server( $this->sid );
				$List = $ser->getRedisList();
				foreach( $List as $k=>$v ){
					self::$redis_config[self::$env][ $k ] = $v;
				}
			}
		}
		if( isset( self::$redis_config[self::$env][$this->type] ) ){
			return self::$redis_config[self::$env][$this->type];
		}else{
			return self::$redis_config[self::$env]['default_1'];
		}
	}

	function getType(){
		return $this->type;
	}
}
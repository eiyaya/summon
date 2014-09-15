<?php
/**
 *@ 用户基类
 **/
 class User_Base extends Base{
 	protected static $recordInfo=array();		//用户对应的record信息 对就zy_uniqRoleRecord表 
 	protected static $throwSQL=array();			//需要异步同步的数据
	private static $userinfo=array(); 			//角色所有信息
	private static $updinfo=array(); 			//角色所有信息
	private static $isupd=array(); 				//判断是否需要更新用户信息
	protected static $missionIdList=array();			//动作触发任务类型列表 如果有触发在析构函数中自动处理
	
	protected $baseTable='zy_uniqRole';			//用户角色表
	protected $baseRecordTable = 'zy_uniqRoleRecord';	//用户record信息表
	
	private $upInfo; 					//角色升级对应参数表
	private $uinfo;	 					//角色信息

	private $userLog;					//用户金钱变化日志

	public function __construct( $uid='' ){
		if( empty( $uid ) ){
			$uid = (int)getReq('uid',381440);
		}
		parent::__construct($uid);
		if( empty($this->uid) ){
			$this->log->e('uid is null');
			ret( 'uid is null' );
		}		
		$this->_init();
	}

	private function _init(){
		$this->redis;
		if( C('test') || !isset( self::$userinfo[$this->uid] ) || empty(self::$userinfo[$this->uid]) || !is_array( self::$userinfo[$this->uid] ) ){
			if( C('test') || !$this->redis->exists('roleinfo:'.$this->uid.':baseinfo') ){
				$this->db;
				$uinfo = $this->db->findOne($this->baseTable,'*',array( 'userid'=>$this->uid ));
				if( empty($uinfo) ){
					$this->log->e('no user uid='.$this->uid);
					ret('no user!');
				}
				$uinfo['skey'] = md5( gettimeofday(true).rand(1000,9999) ); //登录校验
				$uinfo['lastUpdTime'] = time(); //用户信息最后更新时间
				$uinfo['mail'] = 0; //邮件标记
				$this->redis->del('roleinfo:'.$this->uid.':baseinfo');
				$this->redis->hmset('roleinfo:'.$this->uid.':baseinfo',$uinfo);
			}else{
				$uinfo = $this->redis->hgetall('roleinfo:'.$this->uid.':baseinfo');
			}
			self::$userinfo[$this->uid] = $uinfo;
		}
		self::$updinfo[$this->uid] = array();
		self::$recordInfo[$this->uid] = array();
		$this->uinfo = self::$userinfo[$this->uid];
	}
/**
 *@ 根据名称查找好友信息
 **/
	function getInfo( $name ){
		$this->db;
		return $this->db->findOne( $this->baseTable,'`userid` id,`nickname`,`image`',array( 'nickname'=>$name ) );
	}
/**
 *@ 获取角色信息
 **/
	public function getUserInfo(){
		return self::$userinfo[$this->uid];
	}
/**
 *@ 获取角色id
 **/
	public function getUid(){
		return (int)$this->uid;
	}
/**
 *@ 获取角色所在服务器id
 **/
	public function getServerId(){
		return (int)self::$userinfo[$this->uid]['sid'];
	}
/**
 *@ 获取角色注册服id
 **/
	public function getRid(){
		return (int)self::$userinfo[$this->uid]['rid'];
	}
/**
 *@ 获取角色昵称
 **/
	public function getUserName(){
		return self::$userinfo[$this->uid]['nickname'];
	}
 /**
 *@ 获取角色头像
 **/
	public function getImage(){
		return self::$userinfo[$this->uid]['image'];
	}
/**
 *@  getUserMaxLife 获取角色最大体力上限
 **/
	public function getUserMaxLife(){
		return self::$userinfo[$this->uid]['maxLife'];
	}
/**
 *@  getUserReductTime 获取角色最大体力上限
 **/
	public function getUserReductTime(){
		return self::$userinfo[$this->uid]['lastDeductTime'];
	}
/**
 *@ 获取角色体力值
 **/
	public function getLife(){
		return $this->_resetLife();
	}
/**
 *@ 恢复角色体力值
 **/
	private function _resetLife(){
		$recover = 600;	//恢复一点需要时间
		$max = $this->getUserMaxLife();
		$now = time();
		$tolTime = $now - $this->getUserReductTime(); //已经恢复的总时间 
		$giveLife = floor($tolTime/$recover);
		if( self::$userinfo[$this->uid]['life'] >= $max ){ //体力本身就是满的
			self::$updinfo[$this->uid]['lastDeductTime'] = self::$userinfo[$this->uid]['lastDeductTime'] = time();
			#return self::$userinfo[$this->uid]['life'];
		}elseif( ( self::$userinfo[$this->uid]['life'] + $giveLife ) > $max ){
			self::$updinfo[$this->uid]['life'] = self::$userinfo[$this->uid]['life'] = $max;
			self::$updinfo[$this->uid]['lastDeductTime'] = self::$userinfo[$this->uid]['lastDeductTime'] = time();
		}else{
			$lastTime = $this->getUserReductTime() + ( $giveLife * $recover );
			self::$updinfo[$this->uid]['life'] = self::$userinfo[$this->uid]['life'] += $giveLife;
			self::$updinfo[$this->uid]['lastDeductTime'] = self::$userinfo[$this->uid]['lastDeductTime'] = $lastTime;
		}
		return (int)self::$userinfo[$this->uid]['life'];
	}
/**
 *@ 扣除用户体力
 **/
	public function reduceLife( $nums ){
		$ulife = $this->getLife();
		if( $nums>0 && $ulife>=$nums ){
			$this->setUpdTime(3);
			$this->log->i('* 用户#'.$this->uid.'#扣除#'.$nums.'#体力'.self::$userinfo[$this->uid]['life'].'->'.( self::$userinfo[$this->uid]['life']-$nums ) );
			self::$updinfo[$this->uid]['life'] = self::$userinfo[$this->uid]['life'] = self::$userinfo[$this->uid]['life']-$nums;
			return self::$userinfo[$this->uid]['life'];
		}else{
			return false;
		}
	}
/**
 *@ 添加用户体力
 **/
	public function addLife( $nums ){
		$this->_resetLife();
		if( $nums > 0 ){
			$this->setUpdTime(3);
			$this->log->i('* 用户#'.$this->uid.'#添加#'.$nums.'#体力'.self::$userinfo[$this->uid]['life'].'->'.( self::$userinfo[$this->uid]['life']+$nums ) );
			self::$updinfo[$this->uid]['life'] = self::$userinfo[$this->uid]['life'] = self::$userinfo[$this->uid]['life'] +$nums;
			return self::$userinfo[$this->uid]['life'];
		}else{
			return $this->reduceLife(-$nums);
		}
	}
/**
 *@ 获取角色等级
 **/
	public function getLevel(){
		return (int)self::$userinfo[$this->uid]['level'];
	}
/**
 *@ 获取角色当前经验
 **/
	public function getExp(){
		return (int)self::$userinfo[$this->uid]['exp'];
	}
/**
 *@ 获取角色当前钻石数量
 **/
	public function getCooldou(){
		return (int)self::$userinfo[$this->uid]['jewel'];
	}
/**
 *@ 获取角色当前钻石数量
 **/
	public function getMoney(){
		return (int)self::$userinfo[$this->uid]['money'];
	}
/**
 *@ 获取角色当前vip等级
 **/
	public function getVlevel(){
		return (int)self::$userinfo[$this->uid]['vlevel'];
	}
/**
 *@ 角色是否是月卡用户
 **/
	public function isMonthCode(){
		$ret = 0;
		if( self::$userinfo[$this->uid]['monthCode'] > 0 && self::$userinfo[$this->uid]['mCodeOverTime'] > time() ){
			$ret = 1;
		}
		return $ret;
	}
/**
 *@ 获取角色登录校验码
 **/
	public function getSkey(){
		return self::$userinfo[$this->uid]['skey'];
	}
/**
 *@ 获取角色最大朋友数量
 **/
	public function getMaxFriends(){
		$fNums = self::$userinfo[$this->uid]['friends'];
		if( $this->getVlevel() > 0 ){
			$vip  = new Vip( $this->getVlevel() );
			$fNums += $vip->getExtFriends(); 
		}
		return (int)$fNums;
	}
/**
 *@ getHeroMaxLevel() 获取当前召唤师等级下能召唤英雄的最高等级
 **/
	public function getHeroMaxLevel(){
		return (int)self::$userinfo[$this->uid]['maxHeroLevel'];
	}
/**
 *@ 获取角色信息最后更新时间
 **/
	public function getLastUpdTime(){
		return self::$userinfo[$this->uid]['lastUpdTime'];
	}
/**
 *@ 获取用户的充值总金额
 **/
	public function getTotalPay(){
		return (int)self::$userinfo[$this->uid]['totalPay'];
	}
/**
 *@ 设置用户月卡
 **/
	public function setMonthCode(){
		$this->setUpdTime(3);
		$this->log->i( '* 用户#'.$this->uid.'#充值月卡' );
		self::$updinfo[$this->uid]['monthCode'] = self::$userinfo[$this->uid]['monthCode'] = 1;
		self::$updinfo[$this->uid]['mCodeOverTime'] = self::$userinfo[$this->uid]['mCodeOverTime'] = time() + 86400*30;
		return true;
	}
/**
 *@ 设置用户头像
 **/
	public function setUserImage( $img ){
		$this->setUpdTime(3);
		$this->log->i('* 用户#'.$this->uid.'#修改头像'.self::$userinfo[$this->uid]['image'].'->'.$img);
		self::$updinfo[$this->uid]['image'] = $img;
		return self::$userinfo[$this->uid]['image'] = $img;
	}
/**
 *@ 设置用户最后登录时间
 **/
	public function setLoginTime(){
		$this->setUpdTime(3);
		return self::$userinfo[$this->uid]['logintime'] = self::$updinfo[$this->uid]['logintime'] = time();
	}
/**
 *@ 设置用户昵称 ( 不能同名 )
 **/
	public function setUserName( $name ){
		$this->setUpdTime(3);
		$this->log->i('* 用户#'.$this->uid.'#修改名称'.self::$userinfo[$this->uid]['nickname'].'->'.$name);
		self::$userinfo[$this->uid]['nickname'] = $name;
		self::$updinfo[$this->uid]['nickname'] = $name;
		return true;
	}
/**
 *@ 设置用户私信标记
 **/
	public function setMessageFlag( $val ){
		$this->setUpdTime(1);
		return self::$userinfo[$this->uid]['message'] = $val;
	}
/**
 *@ 设置用户邮件标记
 **/
	public function setNewMail(){
		$this->setUpdTime(1);
		return self::$userinfo[$this->uid]['mail'] = 1;
	}
/**
 *@ 设置用户任务完成标记
 **/
	public function setMissionNum(){
		$this->setUpdTime(1);
		if( isset( self::$userinfo[$this->uid]['mission'] ) ){
			return self::$userinfo[$this->uid]['mission'] += 1;
		}else{
			return self::$userinfo[$this->uid]['mission'] = 1;
		}
	}
/**
 *@ 用户领取任务奖励后自动减1
 **/
	public function reduceMissionNum(){
		$this->setUpdTime(1);
		if( isset( self::$userinfo[$this->uid]['mission'] ) &&  self::$userinfo[$this->uid]['mission']>0 ){
			return self::$userinfo[$this->uid]['mission'] -= 1;
		}else{
			return self::$userinfo[$this->uid]['mission'] = 0;
		}
	}
/**
 *@ 添加用户金币
 **/
	public function addMoney( $nums ){
		if( $nums > 0 ){
			$this->userLog['source'] = 1;
			$this->userLog['type'] = 1;
			$this->userLog['nums'] = $nums;

			$this->setUpdTime(2);
			$this->log->i('* 用户#'.$this->uid.'#添加#'.$nums.'#金币'.self::$userinfo[$this->uid]['money'].'->'.( self::$userinfo[$this->uid]['money']+$nums ) );
			self::$updinfo[$this->uid]['money'] = self::$userinfo[$this->uid]['money'] + $nums;
			return self::$userinfo[$this->uid]['money'] += $nums;
		}else{
			return $this->reduceMoney( -$nums );
		}
	}
/**
 *@ 扣除用户金币
 **/
	public function reduceMoney( $nums ){
		$umoney = $this->getMoney();
		if( $nums>0 && $umoney>=$nums ){
			$this->userLog['source'] = 1;
			$this->userLog['type'] = 1;
			$this->userLog['nums'] = $nums;

			$this->setUpdTime(2);
			$this->log->i('* 用户#'.$this->uid.'#扣除#'.$nums.'#金币'.self::$userinfo[$this->uid]['money'].'->'.( self::$userinfo[$this->uid]['money']-$nums ) );
			self::$updinfo[$this->uid]['money'] = self::$userinfo[$this->uid]['money'] - $nums;
			return self::$userinfo[$this->uid]['money'] -= $nums;
		}else{
			return false;
		}
	}
/**
 *@ 添加用户钻石
 **/
	public function addCooldou( $nums ){
		if( $nums > 0 ){
			$this->userLog['source'] = 2;
			$this->userLog['type'] = 1;
			$this->userLog['nums'] = $nums;

			$this->setUpdTime(2);
			$this->log->i('* 用户#'.$this->uid.'#添加#'.$nums.'#钻石'.self::$userinfo[$this->uid]['jewel'].'->'.( self::$userinfo[$this->uid]['jewel']+$nums ) );
			self::$updinfo[$this->uid]['jewel'] = self::$userinfo[$this->uid]['jewel'] + $nums;
			return self::$userinfo[$this->uid]['jewel'] += $nums;
		}else{
			return $this->reduceCooldou( -$nums );
		}
	}
/**
 *@ 扣除用户钻石
 **/
	public function reduceCooldou( $nums ){
		$umoney = $this->getCooldou();
		if( $nums == 0 ){return true;}
		if( $nums>0 && $umoney>=$nums ){
			$this->userLog['source'] = 2;
			$this->userLog['type'] = 1;
			$this->userLog['nums'] = $nums;

			$this->setUpdTime(2);
			$this->log->i('* 用户#'.$this->uid.'#扣除#'.$nums.'#钻石'.self::$userinfo[$this->uid]['jewel'].'->'.( self::$userinfo[$this->uid]['jewel']-$nums ) );
			self::$updinfo[$this->uid]['jewel'] = self::$userinfo[$this->uid]['jewel'] - $nums;
			return self::$userinfo[$this->uid]['jewel'] -= $nums;
		}else{
			return false;
		}
	}
/**
 *@ 用户充值总数累加
 **/
	public function addTotalPay( $nums ){
		if( $nums>0 ){
			$this->setUpdTime(3);
			self::$userinfo[$this->uid]['totalPay'] += $nums;
			self::$updinfo[$this->uid]['totalPay'] = self::$userinfo[$this->uid]['totalPay'];
			$vip = new Vip( $this->getVlevel() );
			$vlevel = $vip->getVipLevelByExp( self::$userinfo[$this->uid]['totalPay'] );
			if( $vlevel > $this->getLevel() ){
				$this->setVip( $vlevel );
			}
			return true;
		}else{
			return false;
		}
	}
/**
 *@ 添加召唤师buff
 **/
	public function addRoleBuff( $buffid ){
		$buff = new Buff( $buffid );
		$this->redis->set( 'roleinfo:'.$this->uid.':buff:'.$buff->getType(), $buffid, $buff->getTime() );
		return array( 'overTime'=>$buff->getTime(), 'bid'=>$buffid );
	}
/**
 *@ 获取召唤师已拥有的buff
 **/
	public function getRoleBuff(){
		$ret=array();
		$keys = $this->redis->keys('roleinfo:'.$this->uid.':buff:*');
		if( is_array( $keys ) )
			foreach( $keys as $v ){
				$buff['overTime'] = (int)$this->redis->ttl( $v );
				$buff['bid'] = (int)$this->redis->get( $v );
				$ret[] = $buff;
				unset($buff);
			}
		return $ret;
	}
/**
 *@ 添加角色经验 升级逻辑
 **/
	public function addExp( $nums ){
		$this->upInfo = new Levelup( $this->uinfo['level'] ); //角色升级表
		$exp = $this->getExp();
		$tolexp = $exp + $nums;
		$upinfo = $this->upInfo->getUpinfo();

		if( self::$userinfo[$this->uid]['level'] >= $this->upInfo->getMaxLevel() && self::$userinfo[$this->uid]['exp'] >=  $upinfo['exp'] ){
			$this->log->e('* 召唤师#'.$this->uid.'#等级达到最大，经验已满'.$upinfo['exp']);
			#self::$updinfo[$this->uid]['exp'] = self::$userinfo[$this->uid]['exp'] =  $upinfo['exp'];
			return true;
		}

		$nextinfo = $this->upInfo->getNextUpinfo();
		while( $tolexp >= $upinfo['exp'] ){
			$tolexp = $tolexp - $upinfo['exp'];
			self::$userinfo[$this->uid]['level'] = $nextinfo['level'];
			self::$userinfo[$this->uid]['exp'] = $tolexp;
			self::$userinfo[$this->uid]['maxLife'] = $nextinfo['life'];
			self::$userinfo[$this->uid]['life'] += $nextinfo['getLife'];
			#self::$userinfo[$this->uid]['lead'] = $nextinfo['lead'];
			self::$userinfo[$this->uid]['friends'] = $nextinfo['friends'];
			#self::$userinfo[$this->uid]['pageNum'] = $nextinfo['pnum'];
			self::$userinfo[$this->uid]['maxHeroLevel'] = $nextinfo['HeroLevel'];
			self::$updinfo[$this->uid]['level'] = $nextinfo['level'];
			self::$updinfo[$this->uid]['exp'] = $tolexp;
			self::$updinfo[$this->uid]['life'] = $nextinfo['life'];
			self::$updinfo[$this->uid]['maxLife'] = self::$userinfo[$this->uid]['maxLife'];
			#self::$updinfo[$this->uid]['lead'] = $nextinfo['lead'];
			self::$updinfo[$this->uid]['friends'] = $nextinfo['friends'];
			#self::$updinfo[$this->uid]['pageNum'] = $nextinfo['pnum'];
			self::$updinfo[$this->uid]['maxHeroLevel'] = $nextinfo['HeroLevel'];
			$this->upInfo = new Levelup( $nextinfo['level'] );
			$upinfo = $this->upInfo->getUpinfo();
			$nextinfo = $this->upInfo->getNextUpinfo();
			self::$missionIdList[1][] = 51;
			$this->log->i('* 用户#'.$this->uid.'#升级到 '.self::$userinfo[$this->uid]['level'].' 级 ');
			if( self::$userinfo[$this->uid]['level'] >= $this->upInfo->getMaxLevel() && $tolexp >= $upinfo['exp'] ){
				self::$updinfo[$this->uid]['exp'] = self::$userinfo[$this->uid]['exp'] = $upinfo['exp'];
				$this->log->e( '* 召唤师#'.$this->uid.'#等级达到最大，经验已满->'.self::$userinfo[$this->uid]['exp'] );
				break;
			}
		}
		if( $upinfo['level'] == $this->uinfo['level'] ){
			self::$userinfo[$this->uid]['exp'] += $nums;
			self::$updinfo[$this->uid]['exp'] = self::$userinfo[$this->uid]['exp'];
		}
		$this->log->i('* 用户#'.$this->uid.'#获得'.$nums.'经验，'.$this->uinfo['exp'].'->'.self::$userinfo[$this->uid]['exp'] );
		$this->setUpdTime(3);
		return true;
	}
/**
 *@ setVip() 	设置用户vip等级
 **/
	public function setVip( $vLevel ){
		$this->setUpdTime(3);
		self::$updinfo[$this->uid][ 'vlevel' ] = $vLevel ;
		return self::$userinfo[$this->uid][ 'vlevel' ] = $vLevel ;
	}
/**
 *@ setMissionId() 	设置相关任务完成进度
 **/
	public function setMissionId( $type, $class ){
		return self::$missionIdList[$type][] = $class;
	}
/**
 *@ 公共代理类
 *@ param:
 *	$cName: 类名
 *	$fName: 方法名
 *	$args:	 初始化类的构造参数
 **/
	public function proxy( $args=array(), $cName= 'User_Mission' , $fName= 'setUserMissing'  ){
		$ret = new Proxy( $args,$cName,$fName );
		return $ret;
	}
/**
 *@ setUpdTime() 设置信息更新标志
 *@ param:
 *	$flag:	标志位  为0时标记需要更新redis，1 标记需要更新心跳，2 金币或钻石数量发生变化需要插入财富流水日志库
 **/
	private function setUpdTime($flag=1){
		self::$isupd[$this->uid] = $flag;
		if( $flag > 1 ){
			self::$userinfo[$this->uid]['lastUpdTime'] = time();
		}
		if( $flag == 2 ){ //金币或钻石发生变化
			global $tag;
			$this->userLog['sid'] = self::$userinfo[$this->uid]['sid'];
			$this->userLog['uid'] = $this->uid;
			$this->userLog['tag'] = $tag;
			$this->userLog['time'] = date('Y-m-d H:i:s');
			$this->setThrowSQL( 'zy_statsUserLog', $this->userLog, '', 1, 'stats' );
			//$this->sdb->insert('zy_statsUserLog',$this->userLog);
		}
	}
/**
 *@ throwSQL 如果信息有改动抛出sql语句后台自动同步
 **/
	public function throwSQL( $table, $upd, $where='',$opt='', $db='' ){
		$init['table'] = $table;
		$init['data'] = $upd;
		$init['where'] = $where;
		$init['opt'] = $opt;
		$init['tag'] = $db;
		$proxy = $this->proxy( $init, 'Sync', 'sendCommand' );
		$proxy->exec();
	}
/**
 *@ setThrowSQL 如果信息有改动抛出sql语句后台自动同步
 **/
	public function setThrowSQL( $table, $upd, $where='', $opt='', $db='' ){
		$init['table'] = $table;
		$init['data'] = $upd;
		$init['where'] = $where;
		$init['opt'] = $opt;
		$init['db'] = $db;
		self::$throwSQL[] = $init;
		return true;
	}
/**
 *@ getUserLastUpdInfo 获取用户信息中发生变化的那部分
 **/
	public function getUserLastUpdInfo(){
		if( isset(self::$recordInfo[$this->uid]) && is_array( self::$recordInfo[$this->uid] ) ){
			return array_merge(self::$updinfo[$this->uid],self::$recordInfo[$this->uid]);
		}else{
			return self::$updinfo[$this->uid];
		}
	}
#====== * 用户设置或同步用户zy_uniqRoleRecord表中的信息 ==========================================================
/**
 *@ setUserRecord() 设置用户的记录表信息
 *param:
 * 	$key: 	对应 zy_uniqRoleRecord 表中的属性值
 *	$value:	$key 对应的值
 **/
	public function setUserRecord( $key, $value ){
		return self::$recordInfo[$this->uid][$key] = $value;
	}
/**
 *@ getUserRecord() 设置用户的记录表信息
 *param:
 * 	$key: 	对应 zy_uniqRoleRecord 表中的属性值
 **/
	public function getUserRecord( $key ){
		return (int)self::$userinfo[$this->uid][$key];
	}
/**
 *@ addUserRecord() 添加或减少用户的记录表信息
 *param:
 * 	$key: 	对应 zy_uniqRoleRecord 表中的属性值
 *	$value:	$key 对应需要添加的值
 **/
	public function addUserRecord( $key, $value ){
		return self::$recordInfo[$this->uid][$key] = (int)self::$userinfo[$this->uid][$key] + $value;
	}
#============================================================================================
	public function __destruct(){
		# 同步用户信息
		if( isset( self::$isupd[$this->uid] ) && self::$isupd[$this->uid] > 0 ){ 
			$this->redis->hmset('roleinfo:'.$this->uid.':baseinfo',self::$userinfo[$this->uid]);
			if( self::$isupd[$this->uid] >= 2 && !empty( self::$updinfo[$this->uid] ) ){
				$this->throwSQL( $this->baseTable, self::$updinfo[$this->uid], array('userid'=>$this->uid) );
				self::$updinfo[$this->uid] = '';
			}
			self::$isupd[$this->uid] = 0;
		}
		#同步用户record信息
		if( is_array( self::$recordInfo[$this->uid] ) && !empty( self::$recordInfo[$this->uid] ) ){
			$this->redis->hmset('roleinfo:'.$this->uid.':baseinfo',self::$recordInfo[$this->uid]);
			$this->throwSQL( $this->baseRecordTable, self::$recordInfo[$this->uid], array('uid'=>$this->uid) );
			self::$recordInfo[$this->uid]='';
		}
		#同步用户任务信息
		if( !empty( self::$missionIdList ) && count( self::$missionIdList>0 ) ){
			$missionIdList = self::$missionIdList;
			self::$missionIdList = array();
			foreach( $missionIdList as $k=>$val ){
				$proxy = $this->proxy( $k );
				foreach( $val as $v ){
					$proxy->exec( $v );
				}
			}
		}
		#命令行模式启动，抛出sql语句
		if( !empty( self::$throwSQL ) && count( self::$throwSQL>0 ) ){
			$throwSQL = self::$throwSQL;
			self::$throwSQL = array();
			foreach( $throwSQL as $val ){
				$this->throwSQL( $val['table'], $val['data'], $val['where'], $val['opt'] );
			}
		}
	}
 }
?>
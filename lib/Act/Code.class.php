<?php
/**
 *@ Act_Code 兑换码通用类
 **/
 class Act_Code extends User_Base{
 	private $table='zy_actionCdkey';	//兑换码表
	private $code;						//兑换码 	
	private $channel;					//兑换码来源渠道	
	private $keyType;					//兑换码类型
	private $errInfo='兑换成功';		//兑换码错误信息
	private $cond;

	public function __construct( $code,$uid='' ){
		parent::__construct( $uid );
		$this->code = $code;
		$this->channel = substr( $this->code, 0, strpos( $this->code, 'a' ) );
		$this->keyType = substr( $this->code, -1 );
		$this->cond = new Cond( $this->table, $uid, get3time() );
	}
/**
 *@ getExchangeInfo() 获取兑换结果信息
 **/
	function  getExchangeInfo(){
		return $this->errInfo;
	}

/**
 *@ 获取兑换码对应的奖品配置信息
 **/
	public function getConfig(){
		if( $this->cond->get( $this->channel.':'.$this->keyType ) ){
			$this->errInfo = '您已经领取过该类型的激活码了';
			return false;
		}
		$this->adb;
		$this->log->d( '~~~~~~~~~~~~~~~~~~~~~~ SELECT DB ~~~~~~~~~~~~~~~~~~~~~~~' );
		$keyConfig = $this->adb->findOne( $this->table,'*',array( 'cdkey'=>$this->code ) );

		if( empty( $keyConfig ) ){
			$this->log->e(' 用户#'.$this->uid.'#使用兑换码#'.$this->code.'#无效，兑换结束');
			$this->errInfo = ' 无效兑换码 ';
			return false;
		}
		if( $keyConfig['status'] > 0 ) {
			$this->log->e(' 用户#'.$this->uid.'#使用的兑换码#'.$this->code.'#已经被（'.$keyConfig['userName'].'）使用，兑换结束');
			$this->errInfo = ' 兑换码已被（'.$keyConfig['userName'].'）使用 ';
			return false;
		}
		if( $keyConfig['overTime'] < time() ) {
			$this->log->e(' 用户#'.$this->uid.'#使用兑换码#'.$this->code.'#已过期，兑换结束');
			$this->errInfo = ' 兑换码已过期 ';
			return false;
		}		
		$this->setCodeUsed();
		$this->cond->set( 1, $this->channel.':'.$this->keyType, $keyConfig['overTime'] - time() );
		return $keyConfig['goods'];
	}
/**
 *@ setCodeUsed() ; 设置兑换码已被使用
 **/
	function setCodeUsed(){
		$upd['uid'] = $this->uid;
		$upd['userName'] = $this->getUserName();
		$upd['status'] = 1;
		$upd['useTime'] = time();
		$upd['serverId'] = $this->getServerId();
		$upd['rid'] = $this->getRid();
		$this->setThrowSQL($this->table,$upd,array('cdkey'=>$this->code),'','action');
		#$this->adb->update( $this->table,$upd,array( 'cdkey'=>$this->code ) );
	}
}
?>
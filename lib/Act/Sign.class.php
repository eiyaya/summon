<?php
/**
 *@ 每日签到类
 **/
class Act_Sign extends User_Base{
	private $table='zy_baseSignConfig';						//签到配置表
	private $month;											//当月缩写
	private $tolDay;										//累计签到天数
	private $cond;											//cond 对应的redis连接源
	private $overTime;										//过期时间
	private $userSigninfo;									//当前用户的签到信息
	private $tolSign;										//签到总次数

	function __construct( $uid ){
		parent::__construct( $uid );
		$this->pre;
		$month = $this->pre->hget( 'action:sign:month_checked','month');
		$this->month = !empty( $month ) ? $month : (int) date('m');
		$this->overTime = mktime(3,0,0,($this->month+1),1)-time(); //签到过期时间为下月1号早上3点
		$this->cond = new Cond( 'actionSign_'.$this->month, $this->uid, $this->overTime );
		$this->_init();
	}
/**
 *@ 每日签到数据初始化
 **/
	private function _init(){
		/**
		 *@ 拉取签到奖品配置信息
		 **/
		if( C('test') || !$this->pre->hget('action:sign:month_checked','check') ){
			$this->cdb;$this->preMaster;$this->pre=$this->preMaster;
			$this->preMaster->hdel('action:sign:month:*');
			$ret = $this->cdb->find( $this->table,'*',array( 'Sign_Month'=>$this->month ) );
			$this->log->d(' ========================== ~ select db ~ ========================= ');
			$this->log->d( ' DB_CONFIG '.json_encode($this->cdb->getConfig()) );
			if( empty( $ret ) ){
				$this->log->e( '本月（'.$this->month.'月）签到未配置' );
				#ret( 'no_config_'.$this->month );
				$ret = $this->cdb->find( $this->table,'*',array( 'Sign_Month'=>0 ) );	//默认配置
			}
			foreach( $ret as $v ){
				$this->preMaster->hmset( 'action:sign:month:'.$v['Sign_Day'],$v );
			}
			$this->preMaster->hset( 'action:sign:month_checked','check',1 );
			$this->preMaster->hset( 'action:sign:month_checked','month',$this->month );
			$this->preMaster->expire( 'action:sign:month_checked',$this->overTime  ); //签到过期时间为下月第一天的 3：00：00
		}
	}
/**
 *@ 清除签到相关数据
 **/
	function delCache(){
		$this->preMaster;$this->pre=$this->preMaster;
		$this->preMaster->del('action:sign:month_checked');
		$this->cond->delDayTimes('common');
		$this->cond->delDayTimes('vip');
	}
/**
 *@ 拉取每月签到配置信息
 **/
	public function getSignConfig( $month ){
		$this->log->d( ' this->month = '.$this->month.' month = '.$month );
		if( $month != $this->month ){
			$keys = $this->pre->keys( 'action:sign:month:*' );
			$ret = $ret['list'][] = array();
			foreach( $keys as $v ){
				if( count( $ret['list'] ) >= getthemonth() )continue;
				$ret['list'][] = $this->pre->hgetall( $v );
			}
		}
		$ret['tol'] = $this->getTotalTimes();
		$ret['com']= $this->getCommonTimes();
		$ret['vip'] = $this->getVipTimes();
		$ret['month'] = (int)$this->month;
		return $ret;
	}
/**
 *@ 获取用户当日签到次数
 **/
	public function getCommonTimes(){
		return (int)$this->cond->getDayTimes('common');
	}
/**
 *@ 获取vip用户当日签到次数
 **/
	public function getVipTimes(){
		return (int)$this->cond->getDayTimes('vip');
	}
/**
 *@ 判断是否可以签到 
 **/
	public function checkSign(){
		if( $this->getCommonTimes() > 0 ){
			$dayConfig = $this->getDayConf();
			if( !empty( $dayConfig['Double_NeedVip'] ) )
				if( $this->getVlevel() >= $dayConfig['Double_NeedVip'] && $this->getVipTimes() == '' )
					return 1;
			return 0;
		}
		return 1;
	}	
/**
 *@ 获取用户本月累计签到次数
 **/
	public function getTotalTimes(){
		return (int)$this->cond->get('total');
	}
/**
 *@ 获取此次签到的配置信息
 **/
	public function getDayConf(){
		$signInfo = $this->cond->get('total');
		if( empty( $signInfo ) ){
			$this->tolSign = 1;
		}else{
			$this->tolSign = $signInfo+1;
		}
		$daySign = $this->getCommonTimes();
		$vipSign =$this->getVipTimes();
		if( $daySign>0 && $vipSign<1 ){
			$this->tolSign -= 1;
		}
		$dayConfig = $this->pre->hgetall( 'action:sign:month:'.$this->tolSign );

		return $dayConfig;
	}
/**
 *@ 执行签到动作
 **/
	public function signIn(){
		$vLevel = $this->getVlevel();
		$daySign = $this->getCommonTimes();
		$vipSign =$this->getVipTimes();
		$this->log->i( '用户#'.$this->uid.'#今日签到次数：com->'.$daySign.' & vip->'.$vipSign.' & vLevel:'.$this->getVlevel() );
		if( $daySign>0 && $vipSign>0 ){
			return false;
		}
		
		$dayConfig = $this->getDayConf();
		$addNums = $dayConfig['Item_Num'];
		$add = false;
		if( empty($daySign) ){//普通签到物品领取
			$this->log->i('* 每日签到普通用户物品发放');
			switch ( $dayConfig['Item_Id'] ) {
				case '90001':
					# code...
					$add['money'] += $addNums;
					#$this->addMoney( $addNums  );
					break;
				case '90002':
					$add['cooldou'] += $addNums;
					#$this->addCooldou( $addNums  );
					break;
				default:
					$give[] = $dayConfig['Item_Id'].','.$addNums;
					/*$good->addGoods( $addNums  );
					$ret = $good->getLastUpdGoods();*/
					break;
			}
			#$this->setMissionId(1,62);
			$this->cond->set( $this->tolSign,'total' );
			$this->cond->setDayTimes(1,'common');	
		}
		$this->log->i( 'dayConfig:'.json_encode($dayConfig) );
		if( !empty($dayConfig['Double_NeedVip']) ){
			if( empty($vipSign)  && $vLevel >=  (int)$dayConfig['Double_NeedVip'] ){//vip用户达到要求再奖励一次
				$this->log->i('* 每日签到（vip'.$dayConfig['Double_NeedVip'].'及以上） 双倍奖励发放');
				switch ( $dayConfig['Item_Id'] ) {
					case '90001':
						# code...
						$add['money'] += $addNums;
						break;
					case '90002':
						$add['cooldou'] += $addNums;
						break;
					default:
						$give[] = $dayConfig['Item_Id'].','.$addNums;
						break;
				}
				$this->cond->setDayTimes(1,'vip');
			}else{
				$this->log->e('签到来这里了，vlevel:'.$this->getVlevel().', needVip:'.$dayConfig['Double_NeedVip']);
			}
		}else{
			$this->cond->setDayTimes(1,'vip');
		}
		$this->log->i( 'add:'.json_encode($add) );

		if( isset( $give ) )
			$add['good'] = implode('#',$give);
		else
			$this->log->e('每日领取数据配置信息错误：'.json_encode($dayConfig));
		return $add;
	}
}
?>
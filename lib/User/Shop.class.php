<?php
/**
 *@ 商店通用类
 **/
 class User_Shop extends User_Base{
	 //商店固定刷新时间配置
	 private $shopConfig = array(
		1=>array(				//普通商店
			'time'=>array(9,12,18,21),	//更新时间
			'type'=>array(6,1,3,3,3,3)	//发放物品类型与个数
		),  	           				
		2=>array(				//高级vip商店 神密商店
			'time'=>array(21),
			'type'=>array(1,1,1,6,6,3,3,3,3,3,3,3),
			'save'=>'3600',			//商店保存时间
			'vip'=>10			//该商店的vip要求
		),	        				
		3=>array(				//竞技场商店
			'time'=>array(21),
			'type'=>array(1,1,1,6,6,3,3,3,3,3,3,3)
		),              				
		4=>array(				//远征币  燃烧远征商店
			'time'=>array(21),
			'type'=>array(1,1,1,6,6,3,3,3,3,3,3,3)
		),             				
	);

	 private $table='zy_baseShopConfig';	//商店配置表

	 private $actRedis;					//活动指向的redis

	 private $type;						//指定商品类型(Shop_Id) 0为所有商店 1为普通商店 

	 private $shopinfo;					//指定类型的商品列表

	 private $isRef=0;					//是否刷新

	 private $nextTime;					//下次刷新时间

	 private $overTime;					//商店下一次刷新或消失的时间  用于倒计时

	 private $tolRate;					//统计概率区域总和

	
	function __construct( $uid,$type=1,$isRef=0 ){
		parent::__construct($uid);
		$this->log->i('~~~~~~~~~~~~~~~~~~  '.__CLASS__.' ~~~~~~~~~~~~~~~~~~');
		$this->type = (int)$type;
		$this->isRef = $isRef;
		
		$now = date('H');
		if( !isset( $this->shopConfig[$this->type] ) ){
			ret('Type_Error! Code:'.__LINE__);
		}
		$uLevel = $this->getVlevel();
		foreach( $this->shopConfig[$this->type]['time'] as $v ){
			if( isset( $this->shopConfig[$this->type]['vip'] ) && $uLevel < $this->shopConfig[$this->type]['vip'] ){
				$this->overTime = $this->shopConfig[$this->type]['save'];
				$this->nextTime = date( 'H:i',time() + $this->shopConfig[$this->type]['save'] ).' 消失';
			}else{
				if( $now < $v ){
					$this->overTime = mktime( $v,0,0 ) - time();
					$this->nextTime = '下次刷新时间：'.$v.'点';
					break;
				}
			}
		}
		if( empty( $this->overTime ) ){
			$this->overTime = mktime( $this->shopConfig[$this->type]['time'][0],0,0 )+86400 - time();
			$this->nextTime = '下次刷新时间：明日'.$this->shopConfig[$this->type]['time'][0].'点';
		}
		$this->_init();
		$this->actRedis = new Cond( 'userShop_'.$this->type,$this->uid,$this->overTime-10 );
	}
/**
 *@ 商店配置初始化
 **/
	private function _init(){
		$this->pre;
		if( C('test') || !$this->pre->exists('shopConfig:'.$this->type.':check') ){
			$where['Shop_Id'] = $this->type;
			$this->cdb;
			$ret = $this->cdb->find( $this->table, '*' , $where );
			if( empty( $ret ) ){
				ret('Config_Error! Code:'.__LINE__);
			}
			foreach( $ret as $v ){
				$this->pre->del('shopConfig:'.$v['Shop_Id'].':'.$v['Item_Type'].':'.$v['id']);
				$this->pre->hmset( 'shopConfig:'.$v['Shop_Id'].':'.$v['Item_Type'].':'.$v['id'],$v );
			}
			$this->pre->hset('shopConfig:'.$this->type.':check','checked',1,get3time());
		}
		$keys = $this->pre->keys('shopConfig:'.$this->type.':*');
		$uLevel = $this->getLevel();
		foreach( $keys as $v ){
			$shopInfo = $this->pre->hgetall( $v );
			$nLevel = explode( ',', $shopInfo['Group_Level'] );
			if( $uLevel >= $nLevel[0] && $uLevel <= $nLevel[1] ){
				$this->shopinfo[ $shopInfo['Item_Type'] ][] = $shopInfo;
				$this->tolRate += (int)$shopInfo['Item_Random'];
			}
		}

	}
/**
 *@ getShopGoods() 随机抽取指定数量的商品
 **/
	function getShopGoods(){
		$ret;
		if( empty( $this->isRef ) ){//如果没有主动刷新 取缓存数据
			$ret = $this->actRedis->get();//缓存的商店数据
		}
		
		if( empty( $ret ) || !is_array( $ret ) ){
			if( isset( $this->shopConfig[$this->type]['vip'] ) && $this->getVlevel() < $this->shopConfig[$this->type]['vip'] && empty( $this->isRef ) ){
				ret( ' 商店已消失 ',-1 );
			}else{
				$ret = $this->getTypeItems();
			}
		}
		$ret['overTime'] = $this->overTime;
		return $ret;
	}
	/**
	 *@ getTypeItems 执行具体的商店物品抽取
	 **/
	public function getTypeItems(){
		$userLevel = $this->getLevel();
		$retList = $oList = array();
		$i=0;
		if( !isset( $this->shopConfig[ $this->type ] ) ){
			$this->log->e('* 客户端传送type值（'.$this->type.'）错误，类:'.__CLASS__);
			ret('Config_Error! Code:'.__LINE__,-1);
		}
		foreach( $this->shopConfig[ $this->type ]['type'] as $val ){
			$list = $oList = array();
			if( !isset( $this->shopinfo[ $val ] ) ){
				$this->log->e('* 配置（'.json_encode($this->shopConfig[$this->type]['type']).'）错误，无商品类型（'.$val.'），类:'.__CLASS__);
				ret('Config_Error! Code:'.__LINE__,-1);
			}
			if( !is_array( $this->shopinfo[ $val ] ) ){
				$this->log->e('* 无指定类型的商品配置（'.$val.'）,类:'.__CLASS__);
				ret('Config_Error! Code:'.__LINE__,-1);
			}
			dump($this->tolRate);
			foreach( $this->shopinfo[ $val ] as $k=>$v ){
				$list[$k] = number_format( $v['Item_Random']/$this->tolRate, 4 );
			}
			dump($list);
			$index = $this->retRate($list);
			dump($index);
			$temp = $this->shopinfo[ $val ][$index];

			if( 6 == $temp['Item_Type'] ){
				$nums = 5;
			}elseif( 1 == $temp['Item_Type'] ){
				$nums = 1;
			}else{
				$numArr = array(3=>0.1,2=>0.2,1=>0.7);
				$nums = $this->retRate($numArr);
			}
			if( empty( $temp ) ){
				$this->log->e( 'shopinfo: '.json_encode($this->shopinfo[ $val ]) );
				$this->log->e( 'index: '.$index );
				$this->log->e( 'list: '.json_encode($list) );
				$this->log->e( 'oList: '.json_encode($oList) );
			}
			$retList['list'][$i]['gid'] = (int)$temp['Item_Id'];
			$retList['list'][$i]['name'] = $temp['Item_Name'];
			$retList['list'][$i]['type'] = (int)$temp['Currency_Type'];
			$retList['list'][$i]['price'] = (int)$temp['Item__Price']*$nums;
			$retList['list'][$i]['status'] = 1; //标记可出售
			$retList['list'][$i++]['nums'] = $nums;
			unset($list);
			unset($oList);
			unset($index);
			unset($temp);
		}
		shuffle($retList['list']);
		$retList['overTime'] = $this->overTime;
		$retList['time'] = $this->nextTime;
		$retList['out'] = $this->out;
		$this->actRedis->set( $retList );
		return $retList;
	}

/**
 *@ 购买
 **/
	public function getItemInfo( $index ){
		$shopInfo = $this->actRedis->get();
		if( empty( $shopInfo ) ){
			return 0;
		}
		return $shopInfo['list'][$index];
	}
/**
 *@ 设置商店的物品的出售标记
 **/
	public function setItemStatus( $index,$val=0 ){
		$shopInfo = $this->actRedis->get();
		$shopInfo['list'][$index]['status'] = $val;
		return $this->actRedis->set( $shopInfo );
	}
 }
?>
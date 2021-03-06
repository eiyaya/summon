<?php
/**
 *@ 装备基础类
 **/
 class Equipbase extends Pbase{
	private $table='zy_baseEquip';	//基础装备表名
	private $eid;						//装备id
	private $eInfo;						//装备信息

	function __construct($eid=''){
		parent::__construct();
		$this->eid = $eid;
		$this->_init();
	}

	private function _init(){
		if( C('test') || !$this->pre->exists('equip:baseinfo_check') ){
			$this->cdb;$this->preMaster;$this->pre=$this->preMaster;
			$ret = $this->cdb->find($this->table);
			foreach( $ret as $v ){
				$this->preMaster->hmset( 'equip:baseinfo:'.$v['Equip_Id'],$v );
			}
			$this->preMaster->set('equip:baseinfo_check',1);
			$this->preMaster->expireat( 'equip:baseinfo_check',( mktime(23,59,59)+1800 ) );
		}

		if( !empty($this->eid) ){
			$this->eInfo = $this->pre->hgetall( 'equip:baseinfo:'.$this->eid );
			if( empty( $this->eInfo ) ){
				ret('no_eid_config',-1);
			}
		}
	}
/**
 *@ 装备需要的英雄最低等级
 **/
	function getEquipMinLevel(){
		return (int)$this->eInfo['Hero_Level'];
	}
/**
 *@ 装备的最大强化次数
 **/
	function getEquipMax(){
		return (int)$this->eInfo['Equip_Color'];
	}
/**
 *@ 计算装备战斗力
 *	装备战斗力 =（装备物理攻击 + 装备法术强度 + 装备物理护甲 + 装备魔法抗性）+
 *（装备生命值 +装备 生命回复 * 2 + 装备法力值 + 装备法力回复 * 2 + 装备移动速度）/ 10 +
 *（|装备攻击速度| + |装备普攻暴击| + |装备技能暴击| +  |装备普攻命中| + |装备技能命中| + |装备闪避| + 
 *	|装备护甲穿透| + |装备法术穿透| + |装备物理吸血| + |装备法术吸血| + |装备技能冷却|）* 100
 **/
	function getFire( $level=0 ){
		$att 	= $this->eInfo['Equip_Att'] 	+ $this->eInfo['Equip_UpAtt'] * $level;									#物理攻击
		$sor 	= $this->eInfo['Equip_Sor'] 	+ $this->eInfo['Equip_UpSor'] * $level;									#法术强度
		$def 	= $this->eInfo['Equip_Def'] 	+ $this->eInfo['Equip_UpDef'] * $level;									#物理护甲
		$res 	= $this->eInfo['Equip_Res'] 	+ $this->eInfo['Equip_UpRes'] * $level;									#魔法抗性

		$ehp 	= $this->eInfo['Equip_Hp'] 		+ $this->eInfo['Equip_UpHp'] 	* $level;								#生命值
		$gethp 	= $this->eInfo['Equip_GetHp'] 	+ $this->eInfo['Equip_UpGetHp'] * $level;								#生命回复		
		$emp 	= $this->eInfo['Equip_Mp'] 		+ $this->eInfo['Equip_UpMp'] 	* $level;								#法力值
		$getmp 	= $this->eInfo['Equip_GetMp'] 	+ $this->eInfo['Equip_UpGetMp'] * $level;								#法力回复
		$speed 	= $this->eInfo['Equip_Mov'] 	+ $this->eInfo['Equip_UpMov'] 	* $level;								#移动速度

		$AttSpd = $this->eInfo['Equip_AttSpd'] + $this->eInfo['Equip_UpAttSpd'] * $level;						#攻击速度
		$AttCri = $this->eInfo['Equip_AttCri'] + $this->eInfo['Equip_UpAttCri'] * $level;						#物理爆机
		$SorCri = $this->eInfo['Equip_SorCri'] + $this->eInfo['Equip_UpSorCri'] * $level;						#法术爆机
		$AttHit = $this->eInfo['Equip_AttHit'] + $this->eInfo['Equip_UpAttHit'] * $level;						#物理命中
		$SkiHit = $this->eInfo['Equip_SkiHit'] + $this->eInfo['Equip_UpSkiHit'] * $level;						#法术命中
		$pry 	= $this->eInfo['Equip_Pry']    + $this->eInfo['Equip_UpPry'] 	  * $level;						#装备闪避
		$AttPierce = $this->eInfo['Equip_AttPierce'] + $this->eInfo['Equip_UpAttPierce'] 	* $level;			#装备护甲穿透
		$SorPierce = $this->eInfo['Equip_SorPierce'] + $this->eInfo['Equip_UpSorPierce'] 	* $level;			#装备法术穿透
		$AttSteal = $this->eInfo['Equip_AttSteal'] + $this->eInfo['Equip_UpAttSteal'] 	* $level;			#物理吸血
		$SorSteal = $this->eInfo['Equip_SorSteal'] + $this->eInfo['Equip_UpSorSteal'] 	* $level;			#法术吸血
		$CoolDown = $this->eInfo['Equip_CoolDown'] + $this->eInfo['Equip_UpCoolDown'] 	* $level;			#技能闪却

		$ret = ( $att + $sor + $def + $res ) + ( $ehp + $gethp*2 + $emp + $getmp*2 + $speed ) / 10 + 100*( abs($AttSpd) + abs($AttCri) + abs($SorCri) + abs($AttHit) + abs($SkiHit) + abs($pry) + abs($AttPierce) + abs($SorPierce) + abs($AttSteal) + abs($SorSteal) + abs($CoolDown) );
		#$this->log->i( 'equip_FIRE:'.$ret );
		return ceil($ret);
	}
 }
?>
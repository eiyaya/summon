<?php
/**
 *@ 聊天系统
 **/
 $user = new User_User();
 $type = isset( $input['t'] ) ? $input['t'] : '';

 if( empty( $type ) ){
 	ret( 'YMD', -1 );
 }

 switch( $type ){
 	case '1': #拉取信息
 		$tag = '拉取世界信息';
 		$lasttime = isset( $input['lt'] ) ? $input['lt'] : (time()-600);
 		$chat = new Chat( $user->getUid() );
 		ret( $chat->getChat( $lasttime ));
 	case '2': #发送信息
 		$tag = '发布世界信息';
 		$to = !empty( $input['to'] ) ? $input['to'] : '';
 		$ct = !empty( $input['ct'] ) ? $input['ct'] : '';
 		$con = $input['con'];
 		$other = $input['other'];  #pvp|key 
 		$strLen = abslength($con);
 		if( $strLen > 65 && $ct != 3 ){
 			ret( '内容不能超过65个汉字'.$strLen, -1 );
 		}
 		if( $ct == 3 ){#聊天公告
 			$endTime = $input[ 'et' ];		#公告结束日期
 			$times = strtotime($endTime) - time();
 			$con = $input['con'];
 			$t = $input['t'];
 			$chat = new Chat( ADMIN_UID,1,$times );
 			$chat->sendChat( $con );
 			ret($con);
 		}elseif( empty( $to ) ){
 			$gag = new Cond( 'user_limit', $user->getUid() );
 			$log->e($gag->getTimes('gag'));
 			$gagTime = $gag->getTimes('gag');
 			if( $gagTime > 0 ){
 				ret('您已被禁言,'.ceil($gagTime/60).'分钟后自动解除',-1);
 			}
	 		$limit = new User_Limit( $user->getUid(),'helloWorld' );
	 		$money = $limit->getOneTimeCooldou();
	 		if( $user->getMoney() >= $money || $ct == 3 ){
	 			$limit->addLimitTimes();
	 			$chat = new Chat( $user->getUid() );
	 			$chat->sendChat( $con, $user->getUserName(), $user->getUid(), $user->getLevel(), $user->getImage(),$other );
	 			if( $money > 0 ){
	 				$give['money'] = -$money;
	 				$ret = $user->sendGoodsFromConfig( $give );
	 			}
	 			ret( array( 'money'=>$user->getMoney() ) );
	 		}
	 		ret( '喊话需要 '.$money.' 金币' );	
	 	}else{
	 		$chat = new Chat( $to,2 );
	 		$chat->sendChat( $con, $user->getUserName(), $user->getUid(), $user->getLevel(), $user->getImage(),$other );
	 		ret('发送成功');
	 	}
	 case '3': #
	 	$tag = '玩家禁言';
	 	$gag = new Cond( 'user_limit', $user->getUid(), 600 );
	 	$gag->set( 1,'gag' );
	 	$ret['v'] = $gag->get('gag');
	 	$ret['t'] = $gag->getTimes('gag');
	 	ret($ret);
 }

 ret( 'YMD'.__LINE__, -1 );
?>
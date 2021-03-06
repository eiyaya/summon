<?php
/**
 *@ 充值发货接口   uid|money|tag|isMonth    用户uid | 充值金额 | 充值渠道标签 | 是否充值月卡
 **/
 $info = $input['pay'];
 
 if( empty( $info ) ){
 	$log->e( '充值请求数据有误，收到的信息配置如下:'.json_encode($input) );
 	ret('400',-1);
 }

 $uInfo = explode( '|',$info );

 $user = new User_User( $uInfo[0], -1 );
 
 $tag = $uInfo[2];

 if( isset( $uInfo[3] ) && $uInfo[3] == 1 ){
 	$user->setMonthCode();
 	$user->addCooldou( 300 );
 }elseif( isset( $uInfo[3] ) && $uInfo[3] == 2 ){
 	$user->setWeekCode();
 	$user->addCooldou( 800 );
 }else{
 	$user->addCooldou( $uInfo[1]*20 );
 }
 $user->addTotalPay( $uInfo[1] );
 ret('suc');
?>
<?php
/*
 获取消息成功接口
{init:1,nid:100,pic:"md5值",name:"Hito",date:"2010年1月1日 12:00:00",detail:"你好"}
获取消息失败接口
{init:0}
接受消息成功接口
{init:1}
接受消息失败接口
{init:0}
 */
if(empty($_POST)) exit();

$action=$_POST['action'];

//初始化memcache
$memcache = new Memcache;
$memcache->connect('localhost', 11211);

//客户端获取消息
if($action=="get"){
	//要获取的消息ID
	if(!$memcache->get('cid')){
		$memcache->set('cid',0,false,0);
	}
	$id=$_POST["id"]==0?$memcache->get('cid')-1:$_POST['id'];
	//获取消息
	$msg=$memcache->get('msg'.$id);
	//对消息进行处理
	//消息格式pic=''&name='''&date=时间戳&detail=nihao
	if(!empty($msg)){
		//获取成功
		preg_match_all("/([^&]*?)=([^&$]+)/i",$msg,$m);
		$key=$m[1];
		$name=$m[2];
		$c=array_combine($key,$name);
		$nid=$id+1;
		$status=array('init'=>1,'nid'=>$memcache->get('cid'));
		echo json_encode(array_merge($status,$c));
	}else {
		//获取失败
		echo "{init:0}";
	}
}else if($action=="send"){
	$name=htmlspecialchars($_POST['chatname']);
	$email=md5($_POST['chatemail']);
	$msg=htmlspecialchars($_POST['chatmsg']);
	$date=time();

	$ar='pic='.$email.'&name='.$name.'&date='.$date.'&detail='.$msg;

	//获取要存储的id
	if(!$memcache->get('cid')){
		$memcache->set('cid',0,false,0);
		$id=0;
	}else{
		$id=$memcache->get('cid');
	}
	//存储信息
	if($memcache->add('msg'.$id,$ar,false,0)){
		$memcache->increment('cid',1);
		echo "{init:1}";
	}else{
		echo "{init:0}";
	}
}

$memcache->close();
?>

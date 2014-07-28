<?php

class CartAction extends Action {
	
	public function index() {

		//$this->display();
	}

	public function add() {

		$id = I('id', 0, 'intval');//产品id
		$num = I('num', 1, 'intval');//数量
		if (!$id) {
			$this->error('请选择要购买的产品');
		}

		if(!isset($_SESSION['USER_AUTH_KEY'])){
			$cartinfo = array('customer'=>'',
						'email' => '',
						'tel'=>'',
						'address'=>'',
						'group'=>'0',
						'uid'=>'0',
						'productinfo'=>array()
					);
		}else{
			$user = M('member')->find($_SESSION['USER_AUTH_KEY']);		
			$cartinfo = array('customer'=> $user['nickname'],
						'email'=> $user['email'],
						'tel'=> $user['tel'],
						'address'=> $user['address'],
						'group' => 'groupid',
						'uid' => $user['id'],
						'productinfo'=>array()
					);
		}
		//del_cookie(array('name' => 'yang_cart'));exit();//清空删除购物车
		$old_cartinfo = get_cookie('yang_cart');

		
		if(empty($old_cartinfo)){//cookie为空		
		
		}else{
		    		
			if($cartinfo['user_id']==0){//未登录前下的订单，更新到新登录的cookie
				$cartinfo['productinfo']=$old_cartinfo['productinfo'];
			}else{
				$cartinfo = $old_cartinfo;	
			}
		}
	
		if(empty($cartinfo['productinfo'][$id])){//cookie无此数据
			$rs = M('product')->find($id);
			$cartinfo['productinfo'][$rs['id']]=array(
				'id' => $rs['id'], 
				'catid'=> $rs['cid'],
				'title'=> $rs['title'], 
				'litpic'=> $rs['litpic'], 
				'num'=>1, 
				'price'=>$rs['price']
				);
		}else{
			$cartinfo['productinfo'][$id]['num']+=1;
		}
		
		
		$args = array(
				'name' => 'yang_cart',				
				'value' => $cartinfo
			);
		set_cookie($args);//保存cookie
		
		//p($cartinfo);exit();
		redirect(U(GROUP_NAME .'/Cart/show'));	
		//redirect("?p=".$request['p']."&a=basket");


	}

	public function show() {

		$cartinfo=get_cookie('yang_cart');
		if(isset($_SESSION['USER_AUTH_KEY']) && $basket['user_id']==0){//用户登录后,购物cookie user_ID为0时，更新到新登录的cookie

			
			$user = M('member')->find($_SESSION['USER_AUTH_KEY']);		
			$cartinfo['customer'] = $user['nickname'];
			$cartinfo['email'] = $user['email'];
			$cartinfo['tel'] = $user['tel'];
			$cartinfo['address'] = $user['address'];
			$cartinfo['group'] = 'groupid';
			$cartinfo['uid'] = $user['id'];
	

			set_cookie(array('name' => 'yang_cart', 'value' =>$cartinfo ));

		}

		//echo "成功放到购物车";
		$this->title = '我的购物车';
		$this->cartinfo = $cartinfo;
		$this->flag = empty($cartinfo['productinfo'])? 0 : 1;
		$this->display();
	}




}

?>
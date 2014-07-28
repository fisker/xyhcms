<?php

class PublicAction extends Action {
	
	public function index() {

	}

	public function login() {
		$furl = $_SERVER['HTTP_REFERER'];
		if (IS_POST) {
			$this->loginPost();
			exit();
		}
		$this->furl = $furl;
		$this->title = '用户登录';
		$this->display();
	}


	public function loginPost() {

		if (!IS_POST) exit();

		$furl = I('furl', '','htmlspecialchars,trim');
		if (empty($furl) || strpos($furl, 'register') || strpos($furl, 'login') || strpos($furl, 'logout') || strpos($furl, 'activate') || strpos($furl, 'sendActivate')) {
			$furl = U(GROUP_NAME. '/Member/index');
	
		}

		$email = I('email','','htmlspecialchars,trim');
		$password = I('password','');
		//$verify = I('code','','md5');
	
		/*
		if ($_SESSION['verify'] != $verify) {
			$this->error('验证码不正确');
		}
		*/

		if ($email == '') {
			$this->error('请输入帐号！', '', array('input'=>'email'));//支持ajax,$this->error(info,url,array);
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->error('账号为邮箱地址，格式不正确！', '', array('input'=>'email'));//支持ajax,$this->error(info,url,array);
		}

		if (strlen($password)<4 || strlen($password)>20) {
			$this->error('密码必须是4-20位的字符！', '', array('input'=>'password'));
		}

	
		$user = M('member')->where(array('email' => $email))->find();

		if (!$user || ($user['password'] != get_password($password, $user['encrypt']))) {
			$this->error('账号或密码错误', '', array('input'=>'password'));
		}

		if ($user['islock']) {
			$this->error('用户被锁定！', '', array('input'=>''));
		}
		//更新数据库的参数
		$data = array('id' => $user['id'] ,//保存时会自动为此ID的更新
				'logintime' => time(),
				'loginip' => get_client_ip(),
				'loginnum' => $user['loginnum']+1,

		);
		//更新数据库
		M('member')->save($data);

		//保存Session
		//session(C('USER_AUTH_KEY'), $user['id']);
		//保存到cookie
		set_cookie( array('name' => 'uid', 'value' => $user['id'] ));
		set_cookie( array('name' => 'email', 'value' => $user['email'] ));
		set_cookie( array('name' => 'nickname', 'value' => $user['nickname'] ));
		set_cookie( array('name' => 'logintime', 'value' => date('Y-m-d H:i:s', $user['logintime'])));
		set_cookie( array('name' => 'loginip', 'value' => $user['loginip']));
		set_cookie( array('name' => 'status', 'value' => $user['status']));//激活状态
		set_cookie( array('name' => 'verifytime', 'value' => time()));//激活状态


		//跳转
		//$this->redirect(GROUP_NAME.'/Member/index');
		//redirect(__GROUP__);
		$this->success('登录成功', $furl , array('input'=>''));
	}

		//退出
	public function logout() {

		$furl = $_SERVER['HTTP_REFERER'];
	
		if (empty($furl) || strpos($furl, 'register') || strpos($furl, 'login') || strpos($furl, 'activate') || strpos($furl, 'sendActivate')) {
			$furl = U(GROUP_NAME. '/Public/login');
	
		}

		//session_unset();
		//session_destroy();


		del_cookie(array('name' => 'uid'));
		del_cookie(array('name' => 'email'));
		del_cookie(array('name' => 'nickname'));
		del_cookie(array('name' => 'logintime'));
		del_cookie(array('name' => 'loginip'));
		del_cookie(array('name' => 'status'));


		//$this->redirect(GROUP_NAME.'/Public/login');
		$this->success('安全退出', $furl);
	}



		//自动登录后，js验证，更新积分
	public function loginChk() {		
	}


	//注册
	public function register() {

	}


	//增加点击数
	public function click(){	
		$id = I('id', 0, 'intval');
		$tablename = I('tn', '');
		if (C('HTML_CACHE_ON') == true) {
			echo 'document.write('. getClick($id, $tablename) .')';
		}
		else {
			echo getClick($id, $tablename);
		}
		
	}


	//证码码
	public function verify(){	
		import('ORG.Util.Image');//导入验证码Image类库
		return Image::buildImageVerify(4, 1);
	}



}



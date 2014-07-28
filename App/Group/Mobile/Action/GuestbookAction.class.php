<?php

class GuestbookAction extends Action {
	
	public function index() {
					
		//分页
		import('Class.Page', APP_PATH);
		$count = M('guestbook')->count();

		$page = new Page($count, 10);
		$page->rollPage = 5;
		$page->setConfig('theme','%upPage% %linkPage% %downPage% 共%totalPage%页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = M('guestbook')->order('id DESC')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;

		$this->title = '留言本';
		$this->display();
	}
	//添加

	public function add() {

		if (!IS_POST) {
			exit();
		}
		$content = I('content', '');
		$data =  I('post.');		
		$verify = I('vcode','','md5');
		if (C('cfg_verify_guestbook') == 1 && $_SESSION['verify'] != $verify) {
			$this->error('验证码不正确');
		}

		if (empty($data['username'])) {
			$this->error('姓名不能为空!');
		}
		if (empty($data['content'])) {
			$this->error('留言内容不能为空!');
		}
		if (checkBadWord($content)) {
			$this->error('留言内容包含非法信息，请认真填写!');
		}

	
		

		$data['posttime'] = time();
		$data['ip'] = get_client_ip();
	
		$db = M('guestbook');

		if($id = $db->add($data)) {
			$this->success('添加成功',U(GROUP_NAME. '/Guestbook/index'));
		}else {
			$this->error('添加失败');
		}
	}

	

}



?>
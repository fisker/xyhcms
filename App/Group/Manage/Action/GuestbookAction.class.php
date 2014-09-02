<?php

class GuestbookAction extends CommonAction {
	
	public function index() {
					
		//分页
		import('Class.Page', APP_PATH);
		$count = M('guestbook')->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = M('guestbook')->order('id DESC')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;
		$this->type = '留言本管理';

		$this->display();
	}
	//添加
	public function add() {
		//当前控制器名称		
		$actionName = strtolower($this->getActionName());		
		if (IS_POST) {
			$this->addPost();
			exit();
		}
		$this->display();
	}

	//
	public function addPost() {

		//M验证
		$validate = array(
			array('username','require','姓名不能为空！'), 
			array('content','require','留言内容不能为空！'), 
		);

		$auto = array ( 
			array('posttime','time',1,'function'), 
			array('ip','get_client_ip',1,'function') ,
		);

		$db = M('guestbook');
		if (!$db->auto($auto)->validate($validate)->create()) {
			$this->error($db->getError());
		}


		if($id = $db->add()) {
			$this->success('添加成功',U(GROUP_NAME. '/Guestbook/index'));
		}else {
			$this->error('添加失败');
		}
	}

	//回复
	public function reply() {
		//当前控制器名称
		$id = I('id', 0, 'intval');
		$actionName = strtolower($this->getActionName());
		if (IS_POST) {
			$this->replyPost();
			exit();
		}

		$vo = M($actionName)->find($id);
		$vo['reply'] = htmlspecialchars($vo['reply']);
		$this->vo = $vo;
		$this->display();
	}


	//回复处理
	public function replyPost() {

		$id = I('id', '', 'intval');
		$reply = I('reply', '', 'trim');
		$pic = I('logo', '', 'trim');
		if (!$id) {
			$this->error('参数错误');
		}
		$data = array(
			'id' => $id,
			'reply' => $reply,
			'replytime' => time()
		);
		

		if (false !== M('guestbook')->save($data)) {
			$this->success('修改成功', U(GROUP_NAME. '/Guestbook/index'));
		}else {

			$this->error('修改失败');
		}
		
	}



	//彻底删除文章
	public function del() {

		$id = I('id',0 , 'intval');
		$batchFlag = I('get.batchFlag', 0, 'intval');
		//批量删除
		if ($batchFlag) {
			$this->delBatch();
			return;
		}
		
		if (M('guestbook')->delete($id)) {
			$this->success('彻底删除成功', U(GROUP_NAME. '/Guestbook/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}


	//批量彻底删除文章
	public function delBatch() {

		$idArr = I('key',0 , 'intval');		
		if (!is_array($idArr)) {
			$this->error('请选择要彻底删除的项');
		}
		$where = array('id' => array('in', $idArr));

		if (M('guestbook')->where($where)->delete()) {
			$this->success('彻底删除成功', U(GROUP_NAME. '/Guestbook/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}




}



?>
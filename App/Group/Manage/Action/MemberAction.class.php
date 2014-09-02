<?php

class MemberAction extends CommonAction {
	
	public function index() {
		
		$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字	
		$where = array('member.id' => array('gt', 0));
		if (!empty($keyword)) {
			if (strpos($keyword, '@')) {
				$where['member.email'] = $keyword;
			}else {
				$where['member.nickname'] = $keyword;
			}
		}
		//分页
		import('Class.Page', APP_PATH);
		$count = D('MemberView')->where($where)->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = D('MemberView')->nofield('password,encrypt')->where($where)->order('member.id desc')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;
		$this->type = '会员列表';
		$this->keyword = $keyword;

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
		$this->vlist = M('membergroup')->where(array('id'=> array('gt',1)))->select();
		$this->display();
	}

	//
	public function addPost() {
		$password = I('password', '');
		//M验证
		$validate = array(
			array('email','email','邮箱格式不正确'), // 内置正则验证邮箱
			array('groupid','require','请选择会员组！'), 
			array('password','require','密码必须填写！'), 
			array('email','','邮箱已经存在！',0,'unique',1), //使用这个是否存在，auto就不能自动完成
		);


		$db = M('member');
		if (!$db->validate($validate)->create()) {
			$this->error($db->getError());
		}


		$data = I('post.');
		$passwordinfo = I('password', '','get_password');
		$data['regtime'] = time();
		$data['password'] = $passwordinfo['password'];
		$data['encrypt'] = $passwordinfo['encrypt'];


		if($id = $db->add($data)) {
			$this->success('添加成功',U(GROUP_NAME. '/Member/index'));
		}else {
			$this->error('添加失败');
		}
	}

	//编辑
	public function edit() {
		//当前控制器名称
		$id = I('id', 0, 'intval');
		$actionName = strtolower($this->getActionName());

		if (IS_POST) {
			$this->editPost();
			exit();
		}
		
		$this->vlist = M('membergroup')->where(array('id'=> array('gt',1)))->select();
		$this->vo = M($actionName)->find($id);
		$this->display();
	}


	//修改文章处理
	public function editPost() {

		$data = I('post.');
		$id = $data['id'] = I('id', 0, 'intval');
		$data['email'] = trim($data['email']);

		if (empty($data['email'])) {
			$this->error('电子邮箱必须填写！');
		}
		if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
				$this->error('电子邮箱格式不正确！');
		}
		
		if (M('member')->where(array('email' => $data['email'], 'id' => array('neq', $id)))->find()) {
			$this->error('失败，邮箱已经存在！');
		}

		$data['groupid'] = I('groupid', 0, 'intval');
		$data['islock'] = I('islock', 0, 'intval');
		
		if(!empty($data['password'])) {
			$passwordinfo = I('password', '','get_password');
			$data['password'] = $passwordinfo['password'];
			$data['encrypt'] = $passwordinfo['encrypt'];
		}else {
			unset($data['password']);//删除密码，防止被添加
		}
	

		if (false !== M('member')->save($data)) {
			$this->success('修改成功', U(GROUP_NAME. '/Member/index'));
		}else {

			$this->error('修改失败');
		}
		
	}



	//用户资料
	public function person() {
		//当前控制器名称
		$id = I('id', 0, 'intval');
		$actionName = strtolower($this->getActionName());
		if (empty($id)) {
			$this->error('参数错误');
		}

		if (IS_POST) {
			$data['realname'] = I('realname', '', 'htmlspecialchars,trim');
			$data['birthday'] = I('birthday', '0000-00-00');
			$data['sex'] = I('sex', 0, 'intval');			
			$data['address'] = I('address', '');
			$data['tel'] = I('tel', '');
			$data['mobile'] = I('mobile', '');
			$data['qq'] = I('qq', '');
			$data['maxim'] = I('maxim', '');

			$data['userid'] = $id;
			$data['updatetime'] = time();
			$new = I('new', 0,'intval');
			if (empty($data['realname'])) {
				$this->error('请输入姓名！');
			}
			
			$result = true;
			if ($new) {
				$result = M('memberdetail')->add($data);
			}else {
				$result = M('memberdetail')->save($data);
			}
			
			if (false !== $result) {
				$this->success('修改用户资料成功', U(GROUP_NAME. '/Member/index'));
			}else {

				$this->error('修改用户资料失败');
			}
			exit();
		}
		
		$userdetail = M('memberdetail')->where(array('userid' => $id))->find();
		if (!$userdetail) {
			$userdetail = array(
				'userid' => $id,
				'realname' => '',
				'sex' => 0,
				'birthday' => '1990-1-1',
				'address'	=> '',
				'tel'	=> '',
				'mobile'	=> '',
				'qq'	=> '',
				'maxim' => '',
			);
			$userdetail['new'] = 1;
		}else {
			$userdetail['new'] = 0;		
		}
		$this->vo = $userdetail;	
		$this->type = '用户基本资料';	
		$this->display();
	}



	//彻底删除
	public function del() {

		$id = I('id',0 , 'intval');
		$batchFlag = I('get.batchFlag', 0, 'intval');
		//批量删除
		if ($batchFlag) {
			$this->delBatch();
			return;
		}
		
		if (M('member')->delete($id)) {			
			M('memberdetail')->delete($id);
			$this->success('彻底删除成功', U(GROUP_NAME. '/Member/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}


	//批量彻底删除
	public function delBatch() {

		$idArr = I('key',0 , 'intval');		
		if (!is_array($idArr)) {
			$this->error('请选择要彻底删除的项');
		}
		$where = array('id' => array('in', $idArr));

		if (M('member')->where($where)->delete()) {			
			M('memberdetail')->where(array('userid' => array('in', $idArr)))->delete();
			$this->success('彻底删除成功', U(GROUP_NAME. '/Member/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}




}



?>
<?php

class MenuAction extends CommonAction {
	
	//列表
	public function index() {

		$cate = M('menu')->order('sort,id')->select();
		if (empty($cate)) {
			$cate = array();
		}
		import('Class.Category', APP_PATH);
		$this->cate = Category::toLevel($cate, '&nbsp;&nbsp;&nbsp;&nbsp;', 0);
		$this->display();
	}

	//添加分类
	public function add() {
	
		if (IS_POST) {
			$this->addPost();
			exit();
		}
		$this->pid = I('pid', 0, 'intval');
		$cate = M('menu')->order('sort')->select();
		import('Class.Category', APP_PATH);
		$this->cate = Category::toLevel($cate, '---',0);
		$this->display();
	}

	//添加分类处理

	public function addPost() {

		$data = I('post.', '');

		
		$data['name'] = trim($data['name']);
		$data['pid'] = intval($data['pid']);
		$data['module'] = ucfirst($data['module']);
		$data['parameter'] = I('parameter', '', '');

	
		//M验证
		if (empty($data['name'])) {
			$this->error('菜单名称不能为空！');
		}



		if ($id = M('menu')->add($data)) {
			//管理员组权限
			
			$this->success('添加成功',U(GROUP_NAME. '/Menu/index'));
		}else {
			$this->error('添加失败');
		}
		
	}


	//修改分类
	public function edit() {

		if (IS_POST) {
			$this->editPost();
			exit();
		}
		$id = I('id', 0, 'intval');
		$data = M('menu')->find($id);
		if (!$data) {
			$this->error('记录不存在');
		}
		$this->data = $data;
		$cate = M('menu')->order('sort')->select();
		import('Class.Category', APP_PATH);
		$this->cate = Category::toLevel($cate, '---',0);
	
		$this->display();
	}



	//修改分类处理

	public function editPost() {

		$data = I('post.', '');		
		$id = $data['id'] = intval($data['id']);	
		
		$data['name'] = trim($data['name']);
		$pid = $data['pid'] = intval($data['pid']);
		$data['module'] = ucfirst($data['module']);
		$data['parameter'] = I('parameter', '', '');

	
		//M验证
		if (empty($data['name'])) {
			$this->error('菜单名称不能为空！');
		}

		if ($id == $pid) {
			$this->error('失败！不能设置自己为自己的子菜单，请重新选择上级菜单');
		}

		

		if (false !== M('menu')->save($data)) {

		
			$this->success('修改成功',U(GROUP_NAME. '/Menu/index'));
		}else {
			$this->error('修改失败');
		}
		
	}

	//批量更新排序
	public function sort() {
		$sortlist = I('sortlist', array(), 'intval');
	
		foreach ($sortlist as $k => $v) {
			$data = array(
					'id' => $k,
					'sort' => $v,
				);
			M('menu')->save($data);		
		}
		$this->redirect(GROUP_NAME. '/Menu/index');
	}


	//批量更新排序
	public function qk() {
		$quicklist = I('quicklist', array(), 'intval');

		M('menu')->where(array('id' => array('GT', 0)))->setField('quick',0);
		if (!empty($quicklist)) {			
			M('menu')->where(array('id' => array('IN', $quicklist) ))->setField('quick',1);
		}
	

		$this->redirect(GROUP_NAME. '/Menu/index');
	}

	//修改分类处理

	public function del() {

		$id = I('id', 0, 'intval');

		//查询是否有子类
		$childCate = M('menu')->where(array('pid' => $id))->select();
		if ($childCate) {
			$this->error('删除失败：请先删除本菜单下的子菜单');
		}
		if (M('menu')->delete($id)) {
		
			$this->success('删除成功',U(GROUP_NAME. '/Menu/index'));
		}else {
			$this->error('删除失败');
		}		
	}


}




?>
<?php

class LinkAction extends CommonAction {
	
	public function index() {
					
		//分页
		import('Class.Page', APP_PATH);
		$count = M('link')->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = M('link')->order('sort')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;
		$this->type = '友情连接列表';

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

		$name = I('name', '', 'trim');
		$url = I('url', '', 'trim');
		$pic = I('logo', '', 'trim');
		if (empty($name) || empty($url)) {
			$this->error('网站名称或网址不能为空');
		}

		$data = array(
			'name'		=> $name,
			'url'		=> $url,
			'logo'		=> $pic,
			'description' => I('description', ''),
			'ischeck'	=> I('ischeck', 0, 'intval'),
			'sort'		=> I('sort', 0, 'intval'),
			'posttime'	=> time(),

		);

		if($id = M('link')->add($data)) {
			//更新上传附件表
			if (!empty($pic)) {
				$attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
				if($attid){
					M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => 0, 'desc' => 'link'));
				}
			}
	

			$this->success('添加成功',U(GROUP_NAME. '/Link/index'));
		}else {
			$this->error('添加失败');
		}
	}

	//编辑文章
	public function edit() {
		//当前控制器名称
		$id = I('id', 0, 'intval');
		$actionName = strtolower($this->getActionName());
		if (IS_POST) {
			$this->editPost();
			exit();
		}
		$this->vo = M($actionName)->find($id);
		$this->display();
	}


	//修改文章处理
	public function editPost() {

		$name = I('name', '', 'trim');
		$url = I('url', '', 'trim');
		$pic = I('logo', '', 'trim');
		$id = I('id', 0, 'intval');
		if (empty($name) || empty($url)) {
			$this->error('网站名称或网址不能为空');
		}
		

		if (false !== M('link')->save($_POST)) {
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => 0, 'desc' => 'link'))->delete();
			//更新上传附件表
			if (!empty($pic)) {
				$attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
				if($attid){
					M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => 0, 'desc' => 'link'));
				}
			}


			$this->success('修改成功', U(GROUP_NAME. '/Link/index'));
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
		
		if (M('link')->delete($id)) {			
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => 0, 'desc' => 'link'))->delete();
			$this->success('彻底删除成功', U(GROUP_NAME. '/Link/index'));
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

		if (M('link')->where($where)->delete()) {
			M('attachmentindex')->where(array('arcid' => array('in', $idArr), 'modelid' => 0, 'desc' => 'link'))->delete();
			$this->success('彻底删除成功', U(GROUP_NAME. '/Link/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}




}



?>
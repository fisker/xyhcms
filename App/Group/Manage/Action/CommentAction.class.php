<?php

class CommentAction extends CommonAction {
	
	public function index() {
					
		//分页
		import('Class.Page', APP_PATH);
		$count = D('CommentView')->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = D('CommentView')->order('id DESC')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;
		$this->type = '评论管理';

		$this->display();
	}


	//编辑文章
	public function edit() {
		//当前控制器名称
		$id = I('id', 0, 'intval');
		$actionName = strtolower($this->getActionName());
		if (IS_POST) {
			$data = I('post.');
			$data['content'] = I('content', '', '');
			if (false !== M('comment')->save($data)) {
				$this->success('修改成功', U(GROUP_NAME. '/Comment/index'));
			}else {

				$this->error('修改失败');
			}
			exit();
		}

		$this->vo = M($actionName)->find($id);
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
		
		if (M('comment')->delete($id)) {
			$this->success('彻底删除成功', U(GROUP_NAME. '/Comment/index'));
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

		if (M('comment')->where($where)->delete()) {
			$this->success('彻底删除成功', U(GROUP_NAME. '/Comment/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}




}



?>
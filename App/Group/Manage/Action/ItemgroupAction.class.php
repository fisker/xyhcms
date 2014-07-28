<?php

class ItemgroupAction extends CommonAction {
	
	public function index() {
					
		//分页
		import('Class.Page', APP_PATH);
		$count = M('itemgroup')->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = M('itemgroup')->order('id')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;
		$this->type = '联动分组列表';

		$this->display();
	}
	//添加
	public function add() {
		//当前控制器名称		
		$actionName = strtolower($this->getActionName());
		if (IS_POST) {
			//M验证
			$validate = array(
				array('remark','require','组名必须填写！'), 
				array('name','require','英文组名必须填写！'), 
				array('name','','英文组名已经存在！',0,'unique',1), 
			);
			$db = M('itemgroup');
			if (!$db->validate($validate)->create()) {
				$this->error($db->getError());
			}
			if($id = M('itemgroup')->add()) {
				$this->success('添加成功',U(GROUP_NAME. '/Itemgroup/index'));
			}else {
				$this->error('添加失败');
			}
			exit();
		}
		$this->display();
	}



	//编辑文章
	public function edit() {
		//当前控制器名称
		$id = I('id', 0, 'intval');
		$actionName = strtolower($this->getActionName());
		if (IS_POST) {
			$name = I('name', '', 'htmlspecialchars,trim');
			$remark = I('remark', '', 'htmlspecialchars,trim');
			if (empty($remark)) {
				$this->error('组名必须填写！');
			}
			if (empty($name)) {
				$this->error('英文组名必须填写！');
			}
			
			if (M('itemgroup')->where(array('name' => $name, 'id' => array('neq', $id)))->find()) {
				$this->error('英文组名已经存在！');
			}

			if (false !== M('itemgroup')->save($_POST)) {
				$this->success('修改成功', U(GROUP_NAME. '/Itemgroup/index', array('pid' => $pid)));
			}else {

				$this->error('修改失败');
			}
			exit();
		}
		$this->vo = M($actionName)->find($id);
		$this->display();
	}




	//彻底删除文章
	public function del() {

		$id = I('id',0 , 'intval');
		$Model =M();		
		$batchFlag = intval($_GET['batchFlag']);
		//批量删除
		if ($batchFlag) {
			$this->delBatch();
			return;
		}

		//getField('id'),返回一个结果，getField('id',true),返回满足的所有(数组)
		$child= $Model->table(C('DB_PREFIX'). 'iteminfo I')->join('inner join '. C('DB_PREFIX').'itemgroup G on I.group = G.name')->where(array('G.id' => $id))->getField('I.id');
		if($child) {
			$this->error('请先删除分组下的联动信息，再删除分组');
		}

		if (M('itemgroup')->delete($id)) {
			$this->success('彻底删除成功', U(GROUP_NAME. '/Itemgroup/index'));
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

		//getField('id'),返回一个结果，getField('id',true),返回满足的所有(数组)
		$Model =M();
		$child= $Model->table(C('DB_PREFIX'). 'iteminfo I')->join('inner join '. C('DB_PREFIX').'itemgroup G on I.group = G.name')->where(array('G.id' => array('in', $idArr)))->getField('I.id');
		if($child) {
			$this->error('请先删除分组下的联动信息，再删除分组');
		}


		if (M('itemgroup')->where($where)->delete()) {
			$this->success('彻底删除成功', U(GROUP_NAME. '/Itemgroup/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}




}



?>
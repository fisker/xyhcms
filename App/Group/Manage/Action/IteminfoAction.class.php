<?php

class IteminfoAction extends CommonAction {
	
	public function index() {

		$group = I('group', '','trim');
		if (empty($group)) {
			$this->error('参数不正确!');
		}

		//分页
		import('Class.Page', APP_PATH);
		$count = M('iteminfo')->where(array('group' => $group))->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = M('iteminfo')->where(array('group' => $group))->order('sort')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;
		$this->group = $group;
		$this->type = '联动信息列表';

		$this->display();
	}
	//添加
	public function add() {
		//当前控制器名称		
		$actionName = strtolower($this->getActionName());
		$group = I('group', '','trim');

		if (IS_POST) {
			//M验证
			$data['name'] = I('name', '', 'trim');
			$data['value'] = I('value', 1, 'intval');
			$data['group'] = I('group', '', 'trim');
			$data['sort'] = I('sort',  0, 'intval');

			if (empty($data['name'])) {
				$this->error('名称不能为空');
			}
			if (empty($data['group'])) {
				$this->error('请选择分组！');
			}
			$vo = M('iteminfo')->where(array('group' => $data['group'], 'value' => $data['value']))->find();
			if ($vo) {
				$this->error('枚举值已经存在，请重新填写');
			}


			if($id = M('iteminfo')->add($data)) {
				$this->success('添加成功',U(GROUP_NAME. '/Iteminfo/index', array('group' => $data['group'])));
			}else {
				$this->error('添加失败');
			}
			exit();
		}


		$this->vlist = M('itemgroup')->select();
		$data = M('iteminfo')->where(array('group' => $group))->field('MAX(value) as maxV')->find();
	
		$this->maxValue = isset($data['maxV'])? $data['maxV'] + 1 : 1;
		$this->group = $group;
		$this->type = '添加联动信息';
		$this->display();
	}



	//编辑
	public function edit() {
		//当前控制器名称
		$id = I('id', 0, 'intval');
		$actionName = strtolower($this->getActionName());
		if (IS_POST) {
			//M验证
			$data['id'] = I('id',  0, 'intval');
			$data['name'] = I('name', '', 'trim');
			$data['value'] = I('value', 1, 'intval');
			$data['group'] = I('group', '', 'trim');			
			$data['sort'] = I('sort',  0, 'intval');

			if (empty($data['name'])) {
				$this->error('名称不能为空');
			}
			if (empty($data['group'])) {
				$this->error('请选择分组！');
			}
			$vo = M('iteminfo')->where(array('id' => array('neq', $data['id']), 'group' => $data['group'], 'value' => $data['value']))->find();
			if ($vo) {
				$this->error('枚举值已经存在，请重新填写');
			}


			if (false !== M('iteminfo')->save($data)) {
				$this->success('修改成功',U(GROUP_NAME. '/Iteminfo/index', array('group' => $data['group'])));
			}else {

				$this->error('修改失败');
			}
			exit();
		}
		$group = I('group', '','trim');
		$this->vlist = M('itemgroup')->select();
		$this->vo = M($actionName)->find($id);
		$this->group = $group;
		$this->type = '修改联动信息';
		$this->display();
	}



	//批量更新排序
	public function sort() {
		$group = $_GET['group'];
		//exit();
		foreach ($_POST as $k => $v) {
			if ($k == 'key') {
				continue;
			}
			M('iteminfo')->where(array('id'=>$k))->setField('sort',$v);
			//echo 'id:'.$k.'___v:'.$v.'<br/>';//debug
		}
		$this->redirect(GROUP_NAME. '/Iteminfo/index', array('group' => $group));
	}


	//彻底删除
	public function del() {

		$id = I('id',0 , 'intval');
		$batchFlag = intval($_GET['batchFlag']);
		//批量删除
		if ($batchFlag) {
			$this->delBatch();
			return;
		}
		
		$group = I('group', '', 'trim');
		if (M('iteminfo')->delete($id)) {
			$this->success('彻底删除成功', U(GROUP_NAME. '/Iteminfo/index' , array('group' => $group)));
		}else {
			$this->error('彻底删除失败');
		}
	}


	//批量彻底删除
	public function delBatch() {

		$idArr = I('key',0 , 'intval');	
		$group = $_GET['group'];
		if (!is_array($idArr)) {
			$this->error('请选择要彻底删除的项');
		}
		$where = array('id' => array('in', $idArr));

		if (M('iteminfo')->where($where)->delete()) {
			$this->success('彻底删除成功', U(GROUP_NAME. '/Iteminfo/index', array('group' => $group)));
		}else {
			$this->error('彻底删除失败');
		}
	}




}



?>
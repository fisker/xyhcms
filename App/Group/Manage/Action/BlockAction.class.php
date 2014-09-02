<?php

class BlockAction extends CommonAction {
	
	public function index() {
					
		//分页
		import('Class.Page', APP_PATH);
		$count = M('block')->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = M('block')->order('id desc')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;
		$this->type = '自由块列表';

		$this->display();
	}
	//添加
	public function add() {

		if (IS_POST) {
			$this->addPost();
			exit();
		}
		$this->type = '添加自由块';
		$this->blocktypelist = getArrayOfItem('blocktype');
		$this->display();
	}

	//
	public function addPost() {	
		//当前控制器名称		
		$actionName = strtolower($this->getActionName());

		$data['name'] = I('name', '', 'htmlspecialchars,trim');
		$data['blocktype'] = I('blocktype', 0, 'intval');
		$data['remark'] = I('remark', '');
		$content = I('content','','');


		if (empty($data['name'])) {
			$this->error('请填写名称');
		}

		if (empty($data['blocktype'])) {
			$this->error('请选择类型');
		}

		if (M('block')->where(array('name' => $data['name']))->find()) {
			$this->error('自由块名称已经存在!');
		}

		$data['content'] = $content[$data['blocktype']];



		if($id = M('block')->add($data)) {

			//更新缓存
			getBlock($data['name'], 1);

			//图片类型
			if ($data['blocktype'] == 2) {
				
				$pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $data['content']);//清除缩略图的!200X200.jpg后缀
				$attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
				if($attid){
					M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => 0, 'desc' => $actionName));
				}

			} elseif ($data['blocktype'] == 3) {
				//内容中的图片
				$img_arr = array();
				$reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
				preg_match_all($reg, $data['content'], $img_arr, PREG_PATTERN_ORDER);
				// 匹配出来的不重复图片
				$img_arr = array_unique($img_arr[1]);
				if (!empty($img_arr)) {
					if(!empty($_SERVER['HTTP_HOST']))
			       		$baseurl = 'http://'.$_SERVER['HTTP_HOST'];
				    else
				        $baseurl = rtrim("http://".$_SERVER['SERVER_NAME'],'/');
				    foreach ($img_arr as $k => $v) {
				    	$img_arr[$k] = str_replace($baseurl, '', $v);//清除域名前缀			    	
				    }
					$attid = M('attachment')->where(array('filepath' => array('in', $img_arr)))->getField('id', true);
					$dataAtt = array();
					if ($attid) {
						foreach ($attid as $v) {
							$dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => 0, 'desc' => $actionName);
						}
						M('attachmentindex')->addAll($dataAtt);
					}
					
				}
			}


			$this->success('添加成功',U(GROUP_NAME. '/Block/index'));
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
		
		$this->type = '添加自由块';
		$this->blocktypelist = getArrayOfItem('blocktype');
		
		$vo = M($actionName)->find($id);
		//非富文本,引号的问题
		$vo['content'] = str_replace("&#39;", "'", $vo['content']);	//只针对input,textarea,ueditor切换	
		$vo['content'] = htmlspecialchars($vo['content']);
		$this->vo = $vo;
		$this->display();
	}


	//修改处理
	public function editPost() {
		$actionName = strtolower($this->getActionName());

		$id = $data['id'] = I('id', 0, 'intval');
		$data['name'] = I('name', '', 'htmlspecialchars,trim');
		$data['blocktype'] = I('blocktype', 0, 'intval');
		$data['remark'] = I('remark', '');
		$content = I('content','','');		


		if (empty($data['name'])) {
			$this->error('请填写名称');
		}

		if (empty($data['blocktype'])) {
			$this->error('请选择类型');
		}

		$data['content'] = $content[$data['blocktype']];
	
		
		if (M('block')->where(array('name' => $data['name'], 'id' => array('neq', $id)))->find()) {
			$this->error('自由块名称已经存在!');
		}


		if (false !== M('block')->save($data)) {

			//更新缓存
			getBlock($data['name'], 1);

			//del
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => 0, 'desc' => $actionName))->delete();

			//图片类型
			if ($data['blocktype'] == 2) {
				
				$pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $data['content']);//清除缩略图的!200X200.jpg后缀
				$attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
				if($attid){
					M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => 0, 'desc' => $actionName));
				}

			} elseif ($data['blocktype'] == 3) {
				//内容中的图片
				$img_arr = array();
				$reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
				preg_match_all($reg, $data['content'], $img_arr, PREG_PATTERN_ORDER);
				// 匹配出来的不重复图片
				$img_arr = array_unique($img_arr[1]);
				if (!empty($img_arr)) {
					
					if(!empty($_SERVER['HTTP_HOST']))
			       		$baseurl = 'http://'.$_SERVER['HTTP_HOST'];
				    else
				        $baseurl = rtrim("http://".$_SERVER['SERVER_NAME'],'/');
				    foreach ($img_arr as $k => $v) {
				    	$img_arr[$k] = str_replace($baseurl, '', $v);//清除域名前缀			    	
				    }
					$attid = M('attachment')->where(array('filepath' => array('in', $img_arr)))->getField('id', true);
					$dataAtt = array();
					if ($attid) {
						foreach ($attid as $v) {
							$dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => 0, 'desc' => $actionName);
						}
						M('attachmentindex')->addAll($dataAtt);
					}
					
				}
			}
			

			$this->success('修改成功', U(GROUP_NAME. '/Block/index'));
		}else {

			$this->error('修改失败');
		}
		
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
		$name = M('block')->where(array('id' => $id))->getField('name');//清除F缓存用
		if (M('block')->delete($id)) {
			getBlock($name, 1);//清除缓存(更新)
			$this->success('彻底删除成功', U(GROUP_NAME. '/Block/index'));
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
		$name = M('block')->where($where)->getField('name', true);//清除F缓存用

		if (M('block')->where($where)->delete()) {
			foreach ($name as $v) {
				getBlock($v, 1);//清除缓存(更新)
			}
			$this->success('彻底删除成功', U(GROUP_NAME. '/Block/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}




}



?>
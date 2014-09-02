<?php

class AnnounceAction extends CommonAction {
	
	public function index() {
					
		//分页
		import('Class.Page', APP_PATH);
		$count = M('announce')->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = M('announce')->order('starttime desc')->limit($limit)->select();

		$this->page = $page->show();
		$this->vlist = $list;
		$this->type = '公告列表';

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

		$id = I('id', 0, 'intval');
		$content = I('content', '', 'trim');
		$validate = array(
			array('title','require','公告标题不能为空！'), 
			array('content','require','公告内容不能为空！'), 
		);

		$auto = array ( 
			array('starttime','strtotime',1,'function'), 
			array('endtime','strtotime',1,'function'), 
			array('posttime','time',1,'function'), 
			array('status','1', 1, 'string'),
		);

		$db = M('announce');
		if (!$db->auto($auto)->validate($validate)->create()) {
			$this->error($db->getError());
		}


		if($id = $db->add()) {
			//内容中的图片
			$img_arr = array();
			$reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
			preg_match_all($reg, $content, $img_arr, PREG_PATTERN_ORDER);
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
						$dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => 0, 'desc' => 'announce');
					}
					M('attachmentindex')->addAll($dataAtt);
				}
				
			}	

			$this->success('添加成功',U(GROUP_NAME. '/Announce/index'));
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

		//M验证
		$id = I('id', 0, 'intval');
		$content = I('content', '', 'trim');
		$validate = array(
			array('title','require','公告标题不能为空！'), 
			array('content','require','公告内容不能为空！'), 
		);

		$auto = array ( 
			array('starttime','strtotime',3,'function'), 
			array('endtime','strtotime',3,'function'), 
		);

		$db = M('announce');
		if (!$db->auto($auto)->validate($validate)->create()) {
			$this->error($db->getError());
		}

		

		if (false !== M('announce')->save()) {

			//del
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => 0, 'desc' => 'announce'))->delete();
			//内容中的图片
			$img_arr = array();
			$reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
			preg_match_all($reg, $content, $img_arr, PREG_PATTERN_ORDER);
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
						$dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => 0, 'desc' => 'announce');
					}
					M('attachmentindex')->addAll($dataAtt);
				}
				
			}
			$this->success('修改成功', U(GROUP_NAME. '/Announce/index'));
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
		
		if (M('announce')->delete($id)) {
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => 0, 'desc' => 'announce'))->delete();
			$this->success('彻底删除成功', U(GROUP_NAME. '/Announce/index'));
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

		if (M('announce')->where($where)->delete()) {
			M('attachmentindex')->where(array('arcid' => array('in', $idArr), 'modelid' => 0, 'desc' => 'announce'))->delete();
			$this->success('彻底删除成功', U(GROUP_NAME. '/Announce/index'));
		}else {
			$this->error('彻底删除失败');
		}
	}




}



?>
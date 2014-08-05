<?php

class SoftAction extends CommonContentAction {
	
	public function index() {

		$pid = I('pid', 0, 'intval');//类别ID
		$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字

		//所有子栏目列表
		import('Class.Category', APP_PATH);
		$cate = getCategory();
		$this->subcate = Category::clearCate(Category::getChilds($cate, $pid),'type');
		$this->poscate = Category::getParents($cate, $pid);
		
		
		if ($pid) {
			$idarr = Category::getChildsId($cate, $pid, 1);//所有子类ID
			//$where = array('soft.status' => 0, 'cid' => $pid);
			$where = array('soft.status' => 0, 'cid' => array('in', $idarr));
		}else {
			$where = array('soft.status' => 0);
		}
		
		if (!empty($keyword)) {
			$where['soft.title'] = array('LIKE', "%{$keyword}%");
		}
		
		//分页
		import('Class.Page', APP_PATH);
		$count = D('SoftView')->where($where)->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$art = D('SoftView')->where($where)->order('soft.id DESC')->limit($limit)->select();

		$this->pid = $pid;
		$this->keyword = $keyword;
		$this->page = $page->show();
		$this->vlist = $art;
		$this->type = '软件下载列表';

		$this->display();
	}
	//添加
	public function add() {

		
		//当前控制器名称		
		$actionName = strtolower($this->getActionName());
		$this->pid = I('pid', 0, 'intval');

		if (IS_POST) {
			$this->addPost();
			exit();
		}


		$cate = getCategory(2);
		import('Class.Category', APP_PATH);
		$cate = Category::unlimitedForLevel($cate);
		$this->softtypelist = getArrayOfItem('softtype');
		$this->softlanguage = getArrayOfItem('softlanguage');
		$this->type = "添加软件下载";
		$this->cate = Category::getLevelOfModel($cate, $actionName);
		$this->flagtypelist = getArrayOfItem('flagtype');//文档属性
		$this->display();
	}

	//
	public function addPost() {

		$pid = I('pid', 0, 'intval');
		$cid = I('cid', 0, 'intval');
		$title = I('title', '', 'htmlspecialchars,rtrim');
		$flags = I('flags', array(),'intval');
		$jumpurl = I('jumpurl', '');
		$description = I('description', '');
		$downlink = I('downlink', '');
		$content = I('content', '', '');			
		$updatelog = I('updatelog', '', '');
		$updatelog = str_replace("\n", '<br />', $updatelog);
		$pic = I('litpic', '', 'htmlspecialchars,trim');
		$updatetime = I('updatetime', time(), 'strtotime');
		if (empty($title)) {
			$this->error('名称不能为空');
		}
		if (!$cid) {
			$this->error('请选择栏目');
		}
		$pid = $cid;//转到自己的栏目		

		if (empty($description)) {			
			$description = str2sub(strip_tags($content), 120);
		}

		//图片标志
		if (!empty($pic) && !in_array(B_PIC, $flags)) {
			$flags[] = B_PIC;
		}
		$flag = 0;
		foreach ($flags as $v) {
			$flag += $v;
		}

		$downlink = str_replace("\n", '|||', $downlink);
		$os = I('os', array());
		$os = implode(' ', $os);


		//获取属于分类信息,得到modelid
		import('Class.Category', APP_PATH);			
		$selfCate = Category::getSelf(getCategory(0), $cid);//当前栏目信息
		$modelid = $selfCate['modelid'];

		
		$data =array(
			'title' => $title,
			'color' => I('color', 'htmlspecialchars,trim'),
			'version' => I('version', '', 'htmlspecialchars,trim'),
			'filesize' => I('filesize', '', 'htmlspecialchars,trim'),
			'downlink' => $downlink,
			'softtype' => I('softtype', 0, 'intval'),
			'language' => I('language', 0, 'intval'),
			'os' => $os,
			'cid'	=> $cid,
			'litpic'	=> $pic,
			'keywords' => I('keywords','','htmlspecialchars,trim'),
			'description' => $description,
			'content' => $content,
			'updatelog' => $updatelog,			
			'publishtime' => I('publishtime', time(),'strtotime'),
			'updatetime' => $updatetime,
			'click' => rand(10,95),
			'status' => 0,
			'commentflag' => I('commentflag', 0,'intval'),			
			'flag'	=> $flag,
			'jumpurl' => $jumpurl,
			'aid'	=> $_SESSION[C('USER_AUTH_KEY')]

		);
		
		if($id = M('soft')->add($data)) {

			//更新上传附件表
			if (!empty($pic)) {				
				$pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $pic);//清除缩略图的!200X200.jpg后缀
				$attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
				if($attid){
					M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => $modelid));
				}				
			}


			//内容中的图片
			$img_arr = array();
			$reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
			preg_match_all($reg, $content, $img_arr, PREG_PATTERN_ORDER);
			// 匹配出来的不重复图片
			$img_arr = array_unique($img_arr[1]);
			if (!empty($img_arr)) {
				$attid = M('attachment')->where(array('filepath' => array('in', $img_arr)))->getField('id', true);
				$dataAtt = array();
				if ($attid) {
					foreach ($attid as $v) {
						$dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => $modelid);
					}
					M('attachmentindex')->addAll($dataAtt);
				}
				
			}

			
			//更新静态缓存
			delCacheHtml('List/index_'.$cid, false, 'list:index');	
			delCacheHtml('Index_index', false, 'index:index');

			$this->success('添加成功',U(GROUP_NAME. '/Soft/index', array('pid' => $pid)));
		}else {
			$this->error('添加失败');
		}
	}

	//编辑文章
	public function edit() {
		//当前控制器名称
		$id = I('id', 0, 'intval');
		$actionName = strtolower($this->getActionName());
		$this->pid = I('pid', 0, 'intval');

		if (IS_POST) {
			$this->editPost();
			exit();
		}

		$cate = getCategory(2);
		import('Class.Category', APP_PATH);
		$cate = Category::unlimitedForLevel($cate);
		$this->cate = Category::getLevelOfModel($cate, $actionName);

		$this->softtypelist = getArrayOfItem('softtype');
		$this->softlanguage = getArrayOfItem('softlanguage');
		$vo = M($actionName)->find($id);
		$vo['downlink'] = str_replace('|||', "\n", $vo['downlink']);		
		$vo['updatelog'] = htmlspecialchars(str_replace('<br />', "\n", $vo['updatelog']));
		$vo['content'] = htmlspecialchars($vo['content']);
		$vo['os'] = explode(' ', $vo['os']);
		
		$this->vo = $vo;
		$this->flagtypelist = getArrayOfItem('flagtype');//文档属性
		$this->type = '修改软件下载';
		$this->display();
	}


	//修改文章处理
	public function editPost() {

		$data =array(
			'id'	=> I('id', 0, 'intval'),
			'title' => I('title','','htmlspecialchars,rtrim'),			
			'color' => I('color', 'htmlspecialchars,trim'),
			'version' => I('version', '', 'htmlspecialchars,trim'),
			'filesize' => I('filesize', '', 'htmlspecialchars,trim'),
			'downlink' => I('downlink', ''),
			'softtype' => I('softtype', 0, 'intval'),
			'language' => I('language', 0, 'intval'),
			'cid'	=> I('cid', 0, 'intval'),
			'litpic'	=> I('litpic', ''),
			'keywords' => I('keywords','','htmlspecialchars,trim'),
			'description' => I('description','','htmlspecialchars,trim'),
			'content' => I('content', '', ''),
			'updatelog' => str_replace("\n", '<br />', I('updatelog', '', '')),
			'publishtime' => I('publishtime', time(),'strtotime'),
			'updatetime' => I('updatetime', time(), 'strtotime'),
			'commentflag' => I('commentflag', 0,'intval'),
			'jumpurl' => I('jumpurl', ''),

		);

		$id = $data['id'];
		$pid = I('pid', 0, 'intval');
		$flags = I('flags', array(),'intval');
		$pic = $data['litpic'];

		if (empty($data['title'])) {
			$this->error('标题不能为空');
		}
		if (!$data['cid']) {
			$this->error('请选择栏目');
		}		
		$pid = $data['cid'];//转到自己的栏目

		if (empty($data['description'])) {			
			$data['description'] = str2sub(strip_tags($data['content']), 120);
		}


		//图片标志
		if (!empty($pic) && !in_array(B_PIC, $flags)) {
			$flags[] = B_PIC;
		}
		$data['flag'] = 0;
		foreach ($flags as $v) {
			$data['flag'] += $v;
		}


		$data['downlink'] = str_replace("\n", '|||', $data['downlink']);
		$os = I('os', array());
		$data['os'] = implode(' ', $os);
		


		//获取属于分类信息,得到modelid
		import('Class.Category', APP_PATH);			
		$selfCate = Category::getSelf(getCategory(0), $data['cid']);//当前栏目信息
		$modelid = $selfCate['modelid'];


		if (false !== M('soft')->save($data)) {
			//del
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => $modelid))->delete();
			
			//更新上传附件表
			if (!empty($pic)) {
				$pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $pic);//清除缩略图的!200X200.jpg后缀
				$attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
				if($attid){
					M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => $modelid));
				}
			}


			//内容中的图片
			$img_arr = array();
			$reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
			preg_match_all($reg, $data['content'], $img_arr, PREG_PATTERN_ORDER);
			// 匹配出来的不重复图片
			$img_arr = array_unique($img_arr[1]);
			if (!empty($img_arr)) {
				$attid = M('attachment')->where(array('filepath' => array('in', $img_arr)))->getField('id', true);
				$dataAtt = array();
				if ($attid) {
					foreach ($attid as $v) {
						$dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => $modelid);
					}
					M('attachmentindex')->addAll($dataAtt);
				}
				
			}

			//更新静态缓存
			delCacheHtml('List/index_'.$data['cid'].'_', false, 'list:index');
			delCacheHtml('List/index_'.$selfCate['ename'], false, 'list:index');//还有只有名称
			delCacheHtml('Show/index_*_'. $id, false, 'show:index');//不太精确，会删除其他模块同id文档		

			$this->success('修改成功', U(GROUP_NAME. '/Soft/index', array('pid' => $pid)));
		}else {

			$this->error('修改失败');
		}
		
	}

	//移动
	public function move() {
		//当前控制器名称
		$id = I('key', 0);
		$actionName = strtolower($this->getActionName());
		$pid = I('pid', 0, 'intval');

		if (IS_POST) {
			$id = I('id', 0);
			$cid = I('cid', 0, 'intval');
			if (empty($id)) {
				$this->error('请选择要移动的文档');
			}

			if (!$cid) {
				$this->error('请选择栏目');
			}

			if (false !== M($actionName)->where(array('id'=> array('in', $id)))->setField('cid',$cid)) {
				$this->success('移动成功', U(GROUP_NAME. '/Soft/index', array('pid' => $pid)));
			}else {
				$this->error('移动失败');
			}			
			exit();
		}

		if (empty($id)) {
			$this->error('请选择要移动的文档');
		}
	
		$cate = getCategory(2);
		import('Class.Category', APP_PATH);
		$cate = Category::unlimitedForLevel($cate);
		$this->cate = Category::getLevelOfModel($cate, $actionName);

		
		$this->id = $id;
		$this->pid = $pid;
		$this->type = '移动文档';
		$this->display();
	}


	//回收站文章列表
	public function trach() {
		import('Class.Page', APP_PATH);
		$where = array('soft.status' => 1);
		$count = D('SoftView')->where($where)->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$art = D('SoftView')->where($where)->limit($limit)->select();

		$this->pid = I('pid', 0, 'intval');
		$this->page = $page->show();
		$this->vlist = $art;		
		$this->type = '软件回收站';
		$this->subcate = '';
		$this->display('index');
	}

	//删除文章到回收站
	public function del() {

		$id = I('id',0 , 'intval');
		$batchFlag = I('get.batchFlag', 0, 'intval');
		//批量删除
		if ($batchFlag) {
			$this->delBatch();
			return;
		}

		$pid = I('pid',0 , 'intval');//单纯的GET没问题

		if (false !== M('soft')->where(array('id' => $id))->setField('status', 1)) {

			delCacheHtml('Show/index_*_'. $id.'.', false, 'show:index');	
			$this->success('删除成功', U(GROUP_NAME. '/Soft/index', array('pid' => $pid)));
			
		}else {
			$this->error('删除失败');
		}
	}

	//批量删除到回收站
	public function delBatch() {

		$idArr = I('key',0 , 'intval');
		$pid = I('get.pid', 0, 'intval');

		if (!is_array($idArr)) {
			$this->error('请选择要删除的项');
		}

		if (false !== M('soft')->where(array('id' => array('in', $idArr)))->setField('status', 1)) {
			
			//更新静态缓存
			foreach ($idArr as $v) {
				delCacheHtml('Show/index_*_'. $v.'.', false, 'show:index');	
			}
			$this->success('批量删除成功', U(GROUP_NAME. '/Soft/index', array('pid' => $pid)));
			
		}else {
			$this->error('批量删除文失败');
		}
	}

	//还原文章
	public function restore() {
		
		$id = I('id',0 , 'intval');
		$batchFlag = I('get.batchFlag', 0, 'intval');
		//批量删除
		if ($batchFlag) {
			$this->restoreBatch();
			return;
		}

		$pid = I('get.pid', 0, 'intval');

		if (false !== M('soft')->where(array('id' => $id))->setField('status', 0)) {
			
			$this->success('还原成功', U(GROUP_NAME. '/Soft/trach', array('pid' => $pid)));
			
		}else {
			$this->error('还原失败');
		}
	}

	//批量还原文章
	public function restoreBatch() {
		
		$idArr = I('key',0 , 'intval');
		$pid = I('get.pid', 0, 'intval');
		if (!is_array($idArr)) {
			$this->error('请选择要还原的项');
		}

		if (false !== M('soft')->where(array('id' => array('in', $idArr)))->setField('status', 0)) {
			
			$this->success('还原成功', U(GROUP_NAME. '/Soft/trach', array('pid' => $pid)));
			
		}else {
			$this->error('还原失败');
		}
	}

	//彻底删除文章
	public function clear() {

		$id = I('id',0 , 'intval');
		$batchFlag = I('get.batchFlag', 0, 'intval');
		//批量删除
		if ($batchFlag) {
			$this->clearBatch();
			return;
		}

		$pid = I('get.pid', 0, 'intval');
		$modelid = D('SoftView')->where(array('id' => $id))->getField('modelid');

		if (M('soft')->delete($id)) {
			// delete picture index
			if ($modelid) {
				M('attachmentindex')->where(array('arcid' => $id , 'modelid' => $modelid ))->delete();//test
			}
			$this->success('彻底删除成功', U(GROUP_NAME. '/Soft/trach', array('pid' => $pid)));
		}else {
			$this->error('彻底删除失败');
		}
	}


	//批量彻底删除文章
	public function clearBatch() {

		$idArr = I('key',0 , 'intval');		
		$pid = I('get.pid', 0, 'intval');
		if (!is_array($idArr)) {
			$this->error('请选择要彻底删除的项');
		}
		$where = array('id' => array('in', $idArr));
		$modelid = D('SoftView')->where(array('id' => $idArr[0]))->getField('modelid');//

		if (M('soft')->where($where)->delete()) {
			// delete picture index
			if ($modelid) {
				M('attachmentindex')->where(array('arcid' => array('in', $idArr) , 'modelid' => $modelid ))->delete();
			}
			$this->success('彻底删除成功', U(GROUP_NAME. '/Soft/trach', array('pid' => $pid)));
		}else {
			$this->error('彻底删除失败');
		}
	}

	
}



?>
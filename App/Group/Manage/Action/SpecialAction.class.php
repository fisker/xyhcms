<?php

class SpecialAction extends CommonAction {
	
	public function index() {

	
		$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字	

		$where = array('special.status' => 0);		

		
		if (!empty($keyword)) {
			$where['special.title'] = array('LIKE', "%{$keyword}%");
		}
		
		//分页
		import('Class.Page', APP_PATH);
		$count = D('SpecialView')->where($where)->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$art = D('SpecialView')->nofield('content')->where($where)->order('id DESC')->limit($limit)->select();

		
		$this->keyword = $keyword;
		$this->page = $page->show();
		$this->vlist = $art;
		$this->type = '专题列表';

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
		$this->cate = Category::toLevel($cate);
		$_styleShowList = getFileFolderList(APP_PATH . C('APP_GROUP_PATH') . '/Home/Tpl/' .C('cfg_themestyle') , 2, 'Special_*');

		$styleShowList = array();
		foreach ($_styleShowList as $v) {
			if (strpos($v, 'Special_index') === false) {
				$styleShowList[] = $v;
			}
		}
		$this->styleShowList = $styleShowList;
		$this->type = '添加专题';
		$this->flagtypelist = getArrayOfItem('flagtype');//文档属性
		$this->display();
	}

	//
	public function addPost() {


		$cid = I('cid', 0, 'intval');
		$title = I('title', '', 'htmlspecialchars,rtrim');		
		$flags = I('flags', array(),'intval');
		$jumpurl = I('jumpurl', '');
		$description = I('description', '');
		$template = I('template', '');

		$pic = I('litpic', '', 'htmlspecialchars,trim');
		if (empty($title)) {
			$this->error('专题名称不能为空');
		}

		if (empty($template)) {
			$this->error('请选择专题模板');
		}


		//图片标志
		if (!empty($pic) && !in_array(B_PIC, $flags)) {
			$flags[] = B_PIC;
		}
		$flag = 0;
		foreach ($flags as $v) {
			$flag += $v;
		}
	
	

		//获取属于分类信息,得到modelid
		//import('Class.Category', APP_PATH);			
		//$selfCate = Category::getSelf(getCategory(0), $cid);//当前栏目信息
		//$modelid = $selfCate['modelid'];

		$data =array(
			'title' => $title ,
			'shorttitle' => I('shorttitle', ''),
			'color' => I('color'),
			'cid'	=> $cid,
			'author'	=> '',
			'keywords' => I('keywords','','htmlspecialchars,trim'),
			'litpic'	=> $pic,
			'description' => $description,
			//'content' => '',
			'publishtime' => I('publishtime', time(),'strtotime'),
			'updatetime' => time(),
			'click' => rand(10,95),
			'commentflag' => I('commentflag', 0,'intval'),	
			'status' => 0,		
			'filename' => I('filename', ''),			
			'template' => $template,
			'flag'	=> $flag,
			'jumpurl' => $jumpurl,
			'aid'	=> $_SESSION[C('USER_AUTH_KEY')]

		);
		
		if($id = M('special')->add($data)) {

			//更新上传附件表
			if (!empty($pic)) {

				$pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $pic);//清除缩略图的!200X200.jpg后缀
				$attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
				if($attid){
					M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => 0, 'desc' => 'special'));
				}
			}	

			//更新静态缓存
			delCacheHtml('Special/index', false, 'special:index');	
			delCacheHtml('Index_index', false, 'index:index');		


			$this->success('添加成功',U(GROUP_NAME. '/Special/index'));
		}else {
			$this->error('添加失败');
		}
	}

	//编辑
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
		$this->cate = Category::toLevel($cate);
		$_styleShowList = getFileFolderList(APP_PATH . C('APP_GROUP_PATH') . '/Home/Tpl/' .C('cfg_themestyle') , 2, 'Special_*');
		$styleShowList = array();		
		foreach ($_styleShowList as $v) {
			if (strpos($v, 'Special_index') === false) {
				$styleShowList[] = $v;
			}
		}
		$this->type = '修改专题';

		$this->styleShowList = $styleShowList;
		$vo = M($actionName)->find($id);
		$vo['content'] = htmlspecialchars($vo['content']);//ueditor
		$this->vo = $vo;
		$this->flagtypelist = getArrayOfItem('flagtype');//文档属性
		$this->display();
	}


	//修改处理
	public function editPost() {


		$data =array(
			'id' => I('id', 0, 'intval'),
			'title' => I('title', '', 'htmlspecialchars,rtrim'),
			'shorttitle' => I('shorttitle', ''),
			'color' => I('color'),
			'cid'	=> I('cid', 0, 'intval'),
			'author'	=> '',
			'keywords' => I('keywords','','htmlspecialchars,trim'),
			'litpic'	=> I('litpic',''),
			'description' => I('description',''),
			//'content' => '',
			'publishtime' => I('publishtime', time(),'strtotime'),
			'updatetime' => time(),
			'commentflag' => I('commentflag', 0,'intval'),			
			'filename' => I('filename', ''),			
			'template' => I('template', ''),
			'jumpurl' => I('jumpurl', ''),
		);
		$id = $data['id'] = intval($data['id']);
		$flags = I('flags', array(),'intval');
		$pic = $data['litpic'];

		if (empty($data['title'])) {
			$this->error('专题名称不能为空');
		}
		
		if (empty($data['template'])) {
			$this->error('请选择专题模板');
		}


		//图片标志
		if (!empty($pic) && !in_array(B_PIC, $flags)) {
			$flags[] = B_PIC;
		}
		$data['flag'] = 0;
		foreach ($flags as $v) {
			$data['flag'] += $v;
		}


		//获取属于分类信息,得到modelid
		//import('Class.Category', APP_PATH);			
		//$selfCate = Category::getSelf(getCategory(0), $data['cid']);//当前栏目信息
		//$modelid = $selfCate['modelid'];

	
		if (false !== M('special')->save($data)) {
			//del
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => 0, 'desc' => 'special'))->delete();
			
			//更新上传附件表
			if (!empty($pic)) {

				$pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $pic);//清除缩略图的!200X200.jpg后缀
				$attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
				if($attid){
					M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => 0, 'desc' => 'special'));
				}
				//halt(M('attachment')->getlastsql());
			}

			//更新静态缓存
			delCacheHtml('Special/index', false, 'special:index');
			delCacheHtml('Special/shows_'.$id, false, 'special:shows');


			$this->success('修改成功', U(GROUP_NAME. '/Special/index'));
		}else {

			$this->error('修改失败');
		}
		
	}


	//回收站列表
	public function trach() {
		import('Class.Page', APP_PATH);
		$where = array('special.status' => 1);
		$count = D('SpecialView')->where($where)->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$art = D('SpecialView')->nofield('content')->where($where)->limit($limit)->select();

		$this->pid = I('pid', 0, 'intval');
		$this->page = $page->show();
		$this->vlist = $art;		
		$this->type = '回收站';
		$this->subcate = '';
		$this->display('index');
	}

	//删除到回收站
	public function del() {

		$id = I('id',0 , 'intval');
		$batchFlag = I('get.batchFlag', 0, 'intval');
		//批量删除
		if ($batchFlag) {
			$this->delBatch();
			return;
		}


		if (false !== M('special')->where(array('id' => $id))->setField('status', 1)) {
			
			//更新静态缓存
			delCacheHtml('Special/index', false, 'special:index');
			delCacheHtml('Special/shows_'.$id, false, 'special:shows');

			$this->success('删除成功', U(GROUP_NAME. '/Special/index'));
			
		}else {
			$this->error('删除失败');
		}
	}

	//批量删除到回收站
	public function delBatch() {

		$idArr = I('key',0 , 'intval');

		if (!is_array($idArr)) {
			$this->error('请选择要删除的项');
		}

		if (false !== M('special')->where(array('id' => array('in', $idArr)))->setField('status', 1)) {
			
			//getlastsql();
			//更新静态缓存
			delCacheHtml('Special/index', false, 'special:index');
			foreach ($idArr as $v) {
				delCacheHtml('Special/shows_'.$v, false, 'special:shows');
			}

			
			$this->success('批量删除成功', U(GROUP_NAME. '/Special/index'));
			
		}else {
			$this->error('批量删除文失败');
		}
	}

	//还原
	public function restore() {
		
		$id = I('id',0 , 'intval');
		$batchFlag = I('get.batchFlag', 0, 'intval');
		//批量删除
		if ($batchFlag) {
			$this->restoreBatch();
			return;
		}

		if (false !== M('special')->where(array('id' => $id))->setField('status', 0)) {
			
			$this->success('还原成功', U(GROUP_NAME. '/Special/trach'));
			
		}else {
			$this->error('还原失败');
		}
	}

	//批量还原
	public function restoreBatch() {
		
		$idArr = I('key',0 , 'intval');
		if (!is_array($idArr)) {
			$this->error('请选择要还原的项');
		}

		if (false !== M('special')->where(array('id' => array('in', $idArr)))->setField('status', 0)) {
			
			$this->success('还原成功', U(GROUP_NAME. '/Special/trach'));
			
		}else {
			$this->error('还原失败');
		}
	}

	//彻底删除
	public function clear() {

		$id = I('id',0 , 'intval');
		$batchFlag = I('get.batchFlag', 0, 'intval');
		//批量删除
		if ($batchFlag) {
			$this->clearBatch();
			return;
		}

		if (M('special')->delete($id)) {
			// delete picture index
			
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => 0, 'desc' => 'special'))->delete();
			
			$this->success('彻底删除成功', U(GROUP_NAME. '/Special/trach'));
		}else {
			$this->error('彻底删除失败');
		}
	}


	//批量彻底删除
	public function clearBatch() {

		$idArr = I('key',0 , 'intval');		
		if (!is_array($idArr)) {
			$this->error('请选择要彻底删除的项');
		}
		$where = array('id' => array('in', $idArr));
		if (M('special')->where($where)->delete()) {
			// delete picture index
			M('attachmentindex')->where(array('arcid' => array('in', $idArr), 'modelid' => 0, 'desc' => 'special'))->delete();
			$this->success('彻底删除成功', U(GROUP_NAME. '/Special/trach'));
		}else {
			$this->error('彻底删除失败');
		}
	}

	
}



?>
<?php

class PictureAction extends CommonContentAction {
	
	public function index() {

		$pid = I('pid', 0, 'intval');//类别ID
		$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字

		//所有子栏目列表
		import('Class.Category', APP_PATH);
		$cate = getCategory();//全部分类
		$this->subcate = Category::getChilds($cate, $pid);
		$this->poscate = Category::getParents($cate, $pid);

		
		if ($pid) {			
			$idarr = Category::getChildsId($cate, $pid, 1);//所有子类ID
			$where = array('picture.status' => 0, 'cid' => array('in', $idarr));
		}else {
			$where = array('picture.status' => 0);
		}

		
		if (!empty($keyword)) {
			$where['picture.title'] = array('LIKE', "%{$keyword}%");
		}
		
		//分页
		import('Class.Page', APP_PATH);
		$count = D('PictureView')->where($where)->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$art = D('PictureView')->where($where)->order('picture.id DESC')->limit($limit)->select();

		$this->pid = $pid;
		$this->keyword = $keyword;
		$this->page = $page->show();
		$this->vlist = $art;
		$this->type = '图片集列表';

		$this->display();
	}
	//添加文章
	public function add() {
		//当前控制器名称		
		$actionName = strtolower($this->getActionName());
		$this->pid = I('pid', 0, 'intval');

		if (IS_POST) {
			$this->addHandle();
			exit();
		}

		$cate = getCategory(2);
		import('Class.Category', APP_PATH);
		$cate = Category::unlimitedForLevel($cate);
		$this->cate = Category::getLevelOfModel($cate, $actionName);
		$this->flagtypelist = getArrayOfItem('flagtype');//文档属性
		$this->display();
	}

	//
	public function addHandle() {

		$pid = I('pid', 0, 'intval');
		$cid = I('cid', 0, 'intval');
		$title = I('title', '', 'htmlspecialchars,rtrim');
		$flags = I('flags', array(),'intval');
		$jumpurl = I('jumpurl', '');
		$description = I('description', '', 'htmlspecialchars');
		$content = I('content', '', '');
		$pic = '';

		if (empty($title)) {
			$this->error('图片名称不能为空');
		}
		if (!$cid) {
			$this->error('请选择栏目');
		}
		$pid = $cid;//转到自己的栏目

		if (empty($description)) {			
			$description = str2sub(strip_tags($content), 120);
		}

		
		//获取属于分类信息,得到modelid
		import('Class.Category', APP_PATH);			
		$selfCate = Category::getSelf(getCategory(0), $cid);//当前栏目信息
		$modelid = $selfCate['modelid'];

		$pictureurls_arr  = array();

		$imgPostUrls = I('pictureurls', '');
		if (is_array($imgPostUrls)) {
			foreach ($imgPostUrls as $k => $v) {
				$pictureurls_arr[] = $v.'$$$'.'$$$';
				//缩略图
				if ($k == 0) {
					$imgtbSize = explode(',', C('cfg_imgthumb_size'));//配置缩略图第一个参数
                	$imgTSize = explode('X', $imgtbSize[0]);
					if (!empty($imgTSize)) {
						$pic = get_picture($v, $imgTSize[0], $imgTSize[1]);
					}else {
						$pic = $v;
					}
				}
			}
		}
		$pictureurls = join('|||',$pictureurls_arr);
		$pic = isset($pic) ? $pic : '';

		//图片标志
		if (!empty($pic) && !in_array(B_PIC, $flags)) {
			$flags[] = B_PIC;
		}
		$flag = 0;
		foreach ($flags as $v) {
			$flag += $v;
		}	


		$data =array(
			'title' => $title,
			'color' => I('color'),
			'cid'	=> $cid,
			'litpic'	=> $pic,
			'keywords' => I('keywords','','htmlspecialchars,trim'),
			'description' => $description,
			'copyfrom' => I('copyfrom', ''),
			'content' => $content,
			'pictureurls' => $pictureurls,			
			'publishtime' => I('publishtime', time(),'strtotime'),
			'updatetime' => time(),
			'click' => rand(10,95),
			'status' => 0,
			'commentflag' => I('commentflag', 0,'intval'),
			'flag'	=> $flag,
			'jumpurl' => $jumpurl,
			'aid'	=> $_SESSION[C('USER_AUTH_KEY')]

		);

		if($id = M('picture')->add($data)) {
			//更新图片集
		
			if (!empty($imgPostUrls)) {
				$attid = M('attachment')->where(array('filepath' => array('in', $imgPostUrls)))->getField('id', true);
				$dataAtt = array();
				if ($attid) {
					foreach ($attid as $v) {
						$dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => $modelid);
					}
					M('attachmentindex')->addAll($dataAtt);
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


			$this->success('添加成功',U(GROUP_NAME. '/Picture/index', array('pid' => $pid)));
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
			$this->editHandle();
			exit();
		}

		$this->pid = I('pid', 0, 'intval');
		$cate = getCategory(2);
		import('Class.Category', APP_PATH);
		$cate = Category::unlimitedForLevel($cate);
		$this->cate = Category::getLevelOfModel($cate, $actionName);
		
		$vo = M($actionName)->find($id);
		
		$pictureurls = array();
		if (!empty($vo['pictureurls'])) {	
			$temparr = explode('|||', $vo['pictureurls']);		
			foreach ($temparr as $key => $v) {
				$temparr2 = explode('$$$', $v);
				$pictureurls[] = array('url' => ''.$temparr2[0], 'alt' => ''.$temparr2[1]);
			}
		}
		
		$vo['pictureurls'] = $pictureurls;
		$vo['content'] = htmlspecialchars($vo['content']);
		$this->vo = $vo;
		$this->flagtypelist = getArrayOfItem('flagtype');//文档属性
		$this->display();
	}


	//修改文章处理
	public function editHandle() {


		$data =array(
			'id'	=> I('id', 0 , 'intval'),
			'title' => I('title','','htmlspecialchars,rtrim'),
			'color' => I('color', ''),
			'cid'	=> I('cid', 0, 'intval'),
			'keywords' => I('keywords','','htmlspecialchars,trim'),
			'description' => I('description', ''),
			'copyfrom' => I('copyfrom', ''),
			'content' => I('content', '', ''),
			'pictureurls' => I('pictureurls', ''),//I方法BUG,不支持数组,			
			'publishtime' => I('publishtime', time(),'strtotime'),
			'updatetime' => time(),
			'commentflag' => I('commentflag', 0,'intval'),
			'jumpurl' => I('jumpurl', ''),
		);
		$id = $data['id'] ;
		$pid = I('pid', 0, 'intval');
		$flags = I('flags', array(),'intval');

		if (empty($data['title'])) {
			$this->error('产品名称不能为空');
		}
		if (!$data['cid']) {
			$this->error('请选择栏目');
		}
		
		$pid = $data['cid'];//转到自己的栏目

		if (empty($data['description'])) {			
			$data['description'] = str2sub(strip_tags($data['content']), 120);
		}



		//获取属于分类信息,得到modelid
		import('Class.Category', APP_PATH);			
		$selfCate = Category::getSelf(getCategory(0), $data['cid']);//当前栏目信息
		$modelid = $selfCate['modelid'];


		$pictureurls_arr  = array();
		$imgPostUrls = $data['pictureurls'];
		if (is_array($imgPostUrls)) {
			foreach ($imgPostUrls as $k => $v) {
				$pictureurls_arr[] = $v.'$$$'.'$$$';//array('url'=> $v ,'alt'=> '');
				if ($k == 0) {
					$imgtbSize = explode(',', C('cfg_imgthumb_size'));//配置缩略图第一个参数
                	$imgTSize = explode('X', $imgtbSize[0]);
					if (!empty($imgTSize)) {
						$pic = get_picture($v, $imgTSize[0], $imgTSize[1]);
					}else {
						$pic = $v;
					}
				}
			}
		}

		$data['pictureurls'] = join('|||',$pictureurls_arr);	
		$data['litpic'] = isset($pic) ? $pic : '';


		//图片标志
		if (!empty($data['litpic']) && !in_array(B_PIC, $flags)) {
			$flags[] = B_PIC;
		}
		$data['flag'] = 0;
		foreach ($flags as $v) {
			$data['flag'] += $v;
		}

		
		if (false !== M('picture')->save($data)) {	
			//del
			M('attachmentindex')->where(array('arcid' => $id, 'modelid' => $modelid))->delete();
			//更新图片集		
			if (!empty($imgPostUrls)) {
				$attid = M('attachment')->where(array('filepath' => array('in', $imgPostUrls)))->getField('id', true);
				$dataAtt = array();
				if ($attid) {
					foreach ($attid as $v) {
						$dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => $modelid);
					}
					M('attachmentindex')->addAll($dataAtt);
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

			$this->success('修改成功', U(GROUP_NAME. '/Picture/index', array('pid' => $pid)));
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
				$this->success('移动成功', U(GROUP_NAME. '/Picture/index', array('pid' => $pid)));
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

	//回收站
	public function trach() {
		import('Class.Page', APP_PATH);
		$where = array('picture.status' => 1);
		$count = D('PictureView')->where($where)->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$art = D('PictureView')->where($where)->order('picture.id DESC')->limit($limit)->select();

		$this->pid = I('pid', 0, 'intval');
		$this->page = $page->show();
		$this->vlist = $art;		
		$this->type = '图片集回收站';
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

		if (false !== M('picture')->where(array('id' => $id))->setField('status', 1)) {

			delCacheHtml('Show/index_*_'. $id.'.', false, 'show:index');	
			$this->success('删除成功', U(GROUP_NAME. '/Picture/index', array('pid' => $pid)));
			
		}else {
			$this->error('删除失败');
		}
	}

	//批量删除到回收站
	public function delBatch() {

		$idArr = I('key', 0, 'intval');
		$pid = I('get.pid', 0, 'intval');

		if (!is_array($idArr)) {
			$this->error('请选择要删除的项');
		}

		if (false !== M('picture')->where(array('id' => array('in', $idArr)))->setField('status', 1)) {
			
			//更新静态缓存
			foreach ($idArr as $v) {
				delCacheHtml('Show/index_*_'. $v.'.', false, 'show:index');	
			}
			$this->success('批量删除成功', U(GROUP_NAME. '/Picture/index', array('pid' => $pid)));
			
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
		
		$pid = I('get.pid', 0, 'intval');

		if (false !== M('picture')->where(array('id' => $id))->setField('status', 0)) {
			
			$this->success('还原成功', U(GROUP_NAME. '/Picture/trach', array('pid' => $pid)));
			
		}else {
			$this->error('还原失败');
		}
	}

	//批量还原
	public function restoreBatch() {
		
		$idArr = I('key',0 , 'intval');
		$pid = I('get.pid', 0, 'intval');
		if (!is_array($idArr)) {
			$this->error('请选择要还原的项');
		}

		if (false !== M('picture')->where(array('id' => array('in', $idArr)))->setField('status', 0)) {
			
			$this->success('还原成功', U(GROUP_NAME. '/Picture/trach', array('pid' => $pid)));
			
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
		
		$pid = I('get.pid', 0, 'intval');
		$modelid = D('PictureView')->where(array('id' => $id))->getField('modelid');

		if (M('picture')->delete($id)) {
			// delete picture index
			if ($modelid) {
				M('attachmentindex')->where(array('arcid' => $id , 'modelid' => $modelid ))->delete();
			}
			
			$this->success('彻底删除成功', U(GROUP_NAME. '/Picture/trach', array('pid' => $pid)));
		}else {
			$this->error('彻底删除失败');
		}
	}


	//批量彻底删除
	public function clearBatch() {

		$idArr = I('key',0 , 'intval');		
		$pid = I('get.pid', 0, 'intval');
		if (!is_array($idArr)) {
			$this->error('请选择要彻底删除的项');
		}
		$where = array('id' => array('in', $idArr));
		$modelid = D('PictureView')->where(array('id' => $idArr[0]))->getField('modelid');//for delete picture index,use

		if (M('picture')->where($where)->delete()) {
			// delete picture index
			if ($modelid) {
				M('attachmentindex')->where(array('arcid' => array('in', $idArr) , 'modelid' => $modelid ))->delete();
			}
			$this->success('彻底删除成功', U(GROUP_NAME. '/Picture/trach', array('pid' => $pid)));
		}else {
			$this->error('彻底删除失败');
		}
	}

	


}



?>
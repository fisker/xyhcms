<?php

class ClearHtmlAction extends CommonAction {
	
	public function index() {

	}


	//一键更新静态缓存html
	public function all() {

		if (IS_POST) {
			delCacheHtml('', true);
			$this->success('更新成功!', U(GROUP_NAME.'/ClearHtml/all'));
			exit();
		}

		$this->type = '一键更新|静态缓存';
		$this->display();
	}


	//更新首页静态缓存html
	public function home() {

		if (IS_POST) {
			delCacheHtml('Index_index', false, 'index:index');
			$this->success('更新成功!', U(GROUP_NAME.'/ClearHtml/home'));
			exit();
		}

		$this->type = '更新首页|静态缓存';
		$this->display('all');
	}

	//更新栏目静态缓存html
	public function lists() {

		if (IS_POST) {
			$isall = I('get.isall', 0, 'intval');
			if ($isall) {
				delCacheHtml('List', true, '');
			}else {
				$idArr = I('key', array(), '');
				$cate = M('category')->where(array('id' => array('IN', $idArr), 'type' => 0))->field(array('id', 'ename'))->select();
				foreach ($cate as $v) {
					//更新静态缓存
					delCacheHtml('List/index_'.$v['id'].'_', false, 'list:index');
					delCacheHtml('List/index_'.$v['ename'], false, 'list:index');//还有只有名称
				}

			}
			
			$this->success('更新成功!', U(GROUP_NAME.'/ClearHtml/lists'));
			exit();
		}

		$cate = D('CategoryView')->nofield('content')->where(array('category.type' => 0))->order('category.sort,category.id')->select();
		//$cate = getCategory();
		import('Class.Category', APP_PATH);
		$this->cate = Category::toLevel($cate, '&nbsp;&nbsp;&nbsp;&nbsp;', 0);

		$this->type = '更新栏目|静态缓存';
		$this->display('all');
	}


	//更新内容页静态缓存html
	public function shows() {

		if (IS_POST) {
			$isall = I('get.isall', 0, 'intval');
			if ($isall) {
				delCacheHtml('Show', true, '');
			}else {
				$idArr = I('key', array(), '');
				$cate = D('CategoryView')->where(array('category.id' => array('IN', $idArr), 'type' => 0))->field(array('id', 'ename', 'tablename'))->select();
				foreach ($cate as $v) {
					//更新静态缓存
					delCacheHtml('Show/index_'.$v['id'].'_', false, 'show:index');
					delCacheHtml('Show/index_'.$v['ename'], false, 'show:index');//还有只有名称
				}

			}
			
			$this->success('更新成功!', U(GROUP_NAME.'/ClearHtml/shows'));
			exit();
		}

		$cate = D('CategoryView')->where(array('category.type' => 0))->order('category.sort,category.id')->select();
		//$cate = getCategory();
		import('Class.Category', APP_PATH);
		$this->cate = Category::toLevel($cate, '&nbsp;&nbsp;&nbsp;&nbsp;', 0);

		$this->type = '更新内容页(文档)|静态缓存';
		$this->display('all');
	}

	//更新专题静态缓存html
	public function special() {

		if (IS_POST) {
			$isall = I('get.isall', 0, 'intval');
			if ($isall) {
				delCacheHtml('Special', true, '');
			}else {				
					delCacheHtml('Special/index', false, 'special:index');
			}
			
			$this->success('更新成功!', U(GROUP_NAME.'/ClearHtml/special'));
			exit();
		}


		$this->type = '更新专题|静态缓存';
		$this->display('all');
	}




}


?>
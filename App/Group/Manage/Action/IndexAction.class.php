<?php

class IndexAction extends CommonAction{
	
	public function index(){
		$this->menudoclist = D('CategoryView')->nofield('content')->where(array('pid' => 0 , 'type' => 0))->order('category.sort,category.id')->select();

		$menu = M('menu')->order('sort,id')->select();
		if (empty($menu)) {
			$menu = array();
		}
		import('Class.Category', APP_PATH);
		$this->menu = Category::toLayer($menu);
		$this->display();
	}

	public function getParentCate(){
		header("Content-Type:text/html; charset=utf-8");//不然返回中文乱码
		$count = D('CategoryView')->where(array('pid' => 0 , 'type' => 0))->count();
		$list = D('CategoryView')->nofield('content')->where(array('pid' => 0 , 'type' => 0))->order('category.sort,category.id')->select();
		$menudoclist = array('count' => $count);
		foreach ($list as $v) {
			$menudoclist['list'][] = array(
				'id' => $v['id'],				
				'name' => $v['name'],		
				'url' => U(GROUP_NAME.'/'. ucfirst($v['tablename']) .'/index', array('pid'=>$v['id']))
			);
		}
		exit(json_encode($menudoclist));
	}


}


?>
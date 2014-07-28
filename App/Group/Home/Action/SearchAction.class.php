<?php
//SearchAction
class SearchAction extends Action{
	//方法：index
	public function index(){

		$cid = I('cid', 0,'intval');		
		$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字
		$cate = getCategory(2);//去除单页和外链接
		
		if($keyword == '请输入关键词') $keyword = '';
		if (!empty($cid)) {

			import('Class.Category', APP_PATH);			
			$self = Category::getSelf($cate, $cid);//当前栏目信息	

			if(!empty($self)) {
				//当前位置
				$position = '<a href="'.C('cfg_weburl') .'">首页</a>&gt;&gt;搜索中心&gt;&gt;'.$self['name']."-搜索 {$keyword} 结果";
				$title = empty($self['seotitle']) ? $self['name'] : $self['seotitle'];
				$title .= '-搜索结果';	
				$self['url'] = getUrl($self);

			}else {
				$cid = 0;
				$self = array(
						'id' => 0,
						'name' => '',
						'ename' => '',
						'url' => '',
					);
			}
		}

		if ($cid == 0 ) {
			$position = '<a href="'.C('cfg_weburl') .'">首页</a>&gt;&gt;搜索中心';
			$title = '搜索中心';
			$self = array(
					'id' => 0,
					'name' => '',
					'ename' => '',
					'url' => '',
				);

		}


		$this->cate = $self;
		$this->title = $title;
		$this->keyword = $keyword;
		$this->cid = $cid;
		$this->searchurl = U('Search/index');
		$this->page = '';		
		$this->display();

	}


}

?>
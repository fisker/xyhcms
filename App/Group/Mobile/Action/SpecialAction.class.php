<?php

class SpecialAction extends Action{
	
	public function index(){
		
		$cid = I('cid', 0,'intval');

		$cate = getCategory(1);
		import('Class.Category', APP_PATH);	
		$self = Category::getSelf($cate, $cid);//当前栏目信息
	
		
		$this->title = "专题首页";
		$this->display();

	}


	/*测试－用户模型*/
	public function lists(){
		
		$cid = I('cid', 0,'intval');

		$cate = getCategory(1);
		import('Class.Category', APP_PATH);	
		$self = Category::getSelf($cate, $cid);//当前栏目信息

		$patterns = array('/^List_/', '/.html$/');
		$replacements = array('', '');
		$template_list = preg_replace($patterns, $replacements, $self['template_list']);
		
		if (empty($template_list)) {
			$this->error('模板不存在');
		}
	
		
		$this->title = "专题首页";
		$this->display($template_list);

	}


	public function shows($id = 0){
		$id = I('id', 0, 'intval');
		if ($id == 0) {
			$this->error('参数错误');
		}

		$content = M('special')->find($id);
		if (!$content) {
			$this->error('专题不存在');
		}
		$cid = $content['cid'];

		$cate = getCategory(1);
		import('Class.Category', APP_PATH);
		$self = Category::getSelf($cate, $cid);//当前栏目信息
		
		if(empty($self)) {
			$self = array(
					'id' => 0,
					'name' => '',
					'ename' => '',
					'url' => '',
				);
		}

		$this->cate = $self;

		$patterns = array('/^Special_/', '/.html$/');
		$replacements = array('', '');
		$template_show = preg_replace($patterns, $replacements, $content['template']);
		if (empty($template_show)) {
			$this->error('模板不存在');
		}

		
		$this->title = $content['title'];
		$this->keywords = $content['keywords'];
		$this->description = $content['description'];
		$this->commentflag = $content['commentflag'];//是否允许评论,debug,以后加上个全局评价 $content['commentflag'] && CFG_Comment
		$this->content = $content;
		$this->tablename = 'special';
		$this->id = $id;
		$this->display($template_show);

	}


}

?>
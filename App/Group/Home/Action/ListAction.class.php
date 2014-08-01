<?php
//ListAction
class ListAction extends Action{
	//方法：index
	public function index(){
		
		$cid = I('cid', 0,'intval');
		$ename = I('e', '', 'htmlspecialchars,trim');

		$cate = getCategory(1);
		import('Class.Category', APP_PATH);	
		
		if (!empty($ename)) {//ename不为空
			$self = Category::getSelfByEName($cate, $ename);//当前栏目信息
		}else {//$cid来判断

			$self = Category::getSelf($cate, $cid);//当前栏目信息
		}		

		if(empty($self)) {
			$this->error('栏目不存在');
		}
		
	
		$cid = $self['id'];//当使用ename获取的时候，就要重新给$cid赋值，不然0
		$_GET['cid'] = $cid;//栏目ID
		$self['url'] = getUrl($self);

		//访问权限
		$groupid = intval(get_cookie('groupid'));
		$groupid = empty($groupid) ? 1 : $groupid;//1为游客
		//判断访问权限
		$access = M('categoryAccess')->where(array('catid' => $cid, 'flag' => 0 , 'action' => 'visit'))->getField('roleid', true);
		//权限存在，则判断
		if (!empty($access) && !in_array($groupid, $access)) {
			$this->error('您没有访问该信息的权限！');
		}


		$this->cate = $self;
		$this->flag_son = Category::hasChild($cate, $cid);//是否包含子类
		$this->title = empty($self['seotitle']) ? $self['name'] : $self['seotitle'];
		$this->keywords = $self['keywords'];
		$this->description = $self['description'];
		$this->cid = $cid;

		
		$patterns = array('/^List_/', '/'.C('TMPL_TEMPLATE_SUFFIX').'$/');
		$replacements = array('', '');
		$template_list = preg_replace($patterns, $replacements, $self['template_list']);
		
		if (empty($template_list)) {
			$this->error('模板不存在');
		}

		switch ($self['tablename']) {
			case 'article':
				$this->display($template_list);
				return;
				break;
			case 'product':
				$this->display($template_list);
				return;
				break;
			case 'picture':
				$this->display($template_list);
				return;
				break;	
			case 'soft':
				$this->display($template_list);
				return;
				break;	
			case 'page':
				{
					$cate = M('Category')->Field('content')->find($cid);
					$self['content'] = $cate['content'];
					$this->cate = $self;
					$this->display($template_list);
				}
				return;
				break;	
			case 'phrase':
				$this->display($template_list);
				return;
				break;		
			default:
				//$this->error('参数错误');
				$userOther = A(ucfirst($self['tablename']));
				$userOther->lists();
				return;
				break;
		}
		
		$this->display();

	}



	//page
	public function page($cid = 0){
		
		if($cid == 0) $this->error('参数错误');

		$self = $this->cate;

		$cate = M('Category')->Field('content')->find($cid);
		$self['content'] = $cate['content'];
		$this->cate = $self;
		$this->display('page');

	}

	//page
	public function article($cid = 0){
		
		if($cid == 0) $this->error('参数错误');

		$this->cid = $cid;		
		$this->display('article');
		

	}

	//product
	public function product($cid = 0){
		
		if($cid == 0) $this->error('参数错误');

		$this->display('product');

	}

	//picture
	public function picture($cid = 0){
		
		if($cid == 0) $this->error('参数错误');
		$this->display('picture');

	}
}

?>
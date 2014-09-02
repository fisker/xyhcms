<?php

class ModelAction extends CommonAction {
	
	//列表
	public function index() {

		$this->vlist = M('model')->order('sort')->select();
		$this->display();
	}

	public function add() {	
		if (IS_POST) {
			$this->addPost();
			exit();
		}	
		$this->styleListList = getFileFolderList(APP_PATH . C('APP_GROUP_PATH') . '/Home/Tpl/' .C('cfg_themestyle') , 2, 'List_*');
		$this->styleShowList = getFileFolderList(APP_PATH . C('APP_GROUP_PATH') . '/Home/Tpl/' .C('cfg_themestyle') , 2, 'Show_*');
		$this->display();
	}

	public function addPost() {

		//M验证
		$validate = array(
			array('name','require','名称必须填写！'), 
			array('name','','模型名称已经存在！',0,'unique',1), 
			array('tablename','require','附加表名必须填写！'), 
			array('tablename','','附加表名已经存在！',0,'unique',1),
			array('template_list','require','请选择列表模板'), 
			array('template_show','require','请选择内容页模板'),  
		);
		/*$auto = array ( 
			array('password','md5',1,'function') , 
			array('create_time','time',2,'function'), 
		);*/
		//M('model')->auto($auto)->validate($validate)->create();
		$data = M('model');
		$result = $data->validate($validate)->create();
		if (!$result){
            $this->error($data->getError());
        }else{
            if ($data->add($_POST)) {
            	$this->success('添加成功',U(GROUP_NAME. '/Model/index'));
            }else {
            	$this->error('添加失败');
            }
        }
	}


	//编辑
	public function edit() {
		if (IS_POST) {
			$this->editPost();
			exit();
		}
		$id = I('id', 0, 'intval');
		$data = M('model')->find($id);
		if (!$data) {
			$this->error('记录不存在');
		}
		$this->vo = $data;			
		$this->styleListList = getFileFolderList(APP_PATH . C('APP_GROUP_PATH') . '/Home/Tpl/' .C('cfg_themestyle') , 2, 'List_*');
		$this->styleShowList = getFileFolderList(APP_PATH . C('APP_GROUP_PATH') . '/Home/Tpl/' .C('cfg_themestyle') , 2, 'Show_*');
		$this->display();
	}



	//修改分类处理

	public function editPost() {

		$id = I('id',0, 'intval');
		$name = I('name', '', 'trim');
		$tablename = I('tablename', '', 'trim');
		$template_list = I('template_list', '', 'trim');
		$template_show = I('template_show', '', 'trim');

		if (empty($name)) {
			$this->error('模型名称不能为空！');
		}
		if (empty($template_list)) {
			$this->error('请选择列表模板');
		}
		if (empty($template_show)) {
			$this->error('请选择内容页模板');
		}

		if (M('model')->where(array('id' => array('neq', $id), array('tablename' => $tablename ,'name' => $name, '_logic' => 'OR')))->find()) {
			$this->error('模型名称或附加表已经存在！');
		}

		if (false !== M('model')->save($_POST)) {
			$this->success('修改成功',U(GROUP_NAME. '/Model/index'));
		}else {
			$this->error('修改失败');
		}
		
	}







	//批量更新排序
	public function sort() {

		$sortlist = I('sortlist', array(), 'intval');	
		foreach ($sortlist as $k => $v) {
			$data = array(
					'id' => $k,
					'sort' => $v,
				);
			M('model')->save($data);		
		}

		$this->redirect(GROUP_NAME. '/Model/index');
	}


	//彻底删除
	public function del() {

		$id = I('id',0 , 'intval');

		if (M('model')->delete($id)) {
			$this->success('删除成功', U(GROUP_NAME. '/Model/index', array('id' => $id)));
		}else {
			$this->error('删除失败');
		}
	}


}



?>
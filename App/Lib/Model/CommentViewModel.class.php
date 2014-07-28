<?php

//视图模型
class CommentViewModel extends ViewModel {
	
	protected $viewFields = array(
		'comment' => array('id', 'postid', 'modelid', 'title', 'username', 'email', 'ip',
		'agent', 'content', 'posttime', 'status', 'pid', 'userid',
		'_type' => 'LEFT'
		),
		'model' => array(
		'name' => 'modelname',
		'tablename' => 'tablename',
		'_on' => 'comment.modelid = model.id',//_on 对应上面LEFT关联条件
		'_type' => 'LEFT'
		),		
		'member' => array(
		'face' => 'face',//显示字段name as model
		'nickname' => 'nickname',//显示字段name as model
		'_on' => 'comment.userid = member.id',//_on 对应上面LEFT关联条件
		),

	);
}

?>
<?php

return array(

	'USER_AUTH_KEY'   => 'uid',			//用户认证识别号
	
	//加载自定义标签
	'TAGLIB_LOAD' => true,//加载标签库打开,好像不用也行
	'APP_AUTOLOAD_PATH' => '@.TagLib',//自动载入TagLib文件夹的文件
	'TAGLIB_PRE_LOAD'=>'yang,other',//预加载的tag名//使用方法：<yang:tagxxx />//不添加也行
	'TAGLIB_BUILD_IN' => 'Cx,yang,other', //作为内置标签引入//Cx为ThinkPHP核心标签(如foreach,if)，必须加上，yang为自定义标签库名称

	'DEFAULT_THEME'  => C('cfg_themestyle'),//默认主题风格
	//'TMPL_DETECT_THEME' => false, // 自动侦测模板主题
	//'THEME_LIST'=>'default,blog',//支持的模板主题项

	//设置URL_MODEL ,0普通模式 ,1:PATHINFO模式（默认模式）,2:REWRITE模式,
	//'URL_MODEL' =>0,//U方法生成的去掉了index.php
	//'URL_MODEL' => C('URL_MODEL__INDEX'),

	//不能在独立分组中使用URL_PATHINFO_DEPR

	//开启静态缓存
	'HTML_CACHE_ON' => C('HTML_CACHE_ON__INDEX'),


	'TMPL_PARSE_STRING' => array(
		'__PUBLIC__' => __ROOT__. '/Public/' . C('cfg_themestyle'),		
		'__DATA__' => __ROOT__. '/Data',
		'__AVATAR__' => __ROOT__. '/avatar',

	),

);

?>
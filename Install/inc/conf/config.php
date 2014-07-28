<?php
define('B_PIC',1);// 图片 
define('B_TOP',2);// 头条 (置顶)
define('B_REC',4);// 推荐 
define('B_SREC',8);// 特荐 
define('B_SLIDE',16);// 幻灯
define('B_JUMP',32);// 跳转  
define('B_OTHER',64);//其他

return array(
	//'配置项'=>'配置值'
	'APP_GROUP_LIST' => 'Home,Manage,Mobile',//分组列表,Index:前台,Manage:后台
	'DEFAULT_GROUP' => 'Home', //默认分组

	//启动独立分组模式
	'APP_GROUP_MODE' => 1, //默认是0(旧)，新式分组1[独立分组]
	//独立分组文件夹名称
	'APP_GROUP_PATH' => 'Group', //默认是Modules,新式才使用

	//模板路径(分组后，模板目录太深,可以设置此项简化)
	'TMPL_FILE_DEPR' => '_', // 控制器_方法.html, 控制器/方法.html[默认]
	//去掉伪静态后缀
	//'URL_HTML_SUFFIX' => '',
	'TMPL_STRIP_SPACE' => false,//是否去除模板文件里面的html空格与换行
	'TMPL_TEMPLATE_SUFFIX' => '.html',//模板后缀


	//显示页面调试信息
	'SHOW_PAGE_TRACE' => false,

	
	'TMPL_ACTION_ERROR'     =>  './Data/resource/system/jump.xyh', // error tpl
    'TMPL_ACTION_SUCCESS'   => './Data/resource/system/jump.xyh', //  success tpl
    'TMPL_EXCEPTION_FILE'   => './Data/resource/system/exception.xyh', //exception tpl


    //加载用户函数
    'LOAD_EXT_FILE' => 'func.other',
	//加载其他配置文件　
	'LOAD_EXT_CONFIG' => 'config.db,config.site,config.url,config.online',//加载扩展配置文件

	//设置URL_MODEL ,0普通模式 ,1:PATHINFO模式（默认模式）,2:REWRITE模式,
	//'URL_MODEL' =>0,//2\U方法生成的去掉了index.php

	//'URL_PATHINFO_DEPR' => '/',//参数之间的分割符号

	'URL_ROUTER_ON'   => false, //开启路由	
	//'URL_ROUTE_RULES' => array( //定义路由规则),
	

	'USER_DATA_PATH' => './Data',
	//系统备份数据库时每个sql分卷大小，单位字节 //5M=5*1024*1024=5242880
    'USER_SQL_FILESIZE' => 5242880, //值不可太大，否则会导致内存溢出备份、恢复失败，合理大小在512K~10M间，建议5M一卷
        
);
?>
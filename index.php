<?php
/**
 * @version        $Id: index.php 1 11:23 2014-04-09 yangyang $
 * @package        yangyang
 * @copyright      Copyright (c) 2014 - 2014.
 * @license        http://www.0871k.com/xyhcms
 * @link           http://www.0871k.com
 */

define('APP_DEBUG',false);//是否调试
define('APP_NAME', "App");//项目名称
define('APP_PATH', "./App/");//项目路径
define('THINK_PATH', "./Include/");

/**/
//判断是否安装
if(!file_exists(APP_PATH.'Conf/config.db.php'))
{
    header('Location:Install/index.php');
    exit();
}

require THINK_PATH.'ThinkPHP.php';//加载ThinkPHP框架


?>
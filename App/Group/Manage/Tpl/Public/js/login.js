function change_code(obj){
	$("#code").attr("src",verifyUrl+'#'+Math.random());
	return false;
}
//登录验证  1为空   2为错误

$(function(){
	
	var $msg = $('.msg');	
	var username=$("input[name='username']");
	var password = $("input[name='password']");
	var code = $("input[name='code']");
	$("#login").submit(function(){
		
		if($.trim(username.val())==''){
			$msg.html("<span class='error'>用户名不能为空</span>");
			username.focus();
			return false;
		}else if($.trim(password.val())=='') {			
			$msg.html("<span class='error'>密码不能为空</span>");
			password.focus();
			return false;
		}else if($.trim(code.val())==''){
			$msg.html("<span class='error'>验证码不能为空</span>");
			code.focus();
			return false;
		}else{
			$msg.html(' ');
			return true;
		}
		
		return false;
	});



	//验证用户名
	$("input[name='username']").blur(function(){
		if($.trim(username.val())==''){
			$msg.html("<span class='error'>用户名不能为空</span>");
			username.focus();
			return ;
		}else {			
			$msg.html("");
		}
		
	});
	//验证密码
	$("input[name='password']").blur(function(){
		var username=$("input[name='username']");
		if($.trim(password.val())==''){
			$msg.html("<span class='error'>密码不能为空</span>");
			//password.focus();
			return ;
		}else {			
			$msg.html("");
		}
		
	});
});


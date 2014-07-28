$(function(){
	//当前页面配置
	/* 
	引用页中设置此项
	var get_review_url = '';//提交评论地址
	var get_review_url = '';/评论点载地址
	*/
    //获取评论,当评论的div存在的时候
    if($(".more-comment").length != 0) {
        get_review();
    }   
        
    //阻止事件的冒泡
    $(':text,textarea').keyup(function(event){
            event.stopPropagation();
        });
        
        //快捷键提交评论
        $("#reviewForm").find('textarea').on("keydown", function(e){
            e.stopPropagation();
            if(e.ctrlKey && e.which ==13){
                $('#reviewForm').submit();
            }
        });
    
    });


    $('.comment-textarea textarea').keyup(function(){
        var self = $(this);
        var speed = Math.max(self.get(0).scrollHeight, 48);
        self.height(speed);
    });

    //表单移动
    var form = $('.comment-item:last');
    //插入回复表单
    $('.comment-box').delegate('.reply-btn','click',function(event){
        var parent = $(this).closest('.comment-item');
        //写入对应回复ID
        form.find(':hidden[name=review_id]').val($(this).attr('reply'));
        var textarea = parent.append(form).find('textarea');
        //对回复回复的处理
        //textarea.text(''); 
		//对回复回复的处理
		if($(this).attr('at-user') == 'true'){
			var username = $(this).parent().find('.username').text();
			textarea.text('回复 @' + username + ' : ');
		}else{
			textarea.text('');
		}
        moveEnd(textarea.get(0));
        event.stopPropagation();
    })
    $('.comment-box').delegate('.comment-item','click',function(event){
        $(this).has('form').length && event.stopPropagation();
    })
    //点击评论框以外的地方，重置评论表单
    $(document).click(function(){
        if(form.find(':hidden[name=review_id]').val() != 0){
            $('.more-comment').after($('.comment-box').find('form'));
            form.find(':hidden[name=review_id]').val(0);
            form.find('textarea').text('');
        }
    })

    //表单提交
    //var post_review_url ='';//所引用页中必需得新设置此项

    $('#reviewForm').submit(function(){
    	var comment_btn = $(".comment-ft");
    	if (typeof(post_review_url)=="undefined") {
    		//alert('post_review_url 未定义');
    		comment_btn.find("span").remove().end().append('<span class="error">js错误：post_review_url 未定义</span>');
    		return false;
    	}

   		if (post_review_url == '') {
   			//alert('提交网址错误');
    		comment_btn.find("span").remove().end().append('<span class="error">js错误：post_review_url 未定义</span>');
    		return false;
    	}
        var content = $("textarea[name='content']");
        
        if($.trim(content.val())==''){
            comment_btn.find("span").remove().end().append("<span class='error'>内容不能为空</span>");
            return false;
        }else {
            comment_btn.find("span").remove();
        }
        
        
        var _postForm = $('#reviewForm').serialize();
        $.post(post_review_url,_postForm,function(data){
            if(data.status == 1) {
                if (data.review_id == 0) {
                     comment_btn.find("span").remove().end().append("<span>评论成功</span>");
                     var html = '<div class="comment-item review_item_list">'+
                                '<a class="avatar" user_id="'+data.user_id+'" href="#" target="_blank">'+
                                '<img src="'+ data.avatar+'" />'+
                                '</a> '+
                                '<div class="comment-hd">';                           
                    html +='<a class="reply-btn" href="javascript:;" reply="'+ data.id +'">回复<i></i></a>';
                    html += '<span class="username">' + data.username + data.ico + '</span>' + 
                            '<span class="commment-time">' + data.posttime + '</span>'+
                            '</div>'+
                            '<div class="comment-bd" id="' + data.id + '">'+ content.val() + 
                            '</div>'+
                            '</div>';
                    $('.comment-box h3').after(html);
                    $('.review-count').text(parseInt($('.review-count').text())+1);
                }else {

                    var html = '<div class="comment-item reply-item" id="' + data.id + '">'+
                            '<a class="avatar" user_id="'+data.user_id+'" href="#" target="_blank">'+
                                '<img src="'+ data.avatar+'" />'+
                            '</a>'+
                            '<div class="comment-hd"><a class="reply-btn" href="javascript:;" reply="'+data.review_id+'">回复<i></i></a>';
                        
                        html += '<span class="username">' + data.username + data.ico + '</span>' + 
                            '<span class="commment-time">' + data.posttime + '</span>'+
                            '</div>'+
                            '<div class="comment-bd">'+
                            '<div>' + data.content + '</div>'+
                            '</div>'
                        '</div>';
                        //$('#' + data.review_id).after(html);
                        $('#reviewForm').before(html);
                }
               
                /*
                //移到最下面，不然，会被删除，不能获取评论了
                if(form.find(':hidden[name=review_id]').val() != 0){
                    $('.more-comment').after($('.comment-box').find('form'));
                    form.find(':hidden[name=review_id]').val(0);
                    form.find('textarea').text('');
                }
                $('.review_item_list').remove();
                //重新加载评论
                page = 1;
                get_review();
                */
                content.val('');

                return false;
            } else if(data.status == 0) {
                comment_btn.find("span").remove().end().append("<span class='error'>"+ data.info +"</span>");
                //alert(data);
            } else {
                comment_btn.find("span").remove().end().append("<span class='error'>--</span>");
                
            }
        },'json');
        
        return false;
    });

    
        
        
    //评论加载
    //var get_review_url = '';//所引用页中必需得新设置此项
    var page =1;
    function get_review(){
    	if (typeof(get_review_url)=="undefined") {
    		//alert('get_review_url 未定义');    		
    		$('.more-comment').before('<p class="error">js加载错误：get_review_url 未定义</p>');
    		return false;
    	}
    	if (get_review_url == '') {
    		//alert('加载错误');
    		$('.more-comment').before('<p class="error">js加载错误：get_review_url</p>');
    		return false;
    	}
        $.get(get_review_url,
            {
                'model_id' : $(':input[name=model_id]').val(), 
                'post_id' : $(':input[name=post_id]').val(), 
                'num' : 5, 
                'page' : page,
                'avatar' : 'middle'
            },
            function(data){
                //是否登录做对应展示
                if(data.user_id != 0){
                    $('#my_avatar').attr('src', data.avatar);
                    $('#reviewForm').show();
                    $('.login-tip').hide();
                }else{
                    $('#my_avatar').attr('src', data.avatar);
                    if (data.guest != 1) {
                        $('#reviewForm').hide();
                    }                   
                    $('.login-tip').show();
                    
                }
                
                $.isNumeric(data.count) && $('.review-count').text(data.count);
                //$('#comment_count').text(data.count);
                if(data.list && (typeof data.list == 'object')){
                    $.each(data.list, function(i, v){
                        var html = '<div class="comment-item review_item_list">'+
                            '<a class="avatar" user_id="'+v.user_id+'" href="#" target="_blank">'+
                                '<img src="'+ v.avatar+'" />'+
                            '</a> '+
                            '<div class="comment-hd">';                           
                            html +='<a class="reply-btn" href="javascript:;" reply="'+v.id+'">回复<i></i></a>';
                            html += '<span class="username">' + v.username + v.ico + '</span>' + 
                            '<span class="commment-time">' + v.posttime + '</span>'+
                            '</div>'+
                            '<div class="comment-bd" id="' + v.id + '">'+ v.content + 
                            '</div>'+
                        '</div>';
                        $('.more-comment').before(html);
                    });
                    page = page+1;
                }
                if(data.review && (typeof data.review == 'object')){

                    $.each(data.review, function(i, v){
                        var html = '<div class="comment-item reply-item" id="' + v.id + '">'+
                            '<a class="avatar" user_id="'+v.user_id+'" href="#" target="_blank">'+
                                '<img src="'+ v.avatar+'" />'+
                            '</a>'+
                            '<div class="comment-hd"><a class="reply-btn" href="javascript:;" reply="'+v.review_id+'" at-user="true">回复<i></i></a>';
                        
                        html += '<span class="username">' + v.username + v.ico + '</span>' + 
                            '<span class="commment-time">' + v.posttime + '</span>'+
                            '</div>'+
                            '<div class="comment-bd">'+
                            '<div>' + v.content + '</div>'+
                            '</div>'
                        '</div>';
                        $('#' + v.review_id).after(html);
                    });
                }
                var review_count = data.count;
                if($('.review_item_list').length < review_count){
                    $('#more_count').text(review_count - $('.review_item_list').length);
                    $('.more-comment').show();
                }else{
                    $('.more-comment').hide();
                }
                               
            },
            'json'
        );
        
    }


    //将光标移动到textarea末尾
    function moveEnd(obj){
		if(obj==null) return false;
        if(obj.offsetWidth<=0&&obj.offsetHeight<=0) {//隐藏时，不能focus
			return false;
		}
		
		obj.focus();
        var len = obj.value.length;
        if (document.selection) {
            var sel = obj.createTextRange(); 
            sel.moveStart('character',len); 
            sel.collapse(); 
            sel.select(); 
        } else if (typeof obj.selectionStart == 'number' && typeof obj.selectionEnd == 'number') {
            obj.selectionStart = obj.selectionEnd = len; 
        } 
    }

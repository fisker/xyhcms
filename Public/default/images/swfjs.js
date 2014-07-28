function changeimg(n)
{
	adNum=n;
	window.clearInterval(theTimer);
	adNum=adNum-1;
	TimeOut = 5000;
	nextAd();
	firstTime=false;
	document.getElementById("imgstart").style.display="none";
	document.getElementById("imgstop").style.display="";
}
function goUrl(){
  if(imgLink[adNum]=="#") {
  } else {
	window.open(imgLink[adNum],'_blank');
  }
}

var count=0;
for (i=1;i<imgUrl.length;i++) {
	if( (imgUrl[i]!="") && (imgLink[i]!="")&&(imgtext[i]!="")&&(imgAlt[i]!="") ) {
		count++;
	} else {
		break;
	}
}
function playTran(){
	//if (document.all) {
		if ( imgInit.filters.item( 0 ).Transition == 23 ){ // ıлʽ23ʽѭ
			imgInit.filters.item( 0 ).Transition = 1 ;
		}else{
			imgInit.filters.item( 0 ).Transition++ ;
		}
		imgInit.filters.item( 0 ).apply();
		imgInit.filters.item( 0 ).play();
		//imgInit.filters.revealTrans.play();
	//}
}
var key=0;

var nextAd = new function(){};

function nextAdNetscape(){
	if(TimeOut==5000) {
		if(adNum<count)adNum++ ;
		else adNum=1;

		document.images.imgInit.src=imgUrl[adNum];
		document.images.imgInit.alt=imgAlt[adNum];
		document.getElementById('focustext').innerHTML=imgtext[adNum];
		//document.getElementById('advtitle').innerHTML=imgtext[adNum];
		//document.getElementById('link'+adNum).style.backgroundColor=buttonLineOn;
		document.getElementById('imglink'+adNum).src="swfimages/num_"+adNum+"_on.gif";
		//document.getElementById('advtd'+adNum).style.backgroundColor="#ff8500";
		for (var i=1;i<=count;i++)
		{
			if (i!=adNum){
			  //document.getElementById('link'+i).style.backgroundColor=buttonLineOff;
			  document.getElementById('imglink'+i).src="swfimages/num_"+i+"_off.gif";
			  //document.getElementById('advtd'+i).style.backgroundColor="#393939";
			}
		}
	}
	theTimer=setTimeout("nextAdNetscape()", TimeOut);
}
function nextAdIE(){
	if(TimeOut==5000) {
		if(adNum<count)adNum++ ;
		else adNum=1;

		if( key==0 ){
			key=1;
		} else if (document.all){
			playTran();
		}
		document.images.imgInit.src=imgUrl[adNum];
		document.images.imgInit.alt=imgAlt[adNum];
		/*if(firstTime){}else{
			document.getElementById('advtitle').innerHTML=imgtext[adNum];
		}*/
		//document.getElementById('link'+adNum).style.backgroundColor=buttonLineOn;
		document.getElementById('imglink'+adNum).src="swfimages/num_"+adNum+"_on.gif";
		//document.getElementById('advtd'+adNum).style.backgroundColor="#ff8500";
		for (var i=1;i<=count;i++)
		{
			if (i!=adNum){
			  //document.getElementById('link'+i).style.backgroundColor=buttonLineOff;
			  document.getElementById('imglink'+i).src="swfimages/num_"+i+"_off.gif";
			  //document.getElementById('advtd'+i).style.backgroundColor="#393939";
			}
		}
		focustext.innerHTML=imgtext[adNum];
	}
	theTimer=setTimeout("nextAdIE()", TimeOut);
}

function start(){
	TimeOut = 5000;
	theTimer = setTimeout("nextAd()", TimeOut);
	document.getElementById("imgstart").style.display="none";
	document.getElementById("imgstop").style.display="";
}

function stop(){
	TimeOut = 5000*1000;
	document.getElementById("imgstart").style.display="";
	document.getElementById("imgstop").style.display="none";
}

//NetScapeʼ
if (navigator.appName == "Netscape")
{
nextAd = nextAdNetscape;
document.write('<style type="text/css">');
document.write('.buttonDiv{height:4px;width:21px;}');
document.write('</style>');
document.write('<a target=_self href="javascript:goUrl()"><img style="FILTER: revealTrans(duration=1,transition=5);" src="javascript:nextAd()" width='+imgWidth+' height='+imgHeight+' border=0 vspace="0" name=imgInit class="imgClass"></a>');
document.write('<div id="txtFrom"><span id="focustext" class="'+textStyle+'"></span></div>');
document.write('<div id="imgTitle">');
document.write(' <div id="imgTitle_down">');
//ְť뿪ʼ
//for(var i=1;i<imgUrl.length;i++){document.write('<a id="link'+i+'"  href="javascript:changeimg('+i+')" class="button" style="cursor:hand" title="'+imgAlt[i]+'" onFocus="this.blur()">'+i+'</a>');}
for(var j=1;j<imgUrl.length;j++){document.write('&nbsp;<a href="javascript:changeimg('+j+')" style="cursor:hand" title="'+imgAlt[j]+'" onFocus="this.blur()"><img id="imglink'+j+'" src="swfimages/num_'+j+'_on.gif" border="0"></a>');}
document.write(' ');
document.write('<a href="javascript:start();" style="cursor:hand" title="ʼ" onFocus="this.blur()"><img id="imgstart" src="swfimages/num_play.gif" border="0" style="display:none;"></a>');
document.write('<a href="javascript:stop();" style="cursor:hand" title="ͣ" onFocus="this.blur()"><img id="imgstop" src="swfimages/num_stop.gif" border="0"></a>');
//ְť
document.write('</div>');
document.write('</div>');
document.write('</div>');
nextAd();
}
//NetScape
//IEʼ
else
{
nextAd = nextAdIE;
document.write('<a target=_self href="javascript:goUrl()"><img style="FILTER: revealTrans(duration=1,transition=5);" src="javascript:nextAd()" width='+imgWidth+' height='+imgHeight+' border=0 vspace="0" name=imgInit class="imgClass"></a>');
document.write('<div id="txtFrom"><span id="focustext" class="'+textStyle+'"></span></div>');
document.write('<div id="imgTitle">');
document.write(' <div id="imgTitle_down">');
//ְť뿪ʼ
//for(var i=1;i<imgUrl.length;i++){document.write('<a id="link'+i+'"  href="javascript:changeimg('+i+')" class="button" style="cursor:hand" title="'+imgAlt[i]+'" onFocus="this.blur()">'+i+'</a>');}
for(var j=1;j<imgUrl.length;j++){document.write('&nbsp;<a href="javascript:changeimg('+j+')" style="cursor:hand" title="'+imgAlt[j]+'" onFocus="this.blur()"><img id="imglink'+j+'" src="images/num_'+j+'_on.gif" border="0"></a>');}
document.write(' ');
document.write('<a href="javascript:start();" style="cursor:hand" title="ʼ" onFocus="this.blur()"><img id="imgstart" src="swfimages/num_play.gif" border="0" style="display:none;"></a>');
document.write('<a href="javascript:stop();" style="cursor:hand" title="ͣ" onFocus="this.blur()"><img id="imgstop" src="swfimages/num_stop.gif" border="0"></a>');
//ְť
document.write('</div>');
document.write('</div>');
document.write('</div>');
changeimg(1)
}
//IE

function simplePreload()
{
  var args = simplePreload.arguments;
  document.imageArray = new Array(args.length);
  for(var i=0; i<args.length; i++)
  {
    document.imageArray[i] = new Image;
    document.imageArray[i].src = args[i];
  }
}
simplePreload();

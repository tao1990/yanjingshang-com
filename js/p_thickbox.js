var tb_pathToImage="themes/default/images/loadingAnimation.gif";$(document).ready(function(){tb_init('a.thickbox, area.thickbox, input.thickbox');imgLoader=new Image();imgLoader.src=tb_pathToImage});function tb_init(domChunk){$(domChunk).click(function(){var t=this.title||this.name||null;var a=this.href||this.alt;var g=this.rel||false;tb_show(t,a,g);this.blur();return false})}function tb_show(caption,url,imageGroup){try{if(typeof document.body.style.maxHeight==="undefined"){if(document.getElementById("TB_HideSelect")===null){$("body").append("<iframe id='TB_HideSelect'></iframe><div id='TB_overlay'></div><div id='TB_window'></div>");$("#TB_overlay").click(tb_remove)}}else{if(document.getElementById("TB_overlay")===null){$("body").append("<div id='TB_overlay'></div><div id='TB_window'></div>");$("#TB_overlay").click(tb_remove)}}if(tb_detectMacXFF()){$("#TB_overlay").addClass("TB_overlayMacFFBGHack")}else{$("#TB_overlay").addClass("TB_overlayBG")}if(caption===null){caption=""}$("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' /></div>");$('#TB_load').show();var baseURL;if(url.indexOf("?")!==-1){baseURL=url.substr(0,url.indexOf("?"))}else{baseURL=url}var urlString=/\.jpg$|\.jpeg$|\.png$|\.gif$|\.bmp$/;var urlType=baseURL.toLowerCase().match(urlString);if(urlType=='.jpg'||urlType=='.jpeg'||urlType=='.png'||urlType=='.gif'||urlType=='.bmp'){TB_PrevCaption="";TB_PrevURL="";TB_PrevHTML="";TB_NextCaption="";TB_NextURL="";TB_NextHTML="";TB_imageCount="";TB_FoundURL=false;if(imageGroup){TB_TempArray=$("a[@rel="+imageGroup+"]").get();for(TB_Counter=0;((TB_Counter<TB_TempArray.length)&&(TB_NextHTML===""));TB_Counter++){var urlTypeTemp=TB_TempArray[TB_Counter].href.toLowerCase().match(urlString);if(!(TB_TempArray[TB_Counter].href==url)){if(TB_FoundURL){TB_NextCaption=TB_TempArray[TB_Counter].title;TB_NextURL=TB_TempArray[TB_Counter].href;TB_NextHTML="<span id='TB_next'>&nbsp;&nbsp;<a href='#'>下一页 &gt;</a></span>"}else{TB_PrevCaption=TB_TempArray[TB_Counter].title;TB_PrevURL=TB_TempArray[TB_Counter].href;TB_PrevHTML="<span id='TB_prev'>&nbsp;&nbsp;<a href='#'>&lt; 上一页</a></span>"}}else{TB_FoundURL=true;TB_imageCount="Image "+(TB_Counter+1)+" of "+(TB_TempArray.length)}}}imgPreloader=new Image();imgPreloader.onload=function(){imgPreloader.onload=null;var pagesize=tb_getPageSize();var x=pagesize[0]-150;var y=pagesize[1]-150;var imageWidth=imgPreloader.width;var imageHeight=imgPreloader.height;if(imageWidth>x){imageHeight=imageHeight*(x/imageWidth);imageWidth=x;if(imageHeight>y){imageWidth=imageWidth*(y/imageHeight);imageHeight=y}}else if(imageHeight>y){imageWidth=imageWidth*(y/imageHeight);imageHeight=y;if(imageWidth>x){imageHeight=imageHeight*(x/imageWidth);imageWidth=x}}TB_WIDTH=imageWidth+30;TB_HEIGHT=imageHeight+60;$("#TB_window").append("<a href='' id='TB_ImageOff' title='关闭'><img id='TB_Image' src='"+url+"' width='"+imageWidth+"' height='"+imageHeight+"' alt='"+caption+"'/></a>"+"<div id='TB_caption'>"+caption+"<div id='TB_secondLine'>"+TB_imageCount+TB_PrevHTML+TB_NextHTML+"</div></div><div id='TB_closeWindow'><a href='#' id='TB_closeWindowButton' title='关闭'>关闭</a>222 1或者Esc1键</div>");$("#TB_closeWindowButton").click(tb_remove);if(!(TB_PrevHTML==="")){function goPrev(){if($(document).unbind("click",goPrev)){$(document).unbind("click",goPrev)}$("#TB_window").remove();$("body").append("<div id='TB_window'></div>");tb_show(TB_PrevCaption,TB_PrevURL,imageGroup);return false}$("#TB_prev").click(goPrev)}if(!(TB_NextHTML==="")){function goNext(){$("#TB_window").remove();$("body").append("<div id='TB_window'></div>");tb_show(TB_NextCaption,TB_NextURL,imageGroup);return false}$("#TB_next").click(goNext)}document.onkeydown=function(e){if(e==null){keycode=event.keyCode}else{keycode=e.which}if(keycode==27){tb_remove()}else if(keycode==190){if(!(TB_NextHTML=="")){document.onkeydown="";goNext()}}else if(keycode==188){if(!(TB_PrevHTML=="")){document.onkeydown="";goPrev()}}};tb_position();$("#TB_load").remove();$("#TB_ImageOff").click(tb_remove);$("#TB_window").css({display:"block"})};imgPreloader.src=url}else{var queryString=url.replace(/^[^\?]+\??/,'');var params=tb_parseQuery(queryString);TB_WIDTH=(params['width']*1)+30||630;TB_HEIGHT=(params['height']*1)+40||440;ajaxContentW=TB_WIDTH;ajaxContentH=TB_HEIGHT;if(url.indexOf('TB_iframe')!=-1){urlNoQuery=url.split('TB_');$("#TB_iframeContent").remove();if(params['modal']!="true"){$("#TB_window").append("<div id='TB_title'><div id='TB_ajaxWindowTitle'>"+caption+"</div><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton' title='关闭'>关闭</a> 2或者Esc键2</div></div><iframe frameborder='0' hspace='0' src='"+urlNoQuery[0]+"' id='TB_iframeContent' name='TB_iframeContent"+Math.round(Math.random()*1000)+"' onload='tb_showIframe()' style='width:"+(ajaxContentW+29)+"px;height:"+(ajaxContentH+17)+"px;' > </iframe>")}else{$("#TB_overlay").unbind();$("#TB_window").append("<iframe frameborder='0' hspace='0' src='"+urlNoQuery[0]+"' id='TB_iframeContent' name='TB_iframeContent"+Math.round(Math.random()*1000)+"' onload='tb_showIframe()' style='width:"+(ajaxContentW+29)+"px;height:"+(ajaxContentH+17)+"px;'> </iframe>")}}else{if($("#TB_window").css("display")!="block"){if(params['modal']!="true"){$("#TB_window").append("<div id='TB_title'><div id='TB_ajaxWindowTitle'>"+caption+"</div><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton'></a></div></div><div id='TB_ajaxContent' style='width:"+ajaxContentW+"px;height:"+ajaxContentH+"px'></div>")}else{$("#TB_overlay").unbind();$("#TB_window").append("<div id='TB_ajaxContent' class='TB_modal' style='width:"+ajaxContentW+"px;height:"+ajaxContentH+"px;'></div>")}}else{$("#TB_ajaxContent")[0].style.width=ajaxContentW+"px";$("#TB_ajaxContent")[0].style.height=ajaxContentH+"px";$("#TB_ajaxContent")[0].scrollTop=0;$("#TB_ajaxWindowTitle").html(caption)}}$("#TB_closeWindowButton").click(tb_remove);if(url.indexOf('TB_inline')!=-1){$("#TB_ajaxContent").append($('#'+params['inlineId']).children());$("#TB_window").unload(function(){$('#'+params['inlineId']).append($("#TB_ajaxContent").children())});tb_position();$("#TB_load").remove();$("#TB_window").css({display:"block"})}else if(url.indexOf('TB_iframe')!=-1){tb_position();if($.browser.safari){$("#TB_load").remove();$("#TB_window").css({display:"block"})}}else{$("#TB_ajaxContent").load(url+="&random="+(new Date().getTime()),function(){tb_position();$("#TB_load").remove();tb_init("#TB_ajaxContent a.thickbox");$("#TB_window").css({display:"block"})})}}if(!params['modal']){document.onkeyup=function(e){if(e==null){keycode=event.keyCode}else{keycode=e.which}if(keycode==27){tb_remove()}}}}catch(e){}}function tb_showIframe(){$("#TB_load").remove();$("#TB_window").css({display:"block"})}function tb_remove(){$("#TB_imageOff").unbind("click");$("#TB_closeWindowButton").unbind("click");$("#TB_window").fadeOut("fast",function(){$('#TB_window,#TB_overlay,#TB_HideSelect').trigger("unload").unbind().remove()});$("#TB_load").remove();if(typeof document.body.style.maxHeight=="undefined"){$("body","html").css({height:"auto",width:"auto"});$("html").css("overflow","")}document.onkeydown="";document.onkeyup="";return false}function tb_position(){$("#TB_window").css({marginLeft:'-'+parseInt((TB_WIDTH/2),10)+'px',width:TB_WIDTH+'px'});if(!window.XMLHttpRequest){$("#TB_window").css("marginTop",$(document).scrollTop())}else{$("#TB_window").css({marginTop:'-'+parseInt((TB_HEIGHT/2),10)+'px'})}}function tb_parseQuery(query){var Params={};if(!query){return Params}var Pairs=query.split(/[;&]/);for(var i=0;i<Pairs.length;i++){var KeyVal=Pairs[i].split('=');if(!KeyVal||KeyVal.length!=2){continue}var key=unescape(KeyVal[0]);var val=unescape(KeyVal[1]);val=val.replace(/\+/g,' ');Params[key]=val}return Params}function tb_getPageSize(){var de=document.documentElement;var w=window.innerWidth||self.innerWidth||(de&&de.clientWidth)||document.body.clientWidth;var h=window.innerHeight||self.innerHeight||(de&&de.clientHeight)||document.body.clientHeight;arrayPageSize=[w,h];return arrayPageSize}function tb_detectMacXFF(){var userAgent=navigator.userAgent.toLowerCase();if(userAgent.indexOf('mac')!=-1&&userAgent.indexOf('firefox')!=-1){return true}}
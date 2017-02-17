/*----------分享到-------------------------------*/
/*----------------------------------------------*/

function ShareCode(server_url, server_icon_url,text){   
    var title = encodeURIComponent(document.title.substring(0,76)); 
	//要分享的当前网页url  
    var url = encodeURIComponent(location.href);   
    server_url = server_url.replace("{title}",title);   
    server_url = server_url.replace("{url}",url);   
    return "<a href='\' mce_href="\""javascript:window.open(\'"   
    + server_url    
    +"',\'_blank\',\'scrollbars=no,width=600,height=450,left=75,top=20,status=no,resizable=yes\'); void 0\" style="\" mce_style="\""color:#000000;text-decoration:none;font-size:12px;font-weight:normal\"><SPAN style="\" mce_style="\""PADDING-RIGHT: 2px; PADDING-LEFT: 2px; FONT-SIZE: 12px; PADDING-BOTTOM: 0px; MARGIN-LEFT: 2px; CURSOR: pointer; PADDING-TOP: 5px\"><IMG alt="    
    + text + " src="\" mce_src="\"""    
    + server_icon_url    
    + "\" align=absMiddle border=0> "    
    + text + "<\/SPAN><\/a>";
	  
}   
function WriteSNS()   
{   
    document.writeln("<div id=\"socialbookmark\">");   
       
    document.writeln(ShareCode("http://www.douban.com/recommend/?url={url}&title={title}",   
    "http://t.douban.com/favicon.ico",   
    "推荐到豆瓣"));   
       
    document.writeln(ShareCode("http://apps.hi.baidu.com/share/?title={title}&url={url}",   
    "http://www.baidu.com/favicon.ico",   
    "转帖到百度空间"));   
       
    document.writeln(ShareCode("http://v.t.sina.com.cn/share/share.php?title={title}&url={url}",   
    "http://t.sina.com.cn/favicon.ico",   
    "转发到新浪微博"));   
       
    document.writeln(ShareCode("http://www.kaixin001.com/repaste/share.php?rtitle={title}&rurl={url}",   
    "http://img1.kaixin001.com.cn/i/favicon.ico",   
    "转贴到开心网"));   
       
    document.writeln(ShareCode("http://share.renren.com/share/buttonshare.do?title={title}&link={url}",   
    "http://s.xnimg.cn\/favicon-rr.ico",   
    "转帖到人人网"));
       
    document.writeln("</div>");   
};  

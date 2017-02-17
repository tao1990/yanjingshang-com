function Pics(opts){	
	// id
	this.id=opts.id;
	// picer
	this.picer=document.getElementById(this.id);
	// elem
	this.elem=opts.elem || this.picer.getElementsByTagName('ul')[0];
	// 背景
	this.fullpage=typeof opts.fullpage!='undefined' ? opts.fullpage : null;
	// 状态
	this.state=typeof opts.state!='undefined' ? opts.state : 1;
	// 位置
	this.index=typeof opts.index!='undefined' ? opts.index : 0;
	// 箭头
	this.arrow=typeof opts.arrow!='undefined' ? opts.arrow : null;
	// 单位宽度
	this.width=opts.width || this.picer.clientWidth;
	// 数量
	this.count=opts.count || this.picer.getElementsByTagName('li').length;
	// 是否可循环
	this.cycle=typeof opts.cycle!='undefined' ? opts.cycle : true;
	// 是否自动播放
	this.auto=typeof opts.auto!='undefined' ? opts.auto : false; 
	// 回调函数
	this.callback=typeof opts.callback!='undefined' ? opts.callback : null;
	this.init.apply(this,arguments);
}

Pics.prototype.init=function(){
	var arrow=this.arrow,
		next=this.next,
		back=this.back,
		self=this;
	if(this.state===0){
		this.close();
	}
	if(this.arrow){
		this.arrow[0].addEventListener('click',back.bind(self),false);
		this.arrow[1].addEventListener('click',next.bind(self),false);
	}
	this.elem.style.left=-this.index*parseInt(this.width)+"px";
	this.isAuto();
}

Pics.prototype.open=function(){
	if(this.fullpage){
		this.fullpage.style.display="block";
		this.fullpage.style.opacity="1";
		this.fullpage.classList.add('ani');
		this.picer.classList.add('ani');
	}else{
		this.picer.style.display="block";
		this.picer.style.opacity="1";
		this.picer.classList.add('ani');
	}
}

Pics.prototype.close=function(){
	if(this.fullpage){
		this.fullpage.style.display="none";
		this.fullpage.style.opacity="0";
		this.fullpage.classList.remove('ani');
		this.picer.classList.remove('ani');
	}else{
		this.picer.style.display="none";
		this.picer.style.opacity="0";
		this.picer.classList.remove('ani');
	}
}

Pics.prototype.next=function(){
	if(this.timer) clearInterval(this.timer);
	if(this.cycle){
		if(this.index>=this.count-1){
			this.index=0;
		}else{
			this.index++;
		}
	}else{
		if(this.index>=this.count-1){
			this.index=this.count-1;
		}else{
			this.index++;
		}
	}
	this.go(this.index);
	this.isAuto();
}

Pics.prototype.back=function(){
	if(this.timer) clearInterval(this.timer);
	if(this.cycle){
		if(this.index<=0){
			this.index=this.count-1;
		}else{
			this.index--;
		}
	}else{
		if(this.index<=0){
			this.index=0;
		}else{
			this.index--;
		}
	}
	this.go(this.index);
	this.isAuto();
}

Pics.prototype.go=function(n){
	var index=this.index=n,
		width=this.width,
		elem=this.elem,
		callback=this.callback,
		count=this.count;
	$(elem).stop(true,true).animate({"left":-index*parseInt(width)+"px"},500,function(){
		if(callback){
			callback(n,count);
		}
	});
}

Pics.prototype.isAuto=function(){
	if(this.auto){
		var auto=this.auto,
			next=this.next,
			self=this;
		this.timer=setInterval(next.bind(self),auto);
	}
}

Pics.init=function(opts){
	return new Pics(opts);
}

window.Pics=Pics;
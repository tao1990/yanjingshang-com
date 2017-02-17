function Folder(opts){
	this.bar=opts.bar;
	this.elem=opts.elem;
	this.state=opts.state===1?1:0;
	this.init.apply(this,arguments);
}

Folder.prototype.init=function(){
	// นุมช
	var bar=this.bar,
		elem=this.elem,
		self=this;
	(this.state===0) ? function(){
		bar.classList.add('off');
		self.off(bar,elem);
	}():function(){
		bar.classList.add('on');
		self.on(bar,elem);
	}();

	bar.addEventListener('click',function(){
		self.toggle(bar,elem);
	},false);
}

Folder.prototype.on=function(bar,elem){
	bar.classList.remove('off');
	bar.classList.add('on');
	elem.style.display="block";
	this.state=1;
}

Folder.prototype.off=function(bar,elem){
	bar.classList.remove('on');
	bar.classList.add('off');
	elem.style.display="none";
	this.state=0;
}

Folder.prototype.toggle=function(bar,elem){
	if(this.state===0){
		this.on(bar,elem);
	}else if(this.state===1){
		this.off(bar,elem);
	}
}
function ChooseInfo(opts){
    var opts=opts||{};
    this.btns=Array.prototype.slice.call(opts.btns,0)||null;
    this.chooseInfo=opts.chooseInfo||null;
    this.init.apply(this,arguments);
}
ChooseInfo.prototype.scan=function(){
    if(this.data){
        this.chooseInfo.innerHTML="已选 "+this.data;
    }else{
        this.chooseInfo.innerHTML="请选择";
    }
}
ChooseInfo.prototype.init=function(){
    this.data="";
    this.scan();
    this.binder();
}
ChooseInfo.prototype.binder=function(){
    var that=this;
    that.btns.forEach(function(btn){
        btn.addEventListener("click",function(){
            that.data=this.getElementsByTagName("input")[0].value;
            that.scan();
        },false);
    })
}
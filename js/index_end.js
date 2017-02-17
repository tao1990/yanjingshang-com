/*===============================================首页-焦点图片控制 2011-6-9=========================================*/
function PicSlide(panel,opt){
    this.panel = typeof panel=="string"? document.getElementById(panel): panel;
    for(var k in opt)this[k]=opt[k]
    this.init()
};
PicSlide.prototype={
    current:0,
    timer:null,
    time:500,
    type:'scroll',
    act: 'scroll',
    interval:4000,
    init:function (){
        var _this=this,world=this.panel.parentNode;
        this.imgs=this.panel.getElementsByTagName('A');
        for(var i=0,l=this.imgs.length;i<l;i++)
            this.imgs[i].style.cssText='position:absolute;display:'+(i==0?'':'none');
        this.createTab();
        this.interval=Math.max(this.interval,this.time);
        world.onmouseover=function (){_this.hover=true};
        world.onmouseout =function (){_this.hover=false};
        this.auto()
    },
    createTab:function (){
        var len=this.imgs.length,btn,_this=this;
        this.nav=document.getElementById('ad_nav');
        this.btns=this.nav.getElementsByTagName('A');
        for(var i=0;i<len;i++){
            btn=this.btns[i];
            if(i==0)btn.className='hot';
            btn.radioIndex=i;
            btn.onmouseover=function (){_this.focus(this.radioIndex)}
        }
    },
    focus:function (next){
        next=next%this.imgs.length;
        if(next==this.current)return;
        this.btns[this.current].className='';
        this.btns[next].className='hot';
        this.fade(next);
    },
    fade:function (next){
        var _this=this;
        clearInterval(this.timer);
        this.timer=this.fx(1,0,function (x){
           _this.opacity( _this.imgs[_this.current],x)
        },function (){
            _this.imgs[_this.current].style.display='none';
            _this.opacity(_this.imgs[next],0);
            _this.imgs[next].style.display='';
            _this.current=next;
            _this.timer=_this.fx(0,1,function (x){
                _this.opacity( _this.imgs[next],x)
            },0,200,.5)
        })
    },
    fx:function (f,t,fn,ed,tm,r){
        var D=Date,d=new D,e,ed=ed||D,c=tm||300,r=r||5;
        return e=setInterval(function (){
            var z=Math.min(1,(new D-d)/c);
            var stop=fn(+f+(t-f)*Math.pow(z,r),z);
            if(z==1||false===stop)ed(clearTimeout(e))
        },10)
    },
    opacity:function (el,n){
       el.style.filter='progid:DXImageTransform.Microsoft.Alpha(opacity='+n*100+');';
       if(n==1)el.style.filter=null;
       el.style.opacity=n;
    },
    auto:function (){
        var _this=this;
        setInterval(function (){if(!_this.hover)_this.focus(_this.current+1)},this.interval);
    }
};
new PicSlide('ad_slide',{type:'opacity'});

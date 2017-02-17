;(function(){
	var areaSelect={};
	areaSelect.result={};
	// 配置
	var defaults={
		baseNum:30,     
		areaArr:[
			{
				"province":"上海",
				"citys":[
					{
						"name":"上海市",
						"districts":[
							"闸北区",
							"徐汇区",
							"虹口区",
							"黄浦区",
							"闵行区",
							"静安区"
						]
					}
				]
			},
			{
				"province":"安徽省",
				"citys":[
					{
						"name":"合肥市",
						"districts":[
							"包河区",
							"蜀山区",
							"瑶海区",
							"肥西县"
						]
					},
					{
						"name":"淮北市",
						"districts":[
							"烈山区",
							"相山区",
							"杜集区",
							"濉溪县"
						]
					},
					{
						"name":"测试市",
						"districts":[
							"测试1区",
							"测试2区",
							"测试3区",
							"测试4县"
						]
					}
				]

			}
		]
	}
	//  变量
	var i_1=0,
		i_2=0,
		i_3=0,
		province_box=document.getElementById('area-provinces'),
		city_box=document.getElementById("area-citys"), //市
		district_box=document.getElementById("area-districts"); //区
	/*
	*   @ areaArr 地址数据
	*   @ province_box 省份元素
	*   @ city_box 市级元素
	*   @ district_box 区级元素
	*   @ i1 省级位置
	*   @ i2 市级位置
	*   @ i3 区级位置
	*   @ baseNum 选项高度
	*   输入参数重新渲染组件
	*/
	areaSelect.render=function(areaArr,province_box,city_box,district_box,i1,i2,i3,baseNum){
		var pro_str="",
			city_str="",
			dis_str="";
		areaArr.forEach(function(area,index){
			pro_str+="<span class='areaSelector-item'>"+area.province+"</span>";
			if(index==i1){
				area.citys.forEach(function(city,_index){
					city_str+="<span class='areaSelector-item'>"+city.name+"</span>";
					if(_index==i2){
						city.districts.forEach(function(district){
							dis_str+="<span class='areaSelector-item'>"+district+"</span>";
						})
					}
				})  
			}
		})
		province_box.innerHTML=pro_str;
		city_box.innerHTML=city_str;
		district_box.innerHTML=dis_str;
		/**控制top**/
		province_box.style.top=baseNum*(1-i1)+"px";
		city_box.style.top=baseNum*(1-i2)+"px";
		district_box.style.top=baseNum*(1-i3)+"px";
	}
	/*
	*   @ o 滑动的元素
	*   吸附并返回各选项选中的位置
	*/
	areaSelect.adsorption=function(o){
		var top=parseInt(o.style.top),
			baseNum=defaults.baseNum,
			len=o.getElementsByTagName('span').length;
		// 第一个
		if(Math.round(top/baseNum)>=1){
			return 0;
		}
		// 最后一个
		if(Math.round(top/baseNum)<=-(len-2)){
			return len-1;
		}
		return Math.abs(Math.round(top/baseNum))+1;
	}
	/*
	*   初始化组件
	*/
	areaSelect.init=function(){
		var areaArr=defaults.areaArr,
			baseNum=defaults.baseNum;
		this.render(areaArr,province_box,city_box,district_box,0,0,0,baseNum);
		this.result={
			province:areaArr[0].province,
			city:areaArr[0].citys[0].name,
			district:areaArr[0].citys[0].districts[0]
		};
		province_box.getElementsByTagName('span')[0].classList.add('current');
		city_box.getElementsByTagName('span')[0].classList.add('current');
		district_box.getElementsByTagName('span')[0].classList.add('current');
	}
	areaSelect.init();
	/*
	*   滑动后回调函数
	*/
	areaSelect.teCallback=function(o){
		var areaArr=defaults.areaArr,
			baseNum=defaults.baseNum;
		switch(o){
			case province_box:
				i_1=this.adsorption(o);
				i_2=0;
				i_3=0;
				this.render(areaArr,province_box,city_box,district_box,i_1,i_2,i_3,baseNum);
				break;
			case city_box:
				i_2=this.adsorption(o);
				i_3=0;
				this.render(areaArr,province_box,city_box,district_box,i_1,i_2,i_3,baseNum);
				break;
			case district_box:
				i_3=this.adsorption(o);
				this.render(areaArr,province_box,city_box,district_box,i_1,i_2,i_3,baseNum);
				break;
		}
		// class控制
		Array.prototype.slice.call(province_box.getElementsByTagName('span'),0).forEach(function(item,index){
			item.classList.remove('current');
			if(index==i_1){
				item.classList.add('current');
			}
		})
		Array.prototype.slice.call(city_box.getElementsByTagName('span'),0).forEach(function(item,index){
			item.classList.remove('current');
			if(index==i_2){
				item.classList.add('current');
			}
		})
		Array.prototype.slice.call(district_box.getElementsByTagName('span'),0).forEach(function(item,index){
			item.classList.remove('current');
			if(index==i_3){
				item.classList.add('current');
			}
		})
		// 返回选择结果
		this.result.province=areaArr[i_1].province;
		this.result.city=areaArr[i_1].citys[i_2].name;
		this.result.district=areaArr[i_1].citys[i_2].districts[i_3];
	}
	window.areaSelect=areaSelect;
})();
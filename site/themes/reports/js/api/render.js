(function(){
	var namespace = $('script[data-seoapi-ns]').attr('data-seoapi-ns');
	
	window[namespace].render = {
			
	dependencies : [],
	
	isReady : function(){
		return true;
	},	
	newEl : function (elName){
		return $(document.createElement(elName));
	},
	newLi : function(key,value){
		var $li = this.newEl('li');
		var $spanK = this.newEl('span').addClass('list-key').html(key);
		var $spanV = this.newEl('span').addClass('list-val').html(value);
		return $li.append($spanK).append($spanV);
	},
	newList : function(obj){
		var $ul = this.newEl('ul');
		for(var x in obj)
			$ul.append(this.newLi(x,obj[x]));
		return $ul;
	},
	newRow : function(obj, type, useKeys){
		type = (typeof type === "undefined") ? 'td' : type;
		useKeys = (typeof useKeys == undefined) ? false : useKeys;
		var $tr = this.newEl('tr');
		for(var x in obj){
			var val = x;
			if(useKeys)
				val = (obj[x] == null) ? "Null" : obj[x];

			$tr.append(this.newEl(type).addClass('c'+x).html(val));
		}
		return $tr;
	},
	
	newTblHead : function(obj, useKeys){
		if(typeof useKeys === "undefined") useKeys = false;
		return this.newEl('thead').append(this.newRow(obj,'th',useKeys));
	},
	
	newTbl : function(objRows, objHead){
		var $tbl = this.newEl('table');
		if(typeof objHead === "undefined"){
			$tbl.append(this.newTblHead(objRows[0],false));
		}else{
			$tbl.append(this.newTblHead(objHead,false));
        }
		
		for(var x in objRows)
			$tbl.append(this.newRow(objRows[x],'td',true));
		
		return $tbl;
	}

}})(/*namespace*/);
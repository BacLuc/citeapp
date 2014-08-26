
//Zeile 17xxx
Graph.prototype._checkShowPopup = function (pointer) {
	console.log("this is my _checkshowpopup");
  var obj = {
    left:   this._XconvertDOMtoCanvas(pointer.x),
    top:    this._YconvertDOMtoCanvas(pointer.y),
    right:  this._XconvertDOMtoCanvas(pointer.x),
    bottom: this._YconvertDOMtoCanvas(pointer.y)
  };

  var id;
  var lastPopupNode = this.popupObj;
  if (this.popupObj == undefined) {
    // search the nodes for overlap, select the top one in case of multiple nodes
    var nodes = this.nodes;
    for (id in nodes) {
      if (nodes.hasOwnProperty(id)) {
        var node = nodes[id];
        if (node.getTitle() !== undefined && node.isOverlappingWith(obj)) {
          this.popupObj = node;
          break;
        }
      }
    }
  }

  if (this.popupObj === undefined) {
    // search the edges for overlap
    var edges = this.edges;
    for (id in edges) {
      if (edges.hasOwnProperty(id)) {
        var edge = edges[id];
        if (edge.connected && (edge.getTitle() !== undefined) &&
            edge.isOverlappingWith(obj)) {
          this.popupObj = edge;
          break;
        }
      }
    }
  }
  //console.log("ich bin hier 1");
  //console.log(this.popupObj);
  //console.log(id);
  if (this.popupObj) {
	    //console.log("ich bin hier 2");
    // show popup message window
    if (this.popupObj != lastPopupNode ) {
		  //console.log("ich bin hier 3");
      var me = this;
      if (!me.popup ) {
		  //console.log("ich bin hier4");  
        me.popup = new Popup(me.frame, me.constants.tooltip);
        
        
      }

      // adjust a small offset such that the mouse cursor is located in the
      // bottom left location of the popup, and you can easily move over the
      // popup area
      me.popup.setPosition(pointer.x - 3, pointer.y - 3);
      me.popup.setText(me.popupObj.getTitle());
      me.popup.show();
      //console.log("ich bin hier");
      if(this.popupObj.properties !== undefined){
		 // console.log("bin schon weiter");
			for(var i in this.popupObj.properties){
				
				
								$( "#obj"+this.popupObj.id+"propCombobox"+String(i) ).combobox();
								
								/*$( "#toggle" ).click(function() {
									$( "#obj"+this.popupObj.id+"propCombobox"+String(i)).toggle();
								});*/
								
								$( "#obj"+this.popupObj.id+"objCombobox"+String(i) ).combobox();
								/*$( "#toggle" ).click(function() {
									$( "#obj"+this.popupObj.id+"objCombobox"+String(i)).toggle();
								});*/
						
				}
			}
      
    }
  }
  else {
    if (this.popup) {
      this.popup.hide();
    }
  }
};


//9146
Node.prototype.reCalcTitle = function(){
	
	
	var toolbox = '<table border = "0" id="nodetable_'+this.id+'"><form action="" method="POST">';
	for( var j in this.properties){
		
		//toolbox += '<tr><td><input type="text" changed="0" value="'+this.properties[j].label+'"</td>';
		
		toolbox += '<tr><td><select> <option>option1></option></select><select id="oid'+this.id+'propCombobox'+j+'"  class="property"></select></td>';
		
		//toolbox += '<td><input type="text" changed="0" value="'+this.properties[j].value+'"</td></tr>';
		
		toolbox += '<tr><td><select id="oid'+this.id+'objCombobox'+j+'"  class="object"></select></td>';
		
	}
	toolbox += "<button id='addButton_"+this.id+"' oid="+this.id+" onClick='graph.nodes["+this.id+"].addProperty();' value='new Property'>new Property</button>";
	toolbox += "<button id='saveButton_"+this.id+"' oid="+this.id+" onClick='graph.nodes["+this.id+"].saveNode();' value='save'>save</button>";
	if(!this.isPopupFrozen){
		toolbox += "<button id='freezeButton_"+this.id+"' oid="+this.id+" onClick='graph.nodes["+this.id+"].freezePopup();' value='freeze'>freeze</button>";
	}else{
		toolbox += "<button id='freezeButton_"+this.id+"' oid="+this.id+" onClick='graph.nodes["+this.id+"].unFreezePopup();' value='unfreeze'>unfreeze</button>";		
	}
	//console.log(this.title);
	this.title=toolbox;
}

Node.prototype.freezePopup = function(){
	this.isPopupFrozen = true;
	this.reCalcTitle();
	graph.constants.tooltip=this.title;
		//graph.popup=null;
	
	graph.popupObj = null;
	graph._checkShowPopup(graph.canvasToDOM({x:this.x, y:this.y}));
	var node=document.getElementById("popupOfNodes").cloneNode(true);
	node.id=node.id+String(this.id);
	
	document.getElementById("mygraph").appendChild(node);
	graph.popupObj = null;
	graph._checkShowPopup(graph.canvasToDOM({x:window.event.clientX, y:window.event.clientY}));
	}
	
Node.prototype.unFreezePopup = function(){
	var element = document.getElementById("popupOfNodes"+String(this.id));
	element.parentNode.removeChild(element);
	this.isPopupFrozen=false;
	this.reCalcTitle();
	}

Node.prototype.addProperty = function(){
		this.properties.push({
			oldtype: null,
			type: "newType",
			label: "newType",
			oldvalue: "newValue",
			value: "newValue",
			isedited: false 
	
		});
		this.reCalcTitle();
			
		if(this.isPopupFrozen){
			$('#popupOfNodes'+String(this.id)).html(this.getTitle());
		}else{
		
			graph.constants.tooltip=this.title;

			graph.popupObj = null;
			graph._checkShowPopup(graph.canvasToDOM({x:this.x, y:this.y}));
	}
		
  }
Node.prototype.searchForTypes = function(propId){}
Node.prototype.saveNode = function(){}



/**
 * Set or overwrite properties for the node
 * @param {Object} properties an object with properties
 * @param {Object} constants  and object with default, global properties
 */
Node.prototype.setProperties = function(properties, constants) {
  if (!properties) {
    return;
  }
  this.originalLabel = undefined;
  // basic properties
  if (properties.id !== undefined)        {this.id = properties.id;}
  if (properties.label !== undefined)     {this.label = properties.label; this.originalLabel = properties.label;}
  if (properties.title !== undefined)     {this.title = properties.title;}
  if (properties.group !== undefined)     {this.group = properties.group;}
  if (properties.x !== undefined)         {this.x = properties.x;}
  if (properties.y !== undefined)         {this.y = properties.y;}
  if (properties.value !== undefined)     {this.value = properties.value;}
  if (properties.level !== undefined)     {this.level = properties.level; this.preassignedLevel = true;}


  // physics
  if (properties.mass !== undefined)                {this.mass = properties.mass;}

  // navigation controls properties
  if (properties.horizontalAlignLeft !== undefined) {this.horizontalAlignLeft = properties.horizontalAlignLeft;}
  if (properties.verticalAlignTop    !== undefined) {this.verticalAlignTop    = properties.verticalAlignTop;}
  if (properties.triggerFunction     !== undefined) {this.triggerFunction     = properties.triggerFunction;}

  if (this.id === undefined) {
    throw "Node must have an id";
  }

  // copy group properties
  if (this.group) {
    var groupObj = this.grouplist.get(this.group);
    for (var prop in groupObj) {
      if (groupObj.hasOwnProperty(prop)) {
        this[prop] = groupObj[prop];
      }
    }
  }

  // individual shape properties
  if (properties.shape !== undefined)          {this.shape = properties.shape;}
  if (properties.image !== undefined)          {this.image = properties.image;}
  if (properties.radius !== undefined)         {this.radius = properties.radius;}
  if (properties.color !== undefined)          {this.color = util.parseColor(properties.color);}

  if (properties.fontColor !== undefined)      {this.fontColor = properties.fontColor;}
  if (properties.fontSize !== undefined)       {this.fontSize = properties.fontSize;}
  if (properties.fontFace !== undefined)       {this.fontFace = properties.fontFace;}

  if (this.image !== undefined && this.image != "") {
    if (this.imagelist) {
      this.imageObj = this.imagelist.load(this.image);
    }
    else {
      throw "No imagelist provided";
    }
  }

  this.xFixed = this.xFixed || (properties.x !== undefined && !properties.allowedToMoveX);
  this.yFixed = this.yFixed || (properties.y !== undefined && !properties.allowedToMoveY);
  this.radiusFixed = this.radiusFixed || (properties.radius !== undefined);

  if (this.shape == 'image') {
    this.radiusMin = constants.nodes.widthMin;
    this.radiusMax = constants.nodes.widthMax;
  }

  // choose draw method depending on the shape
  switch (this.shape) {
    case 'database':      this.draw = this._drawDatabase; this.resize = this._resizeDatabase; break;
    case 'box':           this.draw = this._drawBox; this.resize = this._resizeBox; break;
    case 'circle':        this.draw = this._drawCircle; this.resize = this._resizeCircle; break;
    case 'ellipse':       this.draw = this._drawEllipse; this.resize = this._resizeEllipse; break;
    // TODO: add diamond shape
    case 'image':         this.draw = this._drawImage; this.resize = this._resizeImage; break;
    case 'text':          this.draw = this._drawText; this.resize = this._resizeText; break;
    case 'dot':           this.draw = this._drawDot; this.resize = this._resizeShape; break;
    case 'square':        this.draw = this._drawSquare; this.resize = this._resizeShape; break;
    case 'triangle':      this.draw = this._drawTriangle; this.resize = this._resizeShape; break;
    case 'triangleDown':  this.draw = this._drawTriangleDown; this.resize = this._resizeShape; break;
    case 'star':          this.draw = this._drawStar; this.resize = this._resizeShape; break;
    default:              this.draw = this._drawEllipse; this.resize = this._resizeEllipse; break;
  }
  
 
  // reset the size of the node, this can be changed
  this.properties = properties.properties;
  if (properties.properties !== undefined)          {this.properties = properties.properties;}
  if (properties.oldtype !== undefined)          {this.oldtype = properties.oldtype;}
  if (properties.type !== undefined)          {this.type = properties.type;}
  if (properties.label !== undefined)          {this.label = properties.label;}
  if (properties.oldvalue !== undefined)          {this.oldvalue = properties.oldvalue;}
  if (properties.isedited !== undefined)          {this.isedited = properties.isedited;}

  
  this._reset();
	
  this.reCalcTitle();
};

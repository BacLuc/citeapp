<!doctype html>
<html>
<head>
	
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />	
  <title>Graph | Navigation</title>

  <style type="text/css">
    body {
      font: 10pt sans;
    }
    #mygraph {
      position:absolute;top:0;bottom:0;
      width:100%;
      border: 1px solid lightgray;
    }
    table.legend_table {
      font-size: 11px;
      border-width:1px;
      border-color:#d3d3d3;
      border-style:solid;
    }
    table.legend_table,td {
      border-width:1px;
      border-color:#d3d3d3;
      border-style:solid;
      padding: 2px;
    }
    div.table_content {
      width:80px;
      text-align:center;
    }
    div.table_description {
      width:100px;
    }

    #operation {
      font-size:28px;
    }
    #graph-popUp {
      display:none;
      position:absolute;
      top:100px;
      left:300px;
      z-index:99;
      background-color: #f9f9f9;
      border-style:solid;
      border-width:3px;
      border-color: #5394ed;
      padding:10px;
      text-align: center;
    }
    #edge-popup {
      display:none;
      position:absolute;
      top:100px;
      left:300px;
      z-index:99;
      width:250px;
      height:120px;
      background-color: #f9f9f9;
      border-style:solid;
      border-width:3px;
      border-color: #5394ed;
      padding:10px;
      text-align: center;
    }
    
	.custom-combobox {
		position: relative;
		display: inline-block;
	}
	.custom-combobox-toggle {
		position: absolute;
		top: 0;
		bottom: 0;
		margin-left: -1px;
		padding: 0;
		/* support: IE7 */
		*height: 1.7em;
		*top: 0.1em;
	}
	.custom-combobox-input {
		margin: 0;
		padding: 0.3em;
	}
	.ui-autocomplete{
		z-index:201;	
	}
	#SearchDiv{
		position: absolute;
		right: 0px;
		top: 10px;
		z-index: 99;
	}
    
  </style>
  <script type="text/javascript" src="vis.js/vis.js"></script>
  <script type="text/javascript" src="jQuery.js"></script>
  <script type="text/javascript" src="drawGraph.js"></script> 
  <link rel="stylesheet" href="autocomplete/jquery_ui.css">
  <script src="jQuery.js"></script>
  <script src="autocomplete/jquery_ui.js"></script>
  <script src="autocomplete.js"></script>
  <link type="text/css" rel="stylesheet" href="vis.js/vis.css">

	
  <script type="text/javascript">   
  
    var nodes = null;
    var nodesDataSet = new vis.DataSet();
    var edges = null;
    var edgesDataSet = new vis.DataSet();
    var graph = null;
    var nodeIdToOid = [];
    var OidToNodeId = [];
    var propertyfields = [];
	var edgesUsed = [];
	
	var markedIndizes = [];
	var graph;
	var labellength = 80;
	var loadDelay = 1000;
	
	function initialize(oid){
			graph = draw(oid);
			
			
		
		
		
		}
	
		

    function draw(oid) {
	
	
	var winHeight = document.getElementById('mygraph').offsetHeight;
	//console.log(winHeight);
	$('body').attr('height', winHeight);
	$('#mygraph').attr('height', winHeight);
	$('#SearchDiv').attr('right', $('#SearchDiv').attr('width'));
	
      nodes = [];
      edges = [];
      var connectionCount = [];
		  var form_data = {
				action : 'getObjectById',
				oid : oid,
				recLevel : 2
				 
			};
		$.ajax({
			type: "POST",
			url: "ajax_server.php",
			data: form_data,
			dataType: "json",
			success: function(response){
				
				if(response.check == "suc"){
					//console.log(response);
					for(var i in response.nodes){
							var fullName= null;
							var citeText= null;
							var bookTitle= null;
							var toolbox = '<table border = "0" id="nodetable_'+i+'"><form action="" method="POST">';
							var nodeProperties = [];
							for( var j in response.nodes[i].properties){

										if(response.nodes[i].properties[j].label == 'fullname' && response.nodes[i].label == 'author'){
												fullName=response.nodes[i].properties[j].value
											}
										if(response.nodes[i].properties[j].label == 'title' && response.nodes[i].label == 'text'){
												bookTitle=response.nodes[i].properties[j].value
											}
										if(response.nodes[i].properties[j].label == 'citetext' && response.nodes[i].label == 'cite'){
												citeText=response.nodes[i].properties[j].value
											}
									nodeProperties.push({
										
											oldtype: response.nodes[i].properties[j].type,
											type: response.nodes[i].properties[j].type,
											label: response.nodes[i].properties[j].label,
											oldlabel: response.nodes[i].properties[j].label,
											oldvalue: response.nodes[i].properties[j].value,
											value: response.nodes[i].properties[j].value,
											isedited: false, 
											isdeleted: false
										
										});
								
								}
								
							
						
							
							nodes[Number(i)]={
								  id: Number(i),
								  label: response.nodes[i].label,
								  title: toolbox,
								  properties: nodeProperties,
								  fontSize : 20
								  
								  
								};
								
							if(nodes[Number(i)].label !== undefined){
									if(nodes[Number(i)].label == 'author'){
											nodes[Number(i)].image='icons/author.jpg';
											nodes[Number(i)].shape = 'image';
											if(fullName != null)nodes[Number(i)].label = fullName;
											
										}
									else if(nodes[Number(i)].label == 'text'){
											nodes[Number(i)].image='icons/text.jpg';
											nodes[Number(i)].shape = 'image';
											if(bookTitle != null){
												nodes[Number(i)].label = bookTitle;
												if(bookTitle.length > labellength){
													nodes[Number(i)].label = bookTitle.substring(0,labellength)+"...";
												}
											}
											//console.log(nodes[Number(i)].label);
										}
									else if(nodes[Number(i)].label == 'cite'){
											nodes[Number(i)].image='icons/cite.jpg';
											nodes[Number(i)].shape = 'image';
											if(citeText != null){
												nodes[Number(i)].label = citeText;
												if(citeText.length > labellength){
													nodes[Number(i)].label = citeText.substring(0,labellength)+"...";
												}
											}
										}
							  
							  
							  }
							
						
									
						
						}
						
						
						  for(var i in response.edges){
									
									
									edges[response.edges[i].from+response.edges[i].to+response.edges[i].label]={
										  from: Number(response.edges[i].from),
										  to: Number(response.edges[i].to),
										  label : response.edges[i].label,
										  title: response.edges[i].label,
										  style: 'arrow'
										};		
								
								
								}
							//console.log(nodes);
							
							drawGraph();
						
						
							for(var i in nodes){
									graph.nodes[nodes[i].id].properties=nodes[i].properties;
									graph.nodes[nodes[i].id].reCalcTitle(); 
									
									//getNodesRecursive(nodes[i].id);
								}
							setTimeout(function(){
							for(var i in nodes){
								
									getNodesRecursive(nodes[i].id);
								
								}
							}, loadDelay);	
							
							
						
								
					}
					else{
							console.log("error:");
							console.log(response);
						}
		}
		
		
	});

	
		$(function() {
			$( "#edgeCombobox" ).combobox();
			$( "#toggle" ).click(function() {
				$( "#edgeCombobox" ).toggle();
			});
		});
		
		$(function() {
			$( "#typeCombobox" ).combobox();
			$( "#toggle" ).click(function() {
				$( "#typeCombobox" ).toggle();
			});
		});
		
		$(function() {
			$( "#MainObjCombobox" ).combobox({focus: function(event, ui){console.log(this); console.log(event); console.log(ui);}});
			$( "#toggle" ).click(function() {
				$( "#MainObjCombobox" ).toggle();
			});
		});
		
		
		$('#ShowButton').on('click', function(e){
				var e= document.getElementById('MainObjCombobox');
				$('#mygraph').remove();
				$('body').append('<div id="mygraph"></div>');
				//draw($(e.options[e.selectedIndex]).attr('oid'));
				window.location="index.php?oid="+$(e.options[e.selectedIndex]).attr('oid');
			
			});
			
		$('#addSearchedNode').on('click', function(e){
				var e= document.getElementById('MainObjCombobox');
				var oid=$(e.options[e.selectedIndex]).attr('oid');
				if(nodes[oid] ===undefined){
					getNodesRecursive($(e.options[e.selectedIndex]).attr('oid'));
				}else{
					
				graph.focusOnNode(oid);	
				}
			});
			
		$('option').on('mouseover', function(e){
				console.log(this);
				console.log(e);
			
			});
			
			
		
			
		
			
		
		
		
    }
    /*
    function calcHeight(textarea) {
		//console.log(value);
		//console.log(textarea);
		
		
		var Inhalt = textarea.value;
		if(Inhalt === undefined)Inhalt = "";
		var eingabe = {};
		eingabe.cols = 15;
		var geteilt = Inhalt.split('\n');
		var Abstand = 0;
		var altPos = -1;
		for(var Zaehler = 0; Zaehler < geteilt.length; Zaehler++) {
			Abstand += Math.floor(geteilt[Zaehler].length/eingabe.cols)
		}

		
		//eingabe.style.height = Abstand+'px';
		textarea.rows=Abstand;
	}*/
    
    function getNodesRecursive(oid){
			var firstNode;
			if(nodes[oid] === undefined){
					firstNode = undefined;
				
				}else{
					firstNode = graph.nodes[nodes[oid].id];
					
				}
			var form_data = {
							action : 'getObjectById',
							oid : oid,
							recLevel : 1
				 
						};
						$.ajax({
							type: "POST",
							url: "ajax_server.php",
							data: form_data,
							dataType: "json",
							startNode: firstNode ,
							success: function(response){
								
								var newNodes = [];
                                var countNodes = nodes.length;
					
								for(var i in response.nodes){
									
									var fullName= null;
									var citeText= null;
									var bookTitle= null;
									var toolbox = '<table border = "0" id="nodetable_'+i+'">';
									var nodeProperties = [];
									for( var j in response.nodes[i].properties){
												if(response.nodes[i].properties[j].label == 'fullname' && response.nodes[i].label == 'author'){
														fullName=response.nodes[i].properties[j].value
													}
												if(response.nodes[i].properties[j].label == 'title' && response.nodes[i].label == 'text'){
											
														bookTitle=response.nodes[i].properties[j].value
													}
												if(response.nodes[i].properties[j].label == 'citetext' && response.nodes[i].label == 'cite'){
														citeText=response.nodes[i].properties[j].value
													}
												nodeProperties.push({
														
															oldtype: response.nodes[i].properties[j].type,
															type: response.nodes[i].properties[j].type,
															label: response.nodes[i].properties[j].label,
															oldlabel: response.nodes[i].properties[j].label,
															oldvalue: response.nodes[i].properties[j].value,
															value: response.nodes[i].properties[j].value,
															isedited: false, 
															isdeleted: false
														
														});
												
												}
												
												
												
												var newNode = {
													  id: Number(i),
													  label: response.nodes[i].label,
													  title: toolbox,
													  properties: nodeProperties,
													  fontSize : 20  
													  
													};
												if(this.startNode !== undefined ){
														newNode.x = this.startNode.x;
														newNode.y = this.startNode.y,
														newNode.allowedToMoveX = true;
														newNode.allowedToMoveY =true;
														
													
													}
											
										
												
													
													if(newNode.label !== undefined){
															if(newNode.label == 'author'){
																	newNode.image='icons/author.jpg';
																	newNode.shape = 'image';
																	if(fullName != null)newNode.label = fullName;
																	
																}
															else if(newNode.label == 'text'){
														
																	newNode.image='icons/text.jpg';
																	newNode.shape = 'image';
																	if(bookTitle != null){
																		newNode.label = bookTitle;
																		if(bookTitle.length > labellength){
																			newNode.label = bookTitle.substring(0,labellength)+"...";
																		}
																	}
																	//console.log(newNode.label);
																}
															else if(newNode.label == 'cite'){
																	newNode.image='icons/cite.jpg';
																	newNode.shape = 'image';
																	if(citeText != null){
																		newNode.label = citeText;
																		if(citeText.length > labellength){
																			newNode.label = citeText.substring(0,labellength)+"...";
																		}
																	}
																}
													  
													  
													  }
													  
													  if(nodes[Number(i)] === undefined){
															nodesDataSet.add(newNode);
															newNodes[Number(i)]= newNode;
														}
								
									
														nodes[Number(i)]=newNode;
														
									
								
													
								
													
																	
													
															
														
																
														
										}
															
														
										for(var i in response.edges){
													var newEdge = {
															  from: Number(response.edges[i].from),
															  to: Number(response.edges[i].to),
															  label : response.edges[i].label,
															  title: response.edges[i].label,
															  style: 'arrow'
															};
															
													if(edges[response.edges[i].from+response.edges[i].to+response.edges[i].label] === undefined){
															edgesDataSet.add(newEdge);
														
														}
													edges[response.edges[i].from+response.edges[i].to+response.edges[i].label]=newEdge;
													
													
											
											}
													//console.log(nodes);
													//console.log(edges);
															
														
									
															
										
											for(var i in newNodes){
													graph.nodes[newNodes[i].id].properties=newNodes[i].properties;
													graph.nodes[newNodes[i].id].reCalcTitle();	
													
													//getNodesRecursive(newNodes[i].id);
												}
											setTimeout(function(){
												for(var i in newNodes){
													
														getNodesRecursive(newNodes[i].id);
													
													}
											}, loadDelay);
																
										
										
													
													
												
												
							
										
										
										
										
							}
								
								
								
						});
		
		
		
		}

		
	
    
  </script>
</head>

<body onload="initialize(<?php if(isset($_GET['oid'])){echo $_GET['oid'];}
					else {echo "1";}?>);">


<div id="graph-popUp" onload="function (){}">
  <span id="operation">node</span> <br>
  <table id='popuptable' style="margin:auto;"><input id="node-label" value="new value" hidden="true">
  <tr>  
    <td>type</td><td><select id="typeCombobox" name="type" class="type"></select></td>
    
  </tr>
    </table>
  <input type = "button" id="new_property" value="Neue Property hinzufuegen">
  <input type="button" value="save" id="saveButton"></button>
  <input type="button" value="cancel" id="cancelButton"></button>
</div>


<div id="edge-popup">
  Bezeichnung der Beziehung
  <select id="edgeCombobox" name="edgeName" class="relation"></select>
  <input type="button" value="save" id="saveButtonEdge"></button>
  <input type="button" value="cancel" id="cancelButtonEdge"></button>
</div>

<div id="SearchDiv">
<select id="MainObjCombobox" class="object"></select>
<button id="ShowButton">Refresh with searched Object</button>
<button id="addSearchedNode">Add searched Object </button>

</div>

<div id="hiddendiv" style="z-index: -100; color: white;"></div>

<br />
<div id="mygraph"></div>

<p id="selection"></p>
</body>
</html>


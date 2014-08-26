function drawGraph(){ 

				  var container = document.getElementById('mygraph');
				  
				  //console.log(container.parent);
				  var visnodes = [];
				  var visedges = [];
				  
				  
				  for(var i in nodes){
						nodesDataSet.add(nodes[i]);
					  
					  }
					
						  
					for(var i in edges){
							
							edgesDataSet.add(edges[i]);
							
						  
						}

				  
				  var data = {
					nodes: nodesDataSet,
					edges: edgesDataSet
				  };
				  var options = {
					tooltip: {
						delay: 0
						},
					edges: {
					  length: 10000
					},
					physics: {
						barnesHut: {
							centralGravity: 1,
							damping: 0.02,
							enabled: true,
							gravitationalConstant: -50000,
							springConstant: 0.2,
							springLength: 500,
							theta: 1.6666666666666667
						},
						centralGravity: 0.3,
						damping: 0.02

					},
					stabilize: false,
					dataManipulation: true,
					onAdd: function(data,callback) {
					
						  var span = document.getElementById('operation');
						  //var idInput = document.getElementById('node-id');
						  var labelInput = document.getElementById('node-label');
						  var saveButton = document.getElementById('saveButton');
						  var cancelButton = document.getElementById('cancelButton');
						  var div = document.getElementById('graph-popUp');
						  span.innerHTML = "Add Node";
						  //idInput.value = data.id;
						  labelInput.value = data.label;
						  saveButton.onclick = saveData.bind(this,data,callback);
						  cancelButton.onclick = clearPopUp.bind();
						  div.style.display = 'block';
					},
					onEdit: function(data,callback) {
					  var span = document.getElementById('operation');
					  var idInput = document.getElementById('node-id');
					  var labelInput = document.getElementById('node-label');
					  var saveButton = document.getElementById('saveButton');
					  var cancelButton = document.getElementById('cancelButton');
					  var div = document.getElementById('graph-popUp');
					  span.innerHTML = "Edit Node";
					  //idInput.value = data.id;
					  //labelInput.value = data.label;
					  saveButton.onclick = saveData.bind(this,data,callback);
					  cancelButton.onclick = clearPopUp.bind();
					  div.style.display = 'block';
					},
					onConnect: function(data,callback) {
					  if (data.from == data.to) {
						var r=confirm("Do you want to connect the node to itself?");
						if (r==true) {
						  
						}
						else{
							return;
						}
					  }
					  else {
					  }
					  
					  var labelInput = document.getElementById('edgeName');
					  var saveButton = document.getElementById('saveButtonEdge');
					  var cancelButton = document.getElementById('cancelButtonEdge');
					  var div = document.getElementById('edge-popup');
					  //span.innerHTML = "Create Edge";
					  //idInput.value = data.id;
					  //labelInput.value = data.label;
					  saveButton.onclick = saveEdge.bind(this,data,callback);
					  cancelButton.onclick = clearEdgePopup.bind();
					  div.style.display = 'block';
					},
					onDelete: function(data, callback){
						
						var r = confirm("Do you really want to delete these nodes and edges?");
						if(!r)return;
						for (var i in data.nodes){
								var form_data = {
										action : 'deleteObject',
										oid : data.nodes[i]
										 
									};
									
								$.ajax({
										type: "POST",
										url: "ajax_server.php",
										data: form_data,
										dataType: "json",
										success: function(response){
											
											if(response.check == "suc"){
												oid=response.oid;
												nodesDataSet.remove(response.oid);
											}else{
												alert("Error deleting a Node");
												
											}
										}
									});
							
							}
							
						for (var i in data.edges){
								var form_data = {
										action : 'removeRelation',
										oid : graph.edges[data.edges[i]].fromId,
										relation : graph.edges[data.edges[i]].label,
										other_oid : graph.edges[data.edges[i]].toId,
									};
									
								$.ajax({
										type: "POST",
										url: "ajax_server.php",
										data: form_data,
										dataType: "json",
										edgeId: data.edges[i],
										success: function(response){
											
											if(response.check == "suc"){
												edgesDataSet.remove(this.edgeId);
											}else{
												alert("Error deleting a Edge");
												
											}
										}
									});
									
								var form_data = {
										action : 'removeRelation',
										oid : graph.edges[data.edges[i]].fromId,
										relation : "http://citeapp.ch/voc.html#"+graph.edges[data.edges[i]].label,
										other_oid : graph.edges[data.edges[i]].toId,
									};
									
								$.ajax({
										type: "POST",
										url: "ajax_server.php",
										data: form_data,
										dataType: "json",
										edgeId: data.edges[i],
										success: function(response){
											
											if(response.check == "suc"){
												edgesDataSet.remove(this.edgeId);
											}else{
												alert("Fehler beim Löschen eines Nodes");
												
											}
										}
									});
							
							}
						
						},
					onEditEdge: function(data, callback){
						alert("Please delete the Edge and make a new one");
						
						}
				  };
				  
				  
				  
				  //console.log(data);
				  graph = new vis.Graph(container, data, options);
				  graph.focusOnNode(0);

				  // add event listeners
				  graph.on('select', function(params) {
					
					document.getElementById('selection').innerHTML = 'Selection: ' + params.nodes;
				  });

				  graph.on("resize", function(params) {console.log(params.width,params.height)});

				  function clearPopUp() {
					var saveButton = document.getElementById('saveButton');
					var cancelButton = document.getElementById('cancelButton');
					saveButton.onclick = null;
					cancelButton.onclick = null;
					var div = document.getElementById('graph-popUp');
					div.style.display = 'none';
					for(var i in propertyfields){
							$('#'+propertyfields[i]+'a').remove();
							$('#'+propertyfields[i]+'b').remove();
						}
					$('#popuptable').empty();
					$('#popuptable').append('<tr><td>type</td><td><select id="typeCombobox" name="type" class="type"></select></td></tr>');
					$(function() {
						$( "#typeCombobox" ).combobox();
						$( "#toggle" ).click(function() {
							$( "#typeCombobox" ).toggle();
						});
					});
					propertyfields = [];
					

				  }

				  function saveData(data,callback) {
					  
					var idInput = document.getElementById('node-id');
					var labelInput = document.getElementById('node-label');
					var div = document.getElementById('graph-popUp');
					//data.id = idInput.value;
					//data.label = labelInput.value;
					
					propertiesArr=[];
					
					var e= document.getElementById('typeCombobox');
				
						
					var type = e.options[e.selectedIndex].value;
					
					
					var form_data = {
							action : 'newObject',
							type : type,
							properties : [],
							values : []
							 
						};
					console.log(propertyfields);
					var nodeProperties = [];
					for(var i in propertyfields){
							 var e= document.getElementById('propCombobox'+i);
				
						
							var property = e.options[e.selectedIndex].value;
							
							var e= document.getElementById('objCombobox'+i);
				
						
							var value = e.options[e.selectedIndex].value;
						
							form_data.properties.push(property);
							form_data.values.push(value);
							$('#propCombobox'+i).remove();
							$('#objCombobox'+i).remove();
							nodeProperties[property]=value;
						}
					clearPopUp();
					
					//console.log(data);
					
					$.ajax({
						type: "POST",
						url: "ajax_server.php",
						data: form_data,
						dataType: "json",
						success: function(response){
							
							if(response.check == "suc"){
								data.id=response.oid;
								getNodesRecursive(response.oid);
							}else{
								alert("Fehler beim Speichernv eines Nodes");
								
							}
						}
					});
					//callback(data);

				  }
				  
				  function saveEdge(data, callback){
				
					    var e= document.getElementById('edgeCombobox');
				
						
						var relationType = e.options[e.selectedIndex].value;
						
						
						var form_data = {
								action : 'addRelation',
								oid : data.from,
								relation : relationType,
								other_oid : data.to
								 
							};

						
						$.ajax({
						type: "POST",
						url: "ajax_server.php",
						data: form_data,
						dataType: "json",
						success: function(response){
								
								if(response.check == "suc"){
									//document.getElementById('edgeCombobox').value = "";
									$('#edge-popup').hide();
									data.label= relationType;
									var splitarray = relationType.split('#');
									if(splitarray.length >0 && splitarray.length <=2){
											data.label= splitarray[1];
										
										}
									edges[String(this.data.oid)+String(this.data.other_oid)+data.label] = data;
									edgesDataSet.add(data);
									
								}else{
									alert("Fehler beim Speichern von Edge");
								}
							}
						});
						
					  }
				  
				  function clearEdgePopup(){
					  console.log("bin hier");
						var saveButton = document.getElementById('saveButton');
						var cancelButton = document.getElementById('cancelButton');
						saveButton.onclick = null;
						cancelButton.onclick = null;
						var div = document.getElementById('edge-popup');
						div.style.display = 'none';
						
					  
					  }
				$('#new_property').on('click',function(e){
						var addid= propertyfields.length;
						$('#popuptable').append('<tr><td><select id="propCombobox'+addid+'" name="" class="property"></select></td><td><select id="objCombobox'+addid+'" name="" class="object"></td> </tr>');
						propertyfields.push(addid);
						$(function() {
							$( "#propCombobox"+addid ).combobox();
							$( "#toggle" ).click(function() {
								$( "#propCombobox"+addid ).toggle();
							});
						});
						
						$(function() {
							$( "#objCombobox"+addid ).combobox();
							$( "#toggle" ).click(function() {
								$( "#objCombobox"+addid ).toggle();
							});
						});
						
						
					});
					
				
				
				return graph;
		
				
				
		}
		
function addNodeProperty(oid){
						$('#nodetable_'+oid+'').append('<tr><td><input type="text" changed="1" value="newPropertyType"</td><td><input type="text" changed="0" value="newPropertyValue"</td></tr>');
						
					}

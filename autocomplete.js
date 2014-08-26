(function( $ ) {
$.widget( "custom.combobox", {
	
	
	_create: function() {
		this.wrapper = $( "<span>" )
		.addClass( "custom-combobox" )
		.insertAfter( this.element );
		this.element.hide();
		this._createAutocomplete();
		this._createShowAllButton();
},
_createAutocomplete: function() {
	
	var id=this.element[0].id;
	var nodeId=$('#'+id).attr('nodeId');
	var type = $('#'+id).attr('class');
	var propId = $('#'+id).attr('propId');
	
	
	
	
	
	
	
	var selected = this.element.children( ":selected" ),
	value = selected.val() ? selected.text() : "";
	if(propId != '' && id.indexOf('oid') > -1){
								
		if(type == 'object'){
				value=graph.nodes[nodeId].properties[propId].value;
			
			}
		else if(type == 'property'){
				value=graph.nodes[nodeId].properties[propId].label;
			}
		var e = document.getElementById(id);
		e.selectedIndex=e.options.length-1;

	}
	
	
	
	this.input = $( "<textarea>" )
	.appendTo( this.wrapper )
	.val( value )
	.attr( "title", "" )
	.addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
	.autocomplete({
		delay: 0,
		minLength: 0,
		source: $.proxy( this, "_source" ),
		focus: function(event, ui){$(this).attr('title', ui.item.option.title);}
	})
	.tooltip({
		tooltipClass: "ui-state-highlight"
	});
	
	
	this._on( this.input, {
		autocompleteselect: function( event, ui ) {
				var Id=this.element[0].id;
				var nodeId=$(this.element[0]).attr('nodeid');
				var propId=$(this.element[0]).attr('propid');
				var type = $('#'+id).attr('class');
				var value= event.srcElement.value;
				
				//console.log(event);
				//console.log(ui);
				//console.log(this);
				if(nodeId != '' && propId != '' && id.indexOf('oid') > -1){
			
					if(type == 'object'){
						graph.nodes[nodeId].properties[propId].value=value;
						
						if(graph.nodes[nodeId].properties[propId].oldvalue != value)graph.nodes[nodeId].properties[propId].isedited=true;
					}else if(type == 'property'){
						graph.nodes[nodeId].properties[propId].type=value;
						var labelsplit= value.split('#');
						var label= value;
						if(labelsplit.length >1){
							
							label = labelsplit[labelsplit.length-1];
							}
						
			
						graph.nodes[nodeId].properties[propId].label=label;
						
						if(graph.nodes[nodeId].properties[propId].oldtype != value)graph.nodes[nodeId].properties[propId].isedited=true;
						
					}
		
		
		}
			
			ui.item.option.selected = true;
			this._trigger( "select", event, {
				item: ui.item.option
			});
		},
		autocompletechange: "_removeIfInvalid"
	});
},
_createShowAllButton: function() {
	
	var input = this.input,
	wasOpen = false;
	$( "<a>" )
	.attr( "tabIndex", -1 )
	.attr( "title", "Show All Items" )
	.tooltip()
	.appendTo( this.wrapper )
	.button({
		icons: {
			primary: "ui-icon-triangle-1-s"
		},
		text: false
	})
	.removeClass( "ui-corner-all" )
	.addClass( "custom-combobox-toggle ui-corner-right" )
	.mousedown(function() {
		wasOpen = input.autocomplete( "widget" ).is( ":visible" );
	})
	.click(function() {
		input.focus();
		// Close if already visible
		if ( wasOpen ) {
			return;
		}
	// Pass empty string as value to search for, displaying all results
		input.autocomplete( "search", "" );
	});
},
_source: function( request, response ) {
	
	
	var id=this.element[0].id;
	var nodeId=$('#'+id).attr('nodeId');
	var type = $('#'+id).attr('class');
	var propId = $('#'+id).attr('propId');
	var nodeId=$('#'+id).attr('nodeId'); 
	var propId=$('#'+id).attr('propId');
	
	
	if(nodeId != '' && propId != '' && id.indexOf('oid') > -1){
			
			if(type == 'object'){
				
				graph.nodes[nodeId].properties[propId].value=request.term;
				
				if(graph.nodes[nodeId].properties[propId].oldvalue != request.term)graph.nodes[nodeId].properties[propId].isedited=true;
			}else if(type == 'property'){
				graph.nodes[nodeId].properties[propId].type=request.term;
				var labelsplit= request.term.split('#');
				var label= request.term;
				/*
				if(labelsplit.length >0){
					label= labelsplit[1];
					
				}*/
	
				graph.nodes[nodeId].properties[propId].label=label;
				
				if(graph.nodes[nodeId].properties[propId].oldtype != request.term)graph.nodes[nodeId].properties[propId].isedited=true;
				
			}
		
		
		}
	
	
	this.element.children( "option" ).map(function() {
			$(this).remove();
		});
	var element= this.element;
	
	if(type == 'relation'){
			var form_data = {
				action : 'getAllRelationTypes',
				searchstring : request.term
				 
				};
			}
	
	else if(type == 'type'){
			var form_data = {
				action : 'getClasses',
				searchstring : request.term
				 
				};
			}
	else if(type == 'property'){
			var form_data = {
				action : 'getAllPropertyTypes',
				searchstring : request.term
				 
				};
			}
	else if(type == 'object'){
			var form_data = {
				action : 'searchObjectsForSearch',
				searchstring : request.term
				 
				};
			}
		$.ajax({
			type: "POST",
			url: "ajax_server.php",
			data: form_data,
			dataType: "json",
			success: function(answer){
				
				if(answer.check == "suc"){
					var appendedtest = '';
					for(var i in answer.result){
							if(type=='object'){
									var text = "<option value='"+answer.result[i].levenprop+"' oid="+i+" title='";
									for( var j in answer.result[i].properties){
											text += answer.result[i].properties[j].label+" :"+ answer.result[i].properties[j].value+ " ,";
										
										}
									text += "'>"+answer.result[i].levenprop+"</option>";
								//console.log(id);
									$('#'+id).append(text);
								}
							else{
								var text="<option value='"+answer.result[i].class+"'>"+answer.result[i].label+"</option>";
								//console.log(answer.result[i].class);
								
								$('#'+id).append(text);
							
							}
							//console.log($('#'+id).html());
							
							//console.log($('#'+id).html());
							
						
					}
					//console.log(appendedtest);
					//console.log(id);
					if(propId != '' && id.indexOf('oid') > -1){
									
									if(type == 'object'){
											$('#'+id).append("<option class=''  nodeId='"+nodeId+"' propId='"+propId+"' value='"+graph.nodes[nodeId].properties[propId].value+"'>"+graph.nodes[nodeId].properties[propId].value+"</option>");
										
										
										}
									else if(type == 'property'){
											$('#'+id).append("<option  nodeId='"+nodeId+"' propId='"+propId+"' value='"+graph.nodes[nodeId].properties[propId].value+"'>"+graph.nodes[nodeId].properties[propId].label+"</option>");
											
										
										}
									var e = document.getElementById(id);
									e.selectedIndex=e.options.length-1;
								
								}
					
				
					
					
			
				var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
				var options = element.children( "option" ).map(function() {
					var text = $( this ).text();
					if ( this.value && ( !request.term || matcher.test(text) ) )
						return {
							label: text,
							value: text,
							option: this
						};
				});
	
				response( options);
					
				}
			}
		});
},
_removeIfInvalid: function( event, ui ) {
	

	//	console.log('trying to remove invalid value');
	var id=this.element[0].id;
	var type = $('#'+id).attr('class');
	// Selected an item, nothing to do
	if ( ui.item ) {
		return;
	}
	// Search for a match (case-insensitive)
	var value = this.input.val(),
	valueLowerCase = value.toLowerCase(),
	valid = false;
	this.element.children( "option" ).each(function() {
		if ( $( this ).text().toLowerCase() === valueLowerCase ) {
			this.selected = valid = true;
			return false;
		}
	});
	// Found a match, nothing to do
	
	var nodeId=$('#'+id).attr('nodeId'); 
	var propId=$('#'+id).attr('propId');
	
	if(nodeId != '' && propId != '' && id.indexOf('oid') > -1){
			
			if(type == 'object'){
				graph.nodes[nodeId].properties[propId].value=value;
				
				if(graph.nodes[nodeId].properties[propId].oldvalue != value)graph.nodes[nodeId].properties[propId].isedited=true;
			}else if(type == 'property'){
				graph.nodes[nodeId].properties[propId].type=value;
				var labelsplit= value.split('#');
				var label= value;
				
				
	
				graph.nodes[nodeId].properties[propId].label=label;
				
				if(graph.nodes[nodeId].properties[propId].oldtype != value)graph.nodes[nodeId].properties[propId].isedited=true;
				
			}
		
		
		}  
	
	if ( valid ) {
		return;
	}
	// Remove invalid value
	
	this.input.autocomplete( "instance" ).term = value;
	
	//TO DO: popup: wollen sie wirklich eine neue... erstellen?
	
	$('#'+id).append("<option value='"+value+"'>"+value+"</option>");
	
	var e= document.getElementById(id);
	e.selectedIndex=e.options.length-1;
	
	
},
_destroy: function() {
	
	this.wrapper.remove();
	this.element.show();
}
});
})( jQuery );

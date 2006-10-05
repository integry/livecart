{literal}
<script language="javascript">

	doc = new DocumentHelper();

	var	current_id = 0;
	var	current_div = '';
	
	function makeOnLoad() {

	  	{/literal}{$javascript}{literal}
	}
		
	function eventGroup(id, spanID) {
		
		current_id = id;	
		if (id > 0) {

		  	doc.getLayer("delete_span").style.visibility = 'visible';		  
		} else {

		  	doc.getLayer("delete_span").style.visibility = 'hidden';
		}	
	
		if (current_div != '') {
		 
		 	doc.getLayer(current_div).className = 'treeMenuNode';		 
		}
		
	  	doc.getLayer(spanID).className = 'treeMenuNodeSelected';		
	  	current_div = spanID;
	  	
	  	if (id > 0) {
	
		  	post = new Array();
		  	post['node_id'] = current_id;
	  		http('POST', {/literal}"{link controller=backend.rolegroup action=viewForm}"{literal} , div_response, post, true);
	  	} else {
		    
		    div_response('');
		}
	}
	
	function eventAdd() {

	  	post = new Array();
	  	post['node_id'] = current_id ;
	  	http('POST', {/literal}"{link controller=backend.rolegroup action=addForm}"{literal}, div_response, post, true);
	}

	function eventMove() {
	  
	  	post = new Array();
	  	post['node_id'] = current_id ;
	  	http('POST', {/literal}"{link controller=backend.rolegroup action=moveForm}"{literal}, div_move_response, post);
	}
	
	function eventCancel() {

	  	eventGroup(current_id, current_div);
	}
	
	function eventDelete() {
	  
	  	if (confirm('Are you sure you want to delete group?')) {
			
			document.delete_form.del.value = current_id;		
			document.delete_form.submit();
		}
	}
	
	function div_response(data) {
		  
		doc.getLayer("main").innerHTML = data; 			
	}
		
	function div_move_response(data) {

		doc.getLayer("main").innerHTML = data.output;
		eval(data.javascript); 			
	}
	
	function makeMove(id) {
	  
	  	document.move_form.moveto.value = id;	  	
		document.move_form.submit();
	}
	

</script>
<style type="text/css">
	#nav {
		background-color: #FAFAFA;
		float:left;
		width: 175pt;
		height: 300pt;
		padding: 10pt;
		margin-left: 10pt;
	}	   
</style>  
{/literal}
<h2>User groups</h2>
{$group}
<br>
<a href="javascript:eventAdd()">Add Subgroup</a><br>
<span id="delete_span" style="visibility: hidden">
	<a href="javascript: eventMove();">
		Move to
	</a> &nbsp;
	<a href="javascript: eventDelete();">
		Delete Group
	</a>		
</span>
<br>
<form name="delete_form" method="post" action="{link controller=backend.rolegroup action=delete}">
	<input type="hidden" name="del" >
</form>	
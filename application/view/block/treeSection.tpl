{literal}
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
<h2>{$title}</h2>
{$group}
<br>
<br>
<a href="{link controller="backend.catalog" action="addForm" id=$id}" 
		onmouseover="{hintshow content="Adds soubgroup to a group." bgcolor="white" moveWithMouse=1}" 
		onmouseout="{hinthide}">{translate text="_addSubgroup"}</a><br>
{if $id}	
<a href="{link controller="backend.catalog" action="moveForm" id=$id}"
		onmouseover="{hintshow content="Moves group to another parent group." bgcolor="white" moveWithMouse=1}" 
		onmouseout="{hinthide}">{translate text="_moveTo"}</a>
<br>
<a href="{confirmedlink controller=backend.catalog action=delete id=$id question='Are you sure you want to delete group?'}">{translate text="_deleteGroup"}</a>	
<br>
<a href="{link controller=backend.product action=addForm id=$id}">{translate text="_addProduct"}</a>	
{/if}


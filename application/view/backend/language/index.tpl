{loadScriptaculous}
{includeJs file=backend/activeList.js}

{pageTitle}{translate text=_admin_languages}{/pageTitle}

{literal}
<script language="javascript">	

	function langListHandler(listId)
	{
		this.create(listId);
	}
	
	langListHandler.prototype = new activeList();
	
	langListHandler.prototype.getDeleteUrl = function(id)
	{	  
		return {/literal}"{link controller=backend.language action=delete}" + id;{literal}
	}  

	langListHandler.prototype.getEditUrl = function (id)
	{	  
	}  
  
	langListHandler.prototype.getSortUpdateUrl = function (order)
	{	  
		return {/literal}"{link controller=backend.language action=saveorder}?draggedId=" + this.draggedId + "&" + order;{literal}
	}  

	function setEnabled(langId, status) 
	{
		url = {/literal}"{link controller=backend.language action=setEnabled}" + langId + "?status=" + status;{literal}  

		img = document.createElement('img');
		img.src = "image/backend/list/indicator.gif";
				
		checkBox = document.getElementById('languageList_enable_' + langId);
		checkBox.parentNode.replaceChild(img, checkBox);
		
		var updater = new Ajax.Updater('languageList_' + langId, url);
	}
	
	function slideForm(id, menuId)
	{
		Effect.Appear(id, {duration: 0.15});	  	
		document.getElementById(menuId).style.display = 'none';
		setTimeout('document.getElementById("' +  id + '").focus()', 100);
	}

	function restoreMenu(blockId, menuId)
	{
		Effect.Fade(blockId, {duration: 0.15});	  	
		document.getElementById(menuId).style.display = 'block'; 	
	}
</script>
{/literal}

{pageMenu id=pageMenu}
	{menuItem}
		{menuCaption}{t _add_language}{/menuCaption}
		{pageAction}slideForm('addLang', 'pageMenu'){/pageAction}
 	{/menuItem}
	{menuItem}
		{menuCaption}{t _update_from_files}{/menuCaption}
		{menuAction}{link language=$language controller=backend.language action=update}{/menuAction} 
	{/menuItem}
{/pageMenu}

{literal}
<style>
.slideForm {
	padding: 10px;
	background-color: #E6E6E6;	  
}
.accessKey {
  	color: red;
  	border-bottom: 1px solid red;
}
</style>
{/literal}

<br />

<div id="addLang" class="slideForm" style="display:none;" onkeydown="{literal}if (getPressedKey(event) == KEY_ESC) {restoreMenu('addLang', 'pageMenu');} {/literal} return true;" onFocus="document.getElementById('addLang-sel').focus();" tabIndex=1>
	<div onFocus="">	
		<form name="addform" method="post" action="{link language=$language controller=backend.language action=add}">
			<select name="new_language" id="addLang-sel" style="width: 200px" tabIndex=3 onKeyDown="{literal}if (getPressedKey(event) == KEY_ENTER) {this.form.submit();} {/literal} return true;">
			   {html_options options=$languages_select}
			</select>
			<input type="submit" value="{t _add_language}" style="width: 120px" name="sm" tabIndex=4>
			or <a href="#" onClick="restoreMenu('addLang', 'pageMenu'); return false;">Cancel</a>
		</form>	
	</div>
</div>


<form name="activeform" method="post" action="{link language=$language controller=backend.language action=setEnabled}">
	<input type="hidden" name="change_active">
	<input type="hidden" name="change_to">
</form>
<form name="currentform" method="post" action="{link language=$language controller=backend.language action=setDefault}">	
	<input type="hidden" name="change_to">
</form>

{literal}
<style>
.enabled_0 {color: #AAAAAA;}
.enabled_1 {}
.listSortHover {background-color: #DDDDDD;}
</style>
{/literal}

{literal}
<script>

	function addlog(info)
	{
		document.getElementById('log').innerHTML += info + '<br />';  
	}

	function deselectText() 
	{
		if (document.selection)
		{
			document.selection.empty();
		}
		else if (window.getSelection)
		{
		    window.getSelection().removeAllRanges();
		}
	}

	var KEY_ENTER = 13;
	var KEY_ESC   = 27;
	var KEY_UP    = 38;	
	var KEY_DOWN  = 40;	
	var KEY_DEL   = 46;

	function getPressedKey(e)
	{
	    // IE
		if (window.event) 
	    {
	    	keynum = e.keyCode;
	    }

	    // Netscape/Firefox/Opera
		else if (e.which) 
	    {
	    	keynum = e.which;
	    }	  	
	    
	    return keynum;
	}

</script>

{/literal}

{activeList id="languageList" sortable=true deletable=true handlerClass=langListHandler}
	{foreach from=$languagesList item=item}
		{include file="backend/language/listItem.tpl"}
	{/foreach}
{/activeList}

<!-- {maketext text="_statistic_languages_full" params="$count_all,$count_active"}. -->

<div id="log"></div>
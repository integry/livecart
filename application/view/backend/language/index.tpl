{loadScriptaculous}
{includeJs file=backend/activeList.js}
{includeJs file=backend/keyboard.js}

{pageTitle}{t _admin_languages}{/pageTitle}

<fieldset class="inlineHelp">
	<legend>{t _help_index_title}</legend>
	{translate|nl2br text="_help_index"}
	<Br><Br><br>
</fieldset>

{literal}
<script language="javascript">	

	function setEnabled(langId, status) 
	{
		url = {/literal}"{link controller=backend.language action=setEnabled}/" + langId + "?status=" + status;{literal}  

		img = document.createElement('img');
		img.src = "image/backend/list/indicator.gif";
				
		checkBox = document.getElementById('languageList_enable_' + langId);
		checkBox.parentNode.replaceChild(img, checkBox);
		
		var updater = new Ajax.Updater('languageList_container_' + langId, url);
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
{/pageMenu}

{*
<ul id="pageMenu">
	<li>
		<a onClick="slideForm('addLang', 'pageMenu')">{t _add_language}</a>
	</li>
</ul>
*}

{literal}
<style>
.activeList_icons {
  	float: left;  	
  	vertical-align: top;
  	display: block;
}
.activeList li {
  	clear: both;
  	list-style: none;
}

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

<div id="addLang" class="slideForm" style="display:none;" onkeydown="{literal}if (getPressedKey(event) == KEY_ESC) {restoreMenu('addLang', 'pageMenu');} {/literal} return true;" onFocus="document.getElementById('addLang-sel').focus();" tabIndex=1>
	<div onFocus="">	
		<form name="addform" method="post" action="{link language=$language controller=backend.language action=add}">
			<select name="new_language" id="addLang-sel" style="width: 200px" tabIndex=3 onKeyDown="{literal}if (getPressedKey(event) == KEY_ENTER) {this.form.submit();} {/literal} return true;">
			   {html_options options=$languages_select}
			</select>
			<input type="submit" value="{t _add_language}" name="sm" tabIndex=4>
			{t _or} <a href="#" onClick="restoreMenu('addLang', 'pageMenu'); return false;">{t _cancel}</a>
		</form>	
	</div>
</div>

<br />

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


<ul id="languageList" class="activeList_add_delete">
{foreach from=$languagesList item=item}
<li id="languageList_{$item.ID}" class="activeList_add_sort">
	{include file="backend/language/listItem.tpl"}
</li>
{/foreach}
</ul>

{literal}
<script type="text/javascript">
	
	
     new LiveCart.ActiveList('languageList', {
         beforeEdit:     function(li) { return 'sort.php?' },
         beforeSort:     function(li, order) 
		 { 
		   /* li.progress.style.display = 'none';  */
		   return '{/literal}{link controller=backend.language action=saveorder}{literal}?draggedId=' + this.getRecordId(li) + '&' + order 
		   },
         beforeDelete:   function(li)
         {
             if(confirm('{/literal}{translate text=_confirm_delete}{literal}')) return '{/literal}{link controller=backend.language action=delete}{literal}' + this.getRecordId(li)
         },
         afterEdit:      function(li, response) {  },
         afterSort:      function(li, response) {  },
         afterDelete:    function(li, response)  { Element.remove(li); }
     });

</script>
{/literal}

<!-- {maketext text="_statistic_languages_full" params="$count_all,$count_active"}. -->
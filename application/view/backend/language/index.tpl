{pageTitle}{translate text=_admin_languages}{/pageTitle}

{literal}
<script language="javascript">	
	function eventsetEnabled(change_active, change_to) {
	  		
	  	document.activeform.change_active.value = change_active;
	  	document.activeform.change_to.value = change_to;
		document.activeform.submit();
	}
	
	function eventsetDefault(change_to) {
	  		
	  	//document.currentform.change_active.value = change_active;
	  	document.currentform.change_to.value = change_to;
		document.currentform.submit();
	}		
</script>
{/literal}
<a href="{link language=$language controller=backend.language action=update}">
	{t _update_from_files}
</a>

{pageMenu}
	{menuItem}
		{menuCaption}{t _update_from_files}!!!{/menuCaption}
{*		{menuAction}{link language=$language controller=backend.language action=update}{/menuAction} *}
	{/menuItem}
	{menuItem}
{*		{caption}{t _add_language}{/caption}
		{pageAction}showAddForm("addLang"){/pageAction}
*} item 2	{/menuItem}
{/pageMenu}

{*
{addForm id=addLang}
	FORM CONTENT
{/quickAddForm}
*}

<br><br>
<form name="addform" method="post" action="{link language=$language controller=backend.language action=add}">
	<select name="new_language" style="width: 200px">
	   {html_options options=$languages_select}
	</select>
	<input type="submit" value="{translate text=_add_language}" style="width: 120px">
</form>
<form name="activeform" method="post" action="{link language=$language controller=backend.language action=setEnabled}">
	<input type="hidden" name="change_active">
	<input type="hidden" name="change_to">
</form>
<form name="currentform" method="post" action="{link language=$language controller=backend.language action=setDefault}">	
	<input type="hidden" name="change_to">
</form>

{literal}
<style>
.enabled_0 {color: #CCCCCC;}
</style>
{/literal}

{*
{list sortable=true editable=true deleteable=true}
{foreach from=$languagesList item=item}
	{listItem}
		{$item.ID|upper} ({$item.name})	
	{/listItem}
{/foreach}
{/list}
*}

<ul>
{foreach from=$languagesList item=item}
	<li>
		{$item.ID|upper} ({$item.name})<br />
		<a href="{link language=$language controller=backend.language action=edit id=$item.ID}" class="listLink">
			{translate text=_edit_definitions}
		</a>
	</li>
{/foreach}
</ul>


<table style="border-collapse: collapse;">
	<tr >
		<td width=50 style=" border-width: 1px 1px 1px 1px;	border-color: black black black black; border-style: inset inset inset inset;">{translate text=_code}
		</td>
		<td width=150 style=" border-width: 1px 1px 1px 1px; border-color: black black black black; border-style: inset inset inset inset;">{translate text=_language}			
		</td>		
		<td width=175 style=" border-width: 1px 1px 1px 1px; border-color: black black black black; border-style: inset inset inset inset;">			
		</td>
		<td width=60 style=" text-align:center; border-width: 1px 1px 1px 1px; border-color: black black black black; border-style: inset inset inset inset;">{translate text=_active}			
		</td>
		<td width=60 style=" text-align:center; border-width: 1px 1px 1px 1px; border-color: black black black black; border-style: inset inset inset inset;">{translate text=_default}			
		</td>
	</tr>
{foreach from=$languagesList item=item}
	<tr >
		<td width=50 style=" border-width: 1px 1px 1px 1px;	border-color: black black black black; border-style: inset inset inset inset;">
			{$item.ID|upper}
		</td>
		<td width=150 style=" border-width: 1px 1px 1px 1px; border-color: black black black black; border-style: inset inset inset inset;">
			{$item.name}
		</td>		
		<td width=175 style=" border-width: 1px 1px 1px 1px; border-color: black black black black; border-style: inset inset inset inset;">
			<a href="{link language=$language controller=backend.language action=edit id=$item.ID}">
				{translate text=_edit_definitions}
			</a>
		</td>
		<td width=60 style=" text-align:center; border-width: 1px 1px 1px 1px; border-color: black black black black; border-style: inset inset inset inset;">
			<input type="checkbox" 	{if $item.isEnabled}checked{/if} {if $item.isDefault}disabled{/if}
				onclick="eventsetEnabled('{$item.ID}',
							 {if $item.isEnabled}0{else}1{/if} )  ">
		</td>
		<td width=60 style=" text-align:center; border-width: 1px 1px 1px 1px; border-color: black black black black; border-style: inset inset inset inset;">
			{if $item.isEnabled}
			<input name="current" type="radio" {if $item.isDefault}checked{/if} 
				onclick="eventsetDefault('{$item.ID}')">
			{/if}
		</td>
	</tr>	
{/foreach}
</table>
{maketext text="_statistic_languages_full" params="$count_all,$count_active"}.
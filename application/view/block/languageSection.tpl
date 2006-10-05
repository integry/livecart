<h3>{translate text=_languages_section}</h3> 
<a href="{link controller=$controller action=$action id=$id}">
	{translate text=_default}
</a>
<br>
{foreach from=$languagesList item=item}
	<a href="{link language=$item.ID controller=$controller action=$action id=$id}">{$item.ID|upper}</a>
{/foreach}
<br><br>
<a href="{link language=$language controller=backend.language action=information}">
	{translate text=_information}
</a><br>
<a href="{link language=$language controller=backend.language action=index}">
	{translate text=_admin_languages}
</a>
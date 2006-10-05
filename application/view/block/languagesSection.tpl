<h3>{translate text=_languages_section}</h3> 
<a href="{link controller=$controller action=$action id=$id}">
	{translate text=_default}
</a>
<br>
{foreach from=$languagesList item=item}
	<a href="{link language=$item.code controller=$controller action=$action id=$id}">{$item.code|upper}</a>
{/foreach}
<br><br>
<a href="{link language=$language controller=backend.languages action=information}">
	{translate text=_information}
</a><br>
<a href="{link language=$language controller=backend.languages action=index}">
	{translate text=_admin_languages}
</a>
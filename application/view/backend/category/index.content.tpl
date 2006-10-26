{translate text="_currentLanguage"}: {$current_lang}
<br>
{translate text="_selectLanguage"}:
{foreach from=$langs item=item}
	<a href="javascript: multi('{$item.ID}')">
		{$item.ID|upper}
	</a>
{/foreach}
<br>
{$content}
<form name="multiform" method="post" action="{link controller="backend.catalog" action="change" id=$id}">
	<input type="hidden" name="multi_language">
</form>
{literal}
<script language="javascript">
	function multi(lang) {
	  
	  	document.multiform.multi_language.value = lang;
	  	document.multiform.submit();
	}
</script>
{/literal}
{if $current_lang != "en"}
{translate text="en"}
<br>
<b>{translate text="_name"}:</b> {$english_name}<br>
<b>{translate text="_description"}:</b> {$english_description}<br>
{/if}
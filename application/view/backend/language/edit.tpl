{pageTitle}{translate text=_language_definitons} (<img src="image/localeflag/{$id}.png" /> {$edit_language}){/pageTitle}

{*
{literal} 	
<script type="text/javascript"> 
	var translations = {/literal}{$translations}{literal}
	var translations = {/literal}{$translations}{literal}
</script>
{/literal}
*}

<form id="navLang" method="post" style="margin-bottom: 10px;" action="{link controller=backend.language action=edit id=$id}">
	<input type="hidden" name="langFileSel" />

	<strong>{t _show_words}:</strong> 
	
	<input type="radio" name="show" value="all" id="show-all" {$selected_all} onclick="this.form.submit()">
		<label for="show-all">{t _all}</label>
	</input>

	<input type="radio" name="show" value="notDefined" id="show-undefined" {$selected_not_defined} onclick="this.form.submit()">
		<label for="show-undefined">{t _not_defined}</label>
	</input>
	
	<input type="radio" name="show" value="defined" id="show-defined" {$selected_defined} onclick="this.form.submit()">
		<label for="show-defined">{t _defined}</label>
	</input>
</form>

<form name="editLang" method="post" action="{link controller=backend.language action=save id=$id}">
	
	<input type="hidden" name="langFileSel" />
	<input type="hidden" name="show" />
	
{*
	<table class="langTranslations dom-template">
		<caption>
			<img src="image/backend/icon/collapse.gif">
			<a href="#" onkeydown="{literal}if (getPressedKey(event) != KEY_TAB && getPressedKey(event) != KEY_SHIFT) {langToggleVisibility(this.parentNode.parentNode, '{$file}');}{/literal}" onClick="return false;">{$file}</a>
		</caption>
		<tbody style="display: none;">	
			{foreach from=$values key=key item=item name=trans}
				<tr{zebraRow}>
					<td class="lang-key">
						{$key}	
					</td>

					<td class="lang-translation">
						<input type="text" name="lang[{$file}][{$key}]" value="{$item|escape:"quotes"}">
						<span>{$en_definitions.$file.$key}</span>
					</td>
				<tr>	
			{/foreach}
		</tbody>	
	</table>
*}	

	{foreach from=$definitions key=file item=values}
		<table class="langTranslations">	
			<caption onClick="langToggleVisibility(this.parentNode, '{$file}');">
				<img src="image/backend/icon/collapse.gif">
				<a href="#" onkeydown="{literal}if (getPressedKey(event) != KEY_TAB && getPressedKey(event) != KEY_SHIFT) {langToggleVisibility(this.parentNode.parentNode, '{$file}');}{/literal}" onClick="return false;">{$file}</a>
			</caption>
			<tbody style="display: none;">	
				{foreach from=$values key=key item=item name=trans}
					<tr{zebraRow}>
						<td class="lang-key">
							{$key}	
						</td>

						<td class="lang-translation">
							<input type="text" name="lang[{$file}][{$key}]" value="{$item|escape:"quotes"}">
							<span>{$en_definitions.$file.$key}</span>
						</td>
					<tr>	
				{/foreach}
			</tbody>
		</table>
	{/foreach}

	<input type="submit" value="{t _save}">
</form>
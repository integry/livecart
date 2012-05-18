{form action="controller=backend.theme action=saveSettings" handle=$form onsubmit="new LiveCart.AjaxRequest(this); return false;"}
	<fieldset>
		<legend>{t _parent_themes}</legend>
		{section name="parents" start=1 loop=4}
			<p>
				{capture assign="label"}{maketext text=_parent_theme_x params=$smarty.section.parents.index}{/capture}
				{if $smarty.section.parents.index == 1}{assign var="tipIndex" value="1"}{else}{assign var="tipIndex" value="2"}{/if}
				<label>{toolTip label=$label hint="_tip_parent_`$tipIndex`"}:</label>
				{selectfield name="parent_`$smarty.section.parents.index`" options=$themes blank=true}
			</p>
		{/section}
	</fieldset>

	<fieldset class="controls">
		<input type="hidden" name="id" value="{$theme.name}" />
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" value="{tn _save}" class="submit" />
		{t _or}
		<a class="cancel" href="{link controller="backend.theme"}">{t _cancel}</a>
	</fieldset>

{/form}
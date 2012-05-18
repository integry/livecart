{if !$page.ID}
	<h1>{t _add_new_title}</h1>
{else}
	<h1>{$page.title_lang}</h1>
	<ul class="menu" id="staticPageMenu">
		<li id="codeMenu">
			<a href="#" class="menu" onclick="pageHandler.showTemplateCode(); return false;">{t _show_template_code}</a>
		</li>
	</ul>
{/if}

<fieldset id="templateCode" style="display: none;">
	<legend>{t _template_code}</legend>
	{t _code_explain}:
	<br /><br />
	{literal}
		&lt;a href="<strong>{pageUrl id={/literal}{$page.ID}{literal}}</strong>"&gt;<strong>{pageName id={/literal}{$page.ID}{literal}}</strong>&lt;/a&gt;
	{/literal}
</fieldset>

{form action="controller=backend.staticPage action=save" handle=$form onsubmit="pageHandler.save(this); return false;" method="post" role="page.update(edit),page.create(add)"}

<fieldset class="container" id="editContainer">

	{input name="title"}
		{label}{t _title}:{/label}
		{if $page.ID}
			{textfield class="wider" id="title_`$page.ID`"}
		{else}
			{textfield class="wider" id="title_`$page.ID`" onkeyup="$('handle').value = ActiveForm.prototype.generateHandle(this.value);"}
		{/if}
	{/input}

	{input name="menuInformation"}
		{checkbox}
		{label}{t _add_page_to_menu}{/label}
	{/input}

	{input name="menuRootCategories"}
		{checkbox}
		{label}{t _main_header_menu}{/label}
	{/input}

	{input name="handle"}
		{label}{t _handle}:{/label}
		{textfield id="handle"}
	{/input}

	{input name="text"}
		{label class="wide"}{t _text}:{/label}
		<div class="textarea" id="textContainer">
			{textarea class="tinyMCE longDescr" style="width: 100%;"}
		</div>
	{/input}

	{input name="metaDescription"}
		{label class="wide"}{t _meta_description}:{/label}
		{textarea style="width: 100%; height: 4em;"}
	{/input}

	{include file="backend/eav/fields.tpl" item=$page}

	{language}
		{input name="title_`$lang.ID`"}
			{label}{t _title}:{/label}
			{textfield class="wider"}
		{/input}

		{input name="text_`$lang.ID`"}
			{label class="wide"}{t _text}:{/label}
			{textarea class="tinyMCE longDescr" style="width: 100%;"}
		{/input}

		{input name="metaDescription_`{$lang.ID}`"}
			{label class="wide"}{t _meta_description}:{/label}
			{textarea style="width: 100%; height: 4em;"}
		{/input}

		{include file="backend/eav/fields.tpl" item=$page language=$lang.ID}

	{/language}

</fieldset>

<input type="hidden" name="id" value="{$page.ID}" />
<fieldset class="controls">
	<span class="progressIndicator" id="saveIndicator" style="display: none;"></span>
	<input type="submit" value="{tn _save}" class="submit" />
	{t _or}
	<a class="cancel" id="cancel" onclick="return false;" href="#">{t _cancel}</a>
</fieldset>

{/form}
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

	<p>
		<label for="title_{$page.ID}" class="wide">{t _title}:</label>
		<fieldset class="error">
			{if $page.ID}
				{textfield name="title" class="wider" id="title_`$page.ID`"}
			{else}
				{textfield name="title" class="wider" id="title_`$page.ID`" onkeyup="$('handle').value = ActiveForm.prototype.generateHandle(this.value);"}
			{/if}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>

	<fieldset class="isInformationBox error">
		{checkbox name="isInformationBox" class="checkbox" id="isInformationBox_`$page.ID`"}
		<label for="isInformationBox_{$page.ID}" class="checkbox">{t _inf_menu}</label>
	</fieldset>

	<p>
		<label for="handle" class="wide">{t _handle}:</label>
		<fieldset class="error">
			{textfield name="handle" id="handle"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>

	<p>
		<label for="text_{$page.ID}" class="wide">{t _text}:</label>
		<fieldset class="error">
			<div class="textarea" id="textContainer">
				{textarea class="tinyMCE longDescr" name="text" id="text_`$page.ID`" style="width: 100%;"}
				<div class="errorText hidden" style="margin-top: 5px;"></div>
			</div>
		</fieldset>
	</p>

	{language}
		<p>
			<label for="title_{$lang.ID}" class="wide">{t _title}:</label>
			<fieldset class="error">
				{textfield name="title_`$lang.ID`" class="wider"}
				<div class="errorText hidden"></div>
			</fieldset>
		</p>

		<p>
			<label for="text_{$lang.ID}" class="wide">{t _text}:</label>
			<fieldset class="error">
				<div class="textarea" id="textContainer">
					{textarea class="tinyMCE longDescr" name="text_`$lang.ID`" style="width: 100%;"}
					<div class="errorText hidden" style="margin-top: 5px;"></div>
				</div>
			</fieldset>
		</p>
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
{if !$page.ID}
	<h1>{t _add_new_title}</h1>
{else}
	<h1>{$page.title_lang}</h1>
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
		<label for="title" class="wide">{t _title}:</label>
		<fieldset class="error">
			{if $page.ID}
				{textfield name="title" class="wider"}
			{else}
				{textfield name="title" class="wider" onkeyup="$('handle').value = ActiveForm.prototype.generateHandle(this.value);"}			
			{/if}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>

	<p>
		<label for="title" class="wide">{t _handle}:</label>
		<fieldset class="error">
			{textfield name="handle"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	
	<p>
		<label for="text" class="wide">{t _text}:</label>
		<fieldset class="error">
			<div class="textarea" id="textContainer">
				{textarea class="longDescr" name="text" style="width: 100%;"}
				<div class="errorText hidden" style="margin-top: 5px;"></div>
			</div>			
		</fieldset>
	</p>

    {foreach from=$languages item="language"}
		<fieldset class="expandingSection">
		<legend>{t Translate to}: {$language.originalName}</legend>
			<div class="expandingSectionContent">
			    
				<p>
					<label for="title_{$language.ID}" class="wide">{t _title}:</label>
					<fieldset class="error">
						{textfield name="title_`$language.ID`" class="wider"}
						<div class="errorText hidden"></div>
					</fieldset>
				</p>
			
				<p>
					<label for="text_{$language.ID}" class="wide">{t _text}:</label>
					<fieldset class="error">
						<div class="textarea" id="textContainer">
							{textarea class="longDescr" name="text_`$language.ID`" style="width: 100%;"}
							<div class="errorText hidden" style="margin-top: 5px;"></div>
						</div>			
					</fieldset>
				</p>

			</div>
		</fieldset>
    {/foreach}

	<p>
		{checkbox name="isInformationBox" class="checkbox"}
		<label for="isInformationBox" class="checkbox">{t _inf_menu}</label>
	</p>

	<script type="text/javascript">
		var expander = new SectionExpander();
	</script>

</fieldset>

<input type="hidden" name="id" value="{$page.ID}" />
<fieldset class="controls">
    <span class="progressIndicator" id="saveIndicator" style="display: none;"></span>
    <input type="submit" value="{tn _save}" class="submit" /> 
    {t _or} 
    <a class="cancel" id="cancel" onclick="return false;" href="#">{t _cancel}</a>
</fieldset>

{/form}
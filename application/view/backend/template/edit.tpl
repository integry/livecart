<h1>{$fileName}</h1>

{form handle=$form action="controller=backend.template action=save" method="POST" id="templateForm"}

	{if $new || $template.isCustomFile}
		<p>
			{{err for="fileName"}}
				<label style="margin-top: 9px;">{t _template_file_name}:</label>
				{textfield class="text wide"}
			{/err}
		</p>
		<div class="clear" style="margin-bottom: 1em;"></div>
	{/if}

	{textarea name="code" id="code"}
	{hidden name="file" id="file"}

	{if $new}
		{hidden name="new" value="true"}
	{/if}

	<fieldset class="controls" {denied role="template.save"}style="display: none;"{/denied}>
		<div class="saveThemeSelector" style="float: right;">
			{t _save_for_theme}: {selectfield name=theme options=$themes blank=true}
		</div>

		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{tn _save_template}" />
		{t _or}
		<a id="cancel" class="cancel" href="{link controller="backend.template"}">{t _cancel}</a>
	</fieldset>
{/form}

{literal}
<script type="text/javascript">
	$('code').value = {/literal}decode64("{$code}");{literal};
	editAreaLoader.baseURL = "{/literal}{baseUrl}javascript/library/editarea/{literal}";
</script>
{/literal}
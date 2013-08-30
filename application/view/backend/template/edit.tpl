<h1>[[fileName]]</h1>
{form handle=$form action="backend.template/save" method="POST" class="templateForm" id="templateForm_`$tabid`"}
	<div class="minimenu" id="minimenu_[[tabid]]">
		<span class="progressIndicator" style="display:none;"></span>
		{selectfield class="version" id="version_`$tabid`" options=$template.backups}
		{selectfield class="othertheme" id="othertheme_`$tabid`" options=$template.otherThemes}
	</div>

	{% if $new || $template.isCustomFile %}
		[[ textfld('fileName', '_template_file_name') ]]
	{% endif %}
	<input type="hidden" value="[[tabid]]" name="tabid" />

	{textarea name="code" class="code" id="code_`$tabid`"}
	{hidden name="file" id="file"}

	{% if !empty(new) %}
		{hidden name="new" value="true"}
	{% endif %}

	<fieldset class="controls" {denied role="template.save"}style="display: none;"{/denied}>
		<div class="saveThemeSelector" style="float: right;">
			{t _save_for_theme}: {selectfield name=theme options=$themes blank=true id="theme_`$tabid`"}
		</div>

		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{t _save_template}" />
		{t _or}
		<a id="cancel_[[tabid]]" class="cancel" href="[[ url("backend.template") ]]">{t _cancel}</a>
	</fieldset>
	<script type="text/javascript">
		$('code_[[tabid]]').value = decode64("[[code]]");
		editAreaLoader.baseURL = "{baseUrl}javascript/library/editarea/";
	</script>
{/form}
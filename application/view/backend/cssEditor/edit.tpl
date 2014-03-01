<h1>[[fileName]]</h1>

<div
	onclick="TabControl.prototype.getInstance('tabContainer').activateTab(('tabColors'));"
	class="warning cssAndStyleTab" id="notice_changes_colors_and_styles_tab_[[tabid]]" style="display:none;"
>{t _notice_changes_colors_and_styles_tab}</div>

{form handle=form action="backend.cssEditor/save" method="POST" class="templateform" id="templateForm_`tabid`"}

	{% if new || template.isCustomFile %}
		[[ textfld('fileName', '_template_file_name') ]]
	{% endif %}

	<div class="minimenu" id="minimenu_[[tabid]]">
		<span class="progressIndicator" style="display:none;"></span>
		{selectfield class="version" id="version_`tabid`" options=template.backups}
	</div>
	{textarea name="code" id="code_[[tabid]]" class="code"}
	{hidden name="file" id="file_[[tabid]]"}

	{% if !empty(new) %}
		{hidden name="new" value="true"}
	{% endif %}

	<fieldset class="controls" {denied role="template.save"}style="display: none;"{/denied}>
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{t _save_css}" />
		{% if isset(noTabHandling) == false %}
			{t _or}
			<a id="cancel_[[tabid]]" class="cancel" href="[[ url("backend.cssEditor") ]]">{t _cancel}</a>
		{% endif %}
	</fieldset>
{/form}


	<script type="text/javascript">
		Backend.isCssEdited["[[tabid]]"] = false;
		if (Backend.Theme.prototype.isStyleTabChanged("[[tabid]]"))
		{
			Backend.Theme.prototype.styleTabChanged("[[tabid]]");
		}
		('code_[[tabid]]').value = decode64("[[code]]");;
		editAreaLoader.baseURL = "{baseUrl}javascript/library/editarea/";
	</script>


{% if !empty(noTabHandling) %}
	<script type="text/javascript">
		new Backend.CssEditorHandler(('templateForm_[[tabid]]'), null, '[[tabid]]');
	</script>
{% endif %}
{% block title %}{t _edit_template}: [[fileName]]{{% endblock %}
{includeCss file="backend/Template.css"}
{includeJs file="backend/Template.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/editarea/edit_area_full.js"}

{include file="layout/backend/meta.tpl"}

{literal}
<style type="text/css">
	body
	{
		background-image: none;
		padding-left: 10px;
		padding-right: 10px;
	}
</style>
{/literal}

<div id="pageTitleContainer">
	<div id="pageTitle">[[PAGE_TITLE]]</div>
</div>

{form handle=$form action="controller=backend.template action=save" method="POST" id="templateForm" class="templateForm"}

	{textarea name="code" class="code" id="code_undefined"}

	<fieldset class="controls">
		{hidden name="file" id="file"}

		<div style="float: left;{denied role="template.save"} display: none;{/denied}">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="hidden" name="theme" value="[[theme]]" />
			<input type="submit" class="submit" value="{tn _save_template}" />
			{t _or}
			<a class="cancel" href="#" onclick="window.close();">{t _cancel}</a>
		</div>

		<div style="float: right;">
			<div class="yellowMessage" style="display: none;">
				<div>
					{t _template_has_been_successfully_updated}
				</div>
			</div>
			<div class="redMessage" style="display: none;">
				<div>
					{t _could_not_update_template}
				</div>
			</div>
		</div>
	</fieldset>

{/form}


{literal}
	<script type="text/javascript">
		editAreaLoader.baseURL = "{/literal}{baseUrl}javascript/library/editarea/{literal}";
		$('code_undefined').value = {/literal}decode64("[[code]]");{literal};
		new Backend.TemplateHandler($('templateForm'));
	</script>
{/literal}
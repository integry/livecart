{pageTitle}{t Edit Template File}: {$fileName}{/pageTitle}
{includeCss file="backend/Template.css"}
{includeJs file="backend/Template.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/Validator.js"}

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
	<div id="pageTitle">{$PAGE_TITLE}</div>
</div>

{form handle=$form action="controller=backend.template action=save" method="POST" id="templateForm"}

	{textarea name="code" id="cd"}
	
	<fieldset class="controls">
		{hidden name="file"}
		
		<div style="float: left;">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit" value="{tn Save Template}" /> 
			{t _or} 
			<a class="cancel" href="#" onclick="window.close();">{t _cancel}</a>
		</div>
		
		<div style="float: right;">
			<div class="yellowMessage" style="display: none;">
				<div>
					{t Template saved successfuly}
				</div>
			</div>
			<div class="redMessage" style="display: none;">
				<div>
					{t Template could not be saved}
				</div>
			</div>
		</div>		
	</fieldset>
	
{/form}

{literal}
	<script type="text/javascript">
		new Backend.TemplateHandler($('templateForm'));
	</script>
{/literal}
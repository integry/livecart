<h1>{$fileName}</h1>

{form handle=$form action="controller=backend.template action=save" method="POST" id="templateForm"}

	{textarea name="code"}
	
	<fieldset class="controls">
		{hidden name="file"}
		
		<div style="float: left;">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit" value="{tn Save Template}" /> 
			{t _or} 
			<a id="cancel" class="cancel" href="{link controller="backend.template"}">{t _cancel}</a>
		</div>
	</fieldset>
	
{/form}

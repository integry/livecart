<h1>{$fileName}</h1>

{form handle=$form action="controller=backend.template action=save" method="POST" id="templateForm"}

	{textarea name="code" id="code"}
	{hidden name="file" id="file"}
    	
	<fieldset class="controls" {denied role="template.save"}style="display: none;"{/denied}>
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
<h1>{$fileName|@substr:9}</h1>

{form handle=$form action="controller=backend.template action=saveEmail" method="POST" id="templateForm"}

	{if !$template.isFragment}
	<p>
		<label class="wide">{t _subject}:</label>
		{textfield name="subject" id="subject" class="text wide"}	
	</p>
	{/if}
	
	<p>
		{if !$template.isFragment}
			<label class="wide">{t _body}:</label>
		{/if}
		{textarea name="body" id="body" class="body"}
	</p>
	
	{language}
		{if !$template.isFragment}
			<p>
				<label class="wide">{t _subject}:</label>
				{textfield name="subject_`$lang.ID`" class="text wide"}	
			</p>
		{/if}
		
		<p>
			{if !$template.isFragment}
				<label class="wide">{t _body}:</label>
			{/if}
			{textarea name="body_`$lang.ID`" id="body_`$lang.ID`" class="body"}
		</p>
	{/language}
	
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
	$('body').value = {/literal}decode64("{$template.bodyEncoded}");{literal};
	editAreaLoader.baseURL = "{/literal}{baseUrl}javascript/library/editarea/{literal}";
</script>
{/literal}
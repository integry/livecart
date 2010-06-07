<h1><a href="{link controller=api action=doc}">{t _livecart_api}</a> &gt; <a href="{link controller=api action=docview query="class=`$className`"}">{$info.path}</a>.{$action}</h1>

{literal}
<style>
	.xmlSample
	{
		border: 1px solid #ccc;
		padding: 0.5em;
	}
	
	.xmlSampleBlock
	{
		margin-bottom: 2em;
		width: 600px;
	}
	
	.sampleTest
	{
		float: right;
	}
	
	.xmlSampleComment
	{
		color: #666;
		font-size: smaller;
		padding: 6px 0;
	}
	
	.apiFields
	{
		border-spacing: 0;
		border-collapse: collapse;
	}
	
	.apiFields td
	{
		font-size: smaller;
		padding: 4px;
		border: 1px solid #ddd;
	}
	
	.fieldGroup td
	{
		background-color: #ddd;
		font-weight: bold;
	}

</style>
{/literal}

{if $xmlSamples}
	<h2>{t _api_xml_samples}</h2>
	
	{foreach from=$xmlSamples item=sample}
		<div class="xmlSampleBlock">
			{if $sample.comments}
				<div class="xmlSampleComment">{$sample.comments}</div>
			{/if}
			<div class="xmlSample">
				<pre>{$sample.formatted|htmlspecialchars}</pre>
			</div>
			<a class="sampleTest" href="{link controller=api action=xml query="xml="}{$sample.xml}">test</a>
		</div>
	{/foreach}
{/if}

{if $searchFields}
	<h2>{t _api_search_fields}</h2>
	
	<table class="apiFields">
		<thead>
			<tr>
				<th>{t _api_field}</th>
				<th>{t _api_field_descr}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$searchFields item=field}
				<tr>
					<td>{$field.field}</td>
					<td>{$field.descr}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/if}

{if $createFields}
	<h2>{t _api_create_fields}</h2>
	
	<table class="apiFields">
		<thead>
			<tr>
				<th>{t _api_field}</th>
				<th>{t _api_field_descr}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$createFields key=group item=fields}
				<tr class="fieldGroup">
					<td colspan="2">{$group}</td>
				</tr>

				{foreach from=$fields key=field item=name}
					<tr>
						<td>{$field}</td>
						<td>{$name}</td>
					</tr>
				{/foreach}
			{/foreach}
		</tbody>
	</table>
{/if}

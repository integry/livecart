<h1><a href="[[ url("api/doc") ]]">{t _livecart_api}</a> &gt; <a href="[[ url("api/docview", "class=`$className`") ]]">[[info.path]]</a>.[[name]]</h1>


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


{% if !empty(xmlSamples) %}
	<h2>{t _api_xml_samples}</h2>

	{foreach from=$xmlSamples item=sample}
		<div class="xmlSampleBlock">
			{% if $sample.comments %}
				<div class="xmlSampleComment">[[sample.comments]]</div>
			{% endif %}
			<div class="xmlSample">
				<pre>{$sample.formatted|htmlspecialchars}</pre>
			</div>
			<a class="sampleTest" href="[[ url("api/xml", "xml=") ]]{$sample.xml|urlencode}">test</a>
		</div>
	{/foreach}
{% endif %}

{% if !empty(searchFields) %}
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
					<td>[[field.field]]</td>
					<td>[[field.descr]]</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{% endif %}

{% if !empty(createFields) %}
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
					<td colspan="2">[[name]]</td>
				</tr>

				{foreach from=$fields key=field item=name}
					<tr>
						<td>[[name]]</td>
						<td>[[name]]</td>
					</tr>
				{/foreach}
			{/foreach}
		</tbody>
	</table>
{% endif %}

{% if 'list' == $action %}
<h2>{t _api_limiting_results}</h2>
<pre>
All List actions have atributes:
	limit - positive integer, optional, how much records to return, default 999999999999999999
	offset- positive integer, optional, from which record start return, default 0
	start - same as limit
	max   - same as offset

	examples:
		&lt;customer&gt;&lt;list limit="10" offset="10"/&gt;&lt;/customer&gt;
		&lt;order&gt;&lt;list max=5 start=10&gt;&lt;/list&gt;&lt;/order&gt;
</pre>
{% endif %}
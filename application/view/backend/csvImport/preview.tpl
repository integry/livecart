<table id="#previewTable">
{foreach from=$preview item="row" name="csvPreview"}
	<tr{% if $smarty.foreach.csvPreview.first %} id="previewFirstRow"{% endif %}>
	{foreach from=$row key="index" item="cell"}
		<td class="column_[[index]]">
			{% if $smarty.foreach.csvPreview.first %}
				<a class="selectLink" href="#" onclick="Backend.CsvImport.showSelect([[index]]); return false;">[[cell]]</a>
				<span class="selectLink">[[cell]]</span>
			{% else %}
				[[cell]]
			{% endif %}
		</td>
	{/foreach}
	</tr>
{/foreach}
</table>

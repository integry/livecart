<p>
	<input type="checkbox" name="firstHeader" id="firstHeader" value="ON" class="checkbox" onclick="Backend.CsvImport.toggleHeaderRow(this.checked, $('previewFirstRow'));"/>
	<label for="firstHeader" class="checkbox">{t _first_header}</label>
</p>
{foreach from=$columns key=index item=column}
	<p id="column_select_{$index}">
		<label><a href="#" onclick="Backend.CsvImport.showColumn({$index}); return false;">{$column}</a></label>
		{selectfield name="column[`$index`]" options=$fields}
	</p>
{/foreach}
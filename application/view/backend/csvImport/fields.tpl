<p>
	<input type="checkbox" name="firstHeader" id="firstHeader" value="ON" class="checkbox" onclick="Backend.CsvImport.toggleHeaderRow(this.checked, $('previewFirstRow'));"/>
	<label for="firstHeader" class="checkbox">{t _first_header}</label>
</p>
{foreach from=$columns key=index item=column}
	<p id="column_select_{$index}">
		<label><a href="#" onclick="Backend.CsvImport.showColumn({$index}); return false;">{$column}</a></label>
		<select name="column[{$index}]">
			<option></option>
			{foreach from=$fields item=group key=groupName}
				<optgroup label="{translate text="$groupName"|escape}">
					{foreach from=$group key=field item=fieldName}
						<option value="{$field}">{$fieldName}</option>
					{/foreach}
				</optgroup>
			{/foreach}
		</select>
		<span class="fieldConfigContainer"></span>
	</p>
{/foreach}
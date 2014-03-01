<input type="hidden" name="options" value="[[options]]" />
<p>
	<input type="checkbox" name="firstHeader" id="firstHeader" value="ON" class="checkbox" onclick="Backend.CsvImport.toggleHeaderRow(this.checked, ('previewFirstRow'));"/>
	<label for="firstHeader" class="checkbox">{t _first_header}</label>
</p>
{% for index, column in columns %}

	<div id="column_select_[[index]]">
		{input name="options[transaction]"}
			{label}<a href="#" onclick="Backend.CsvImport.showColumn([[index]]); return false;">[[column]]</a>{/label}

			<select name="column[[[index]]]">
				<option></option>
				{foreach from=fields item=group key=groupName}
					<optgroup label="{translate text="groupName"|escape}">
						{% for field, fieldName in group %}
							<option value="[[field]]">[[fieldName]]</option>
						{% endfor %}
					</optgroup>
				{% endfor %}
			</select>
			<span class="fieldConfigContainer"></span>
		{/input}
	</div>

{% endfor %}
{includeCss file="backend/DatabaseImport.css"}
{includeCss file="backend/CsvImport.css"}

{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="backend/SelectFile.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/CsvImport.js"}

{% block title %}{t _import_csv}{{% endblock %}

[[ partial("layout/backend/header.tpl") ]]

<div id="import">

{include file="backend/csvImport/wizardProgress.tpl" class="stepSelect"}

{form action="controller=backend.csvImport action=setFile" method="POST" handle=$form}

	<fieldset>
		<legend>{t _data_type}</legend>
		<label></label>
		{selectfield name="type" options=$types}
	</fieldset>

	<fieldset>
		<legend>{t _select_file}</legend>

		{input name="upload"}
			{label}{t _upload_file}:{/label}
			{filefield}
		{/input}

		<div class="input">
			<label></label>
			- {t _or} -
		</div>

		{input name="atServer"}
			{label}{t _select_at_server}:{/label}
			{textfield id="atServer" class="file"}<input type="button" class="button browse" id="selectAtServer" value="{tn _browse}" />
		{/input}
	</fieldset>

	<fieldset>
		<legend>{t _options}</legend>

		<div class="input required">
			<label>{t _target_category}</label>
			<label id="targetCategory">
				{foreach from=$catPath item=node name=catPath}
					<a href="#" onclick="Backend.CsvImport.showCategorySelector([[node.ID]]); return false;">[[node.name_lang]]</a>
					{if !$smarty.foreach.catPath.last}
						&gt;
					{/if}
				{/foreach}
			</label>
			{hidden id="categoryID" name="category"}
		</div>

		<div class="options">

			{input name="options[action]"}
				{label}{t _import_action}:{/label}
				<select name="options[action]">
					<option value="both">{t _add_and_update}</option>
					<option value="add">{t _add_only}</option>
					<option value="update">{t _update_only}</option>
				</select>
			{/input}

			{input name="options[missing]"}
				{label}{t _import_missing_products}:{/label}
				<select name="options[missing]">
					<option value="keep">{t _keep_intact}</option>
					<option value="disable">{t _disable}</option>
					<option value="delete">{t _delete}</option>
				</select>
			{/input}

			{input name="options[transaction]"}
				{checkbox id="options_transaction"}
				{label}{tip _enclose_transaction _transaction_descr}{/label}
			{/input}

		</div>

	</fieldset>

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{tn _continue}" />
		{t _or}
		<a class="cancel" href="{link controller="backend.csvImport"}">{t _cancel}</a>
	</fieldset>

{/form}
</div>

{literal}
	<script type="text/javascript">
		Backend.SelectFile.url = {/literal}'{link controller="backend.selectFile"}'{literal};
		Backend.Category.links.popup = {/literal}'{link controller="backend.category" action=popup}'{literal};
		Event.observe($('selectAtServer'), 'click', function() {new Backend.SelectFile($('atServer')); });
	</script>
{/literal}

[[ partial("layout/backend/footer.tpl") ]]
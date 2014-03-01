{includeCss file="backend/DatabaseImport.css"}
{includeCss file="backend/CsvImport.css"}

{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="backend/CsvImport.js"}

{% title %}{t _import_csv}{% endblock %}

[[ partial("layout/backend/header.tpl") ]]

<div id="fieldConfigTemplates" class="hidden">
	<span class="ProductPrice.price ProductPrice.listPrice config">
		<span class="block">
			<span class="title">
				{t _currency}
			</span>
			<select name="currency">
				{% for currency in currencies %}
					<option>[[currency]]</option>
				{% endfor %}
			</select>
		</span>
		<span class="block priceGroup">
			<span class="title">
				{t _group}
			</span>
			<select name="group">
				<option></option>
				{% for group in groups %}
					<option value="[[group.ID]]">[[group.name]]</option>
				{% endfor %}
			</select>
		</span>
		<span class="block priceQuant">
			<span class="title">
				{t _quantity_level}
			</span>
			<select name="quantityLevel">
				<option></option>
				<option>1</option>
				<option>2</option>
				<option>3</option>
				<option>4</option>
				<option>5</option>
			</select>
		</span>
	</span>
	<span class="Product.name Product.shortDescription Product.longDescription Product.keywords NewsPost.text NewsPost.moreText ShippingService.name config">
		<span class="block">
			<span class="title">
				{t _language}
			</span>
			<select name="language">
				{% for language in languages %}
					<option value="[[language.ID]]">[[language.originalName]] ([[language.name]])</option>
				{% endfor %}
			</select>
		</span>
	</span>
</div>

<div id="importDelimiters">

[[ partial('backend/csvImport/wizardProgress.tpl', ['class': "stepDelimiters"]) ]]

{form action="backend.csvImport/preview" method="POST" id="delimitersform handle=form onsubmit="Backend.CsvImport.cont(); return false;"}

	<div id="import">

	{hidden name="file"}
	{hidden name="category"}
	{hidden name="type"}
	{hidden name="continue"}
	{hidden name="uid"}
	{hidden name="options"}

	<span style="display: none;">
		<span id="fieldsUrl">[[ url("backend.csvImport/fields") ]]</span>
		<span id="importUrl">[[ url("backend.csvImport/import") ]]</span>
		<span id="cancelUrl">[[ url("backend.csvImport/isCancelled") ]]</span>
	</span>

	<fieldset id="info">
		<form>

			{input name=""}
				{label}{tip _import_file}:{/label}
				<label>[[file]]</label>
			{/input}

			{% if 'ProductImport' == type %}
			<p>
				<label>{t _import_category}</label>
				<label>
					{foreach from=catPath item=node name="catPath"}
						<a href="[[ url("backend.csvImport/index") ]]?file=[[file]]&category=[[node.ID]]&selectCategory=true">[[node.name()]]</a>
						{% if !smarty.foreach.catPath.last %}
							&gt;
						{% endif %}
					{% endfor %}
				</label>
			</p>
			{% endif %}

		</form>
	</fieldset>

	<fieldset id="delimiters">
		<legend>{t _set_delimiter}</legend>

		{input name="delimiter"}
			{label}{t _delimiter}:{/label}
			{selectfield options=delimiters onchange="Backend.CsvImport.updatePreview()"}
			<span id="previewIndicator" class="progressIndicator" style="display: none;"></span>
		{/input}
	</fieldset>

	<div class="clear"></div>

	<div id="cancelCompleteMessage" class="yellowMessage" style="display: none;">
		<div style="float: left; margin-bottom: 1em;">{t _import_cancelled}</div>
	</div>

	<div id="cancelFailureMessage" class="redMessage" style="display: none;">
		<div style="float: left; margin-bottom: 1em;">{t _import_cancel_failed}</div>
	</div>

	<div id="nonTransactionalMessage" class="redMessage" style="display: none;">
		<div style="float: left; margin-bottom: 1em;">{t _timeout_error}</div>
	</div>

	<div class="clear"></div>

	<fieldset id="columns" style="display: none;">
		<legend>[[ branding({t _map_data}) ]]</legend>

		<div id="importProfiles">
			[[ partial("backend/csvImport/profiles.tpl") ]]
		</div>

		<div id="fieldsContainer"></div>
		<div class="clear"></div>

		<fieldset class="error">
			{hidden name="err"}
			<div class="errorText" style="display: none; margin-top: 0.5em;"></div>
		</fieldset>
	</fieldset>

	<div class="clear"></div>

	<fieldset class="controls" id="importControls">
		<div class="input saveProfile" style="display: none;">
			<input type="checkbox" class="checkbox" name="saveProfile" id="saveProfile" />
			<label for="saveProfile" class="checkbox" style="margin-right: 1em;">{t _save_profile}:</label>
			<input type="text" class="text" name="profileName" id="profileName" disabled="disabled" />
		</div>

		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{t _continue}" />
		{t _or}
		<a class="cancel" href="[[ url("backend.csvImport") ]]">{t _cancel}</a>
	</fieldset>

	<div class="clear"></div>

	<div id="completeMessage" class="yellowMessage stick" style="display: none;">
		<div style="float: left; margin-bottom: 1em;">{t _import_completed}</div>
	</div>

	<div class="clear"></div>

	<fieldset id="progress" style="display: none;">
		<legend>{t _importing}</legend>
		<div class="progressBarIndicator"></div>
		<div class="progressBar" style="display: none;">
			<span class="progressCount"></span>
			<span class="progressSeparator"> / </span>
			<span class="progressTotal"></span>
		</div>
		<a class="cancel" href="#" onclick="Backend.CsvImport.cancel(); return false;">{t _cancel}</a>
		<div class="lastName"></div>
	</fieldset>

	</div>

	<div class="clear"></div>

	<fieldset id="preview">
		<legend>{maketext text=_preview_count params="`previewCount`,`total`"}</legend>
			<div id="previewContainer">
				[[ partial('backend/csvImport/preview.tpl', ['preview': preview]) ]]
			</div>
	</fieldset>

{/form}


</div>

[[ partial("layout/backend/footer.tpl") ]]
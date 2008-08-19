{includeCss file="backend/DatabaseImport.css"}
{includeCss file="backend/CsvImport.css"}

{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="backend/CsvImport.js"}

{pageTitle}{t _import_csv}{/pageTitle}

{include file="layout/backend/header.tpl"}

<div id="importDelimiters">

{include file="backend/csvImport/wizardProgress.tpl" class="stepDelimiters"}

{form action="controller=backend.csvImport action=preview" method="POST" id="delimitersForm" handle=$form onsubmit="Backend.CsvImport.cont(); return false;"}

	<div id="import">

	{hidden name="file"}
	{hidden name="category"}
	{hidden name="continue"}

	<span style="display: none;">
		<span id="fieldsUrl">{link controller=backend.csvImport action=fields}</span>
		<span id="importUrl">{link controller=backend.csvImport action=import}</span>
		<span id="cancelUrl">{link controller=backend.csvImport action=isCancelled}</span>
	</span>

	<fieldset id="info">
		<form>
			<p>
				<label>{t _import_file}</label>
				<label class="wide">{$file}</label>
			</p>

			<p>
				<label>{t _import_category}</label>
				<label class="wide">
					{foreach from=$catPath item=node name="catPath"}
						<a href="{link controller=backend.csvImport action=index}?file={$file}&category={$node.ID}&selectCategory=true">{$node.name_lang}</a>
						{if !$smarty.foreach.catPath.last}
							&gt;
						{/if}
					{/foreach}
				</label>
			</p>
		</form>
	</fieldset>

	<fieldset id="delimiters">
		<legend>{t _set_delimiter}</legend>

		<p class="required">
			{err for="delimiter"}
				{{label {t _delimiter} }}
				{selectfield options=$delimiters onchange="Backend.CsvImport.updatePreview()"}
				<span id="previewIndicator" class="progressIndicator" style="display: none;"></span>
			{/err}
		</p>
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
		<legend>{t _map_data|branding}</legend>

		<div id="fieldsContainer"></div>
		<div class="clear"></div>

		<fieldset class="error">
			{hidden name="err"}
			<div class="errorText" style="display: none; margin-top: 0.5em;"></div>
		</fieldset>
	</fieldset>

	<div class="clear"></div>

	<fieldset class="controls" id="importControls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{tn _continue}" />
		{t _or}
		<a class="cancel" href="{link controller=backend.csvImport}">{t _cancel}</a>
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
		<legend>{maketext text=_preview_count params=$previewCount,$total}</legend>
			<div id="previewContainer">
				{include file="backend/csvImport/preview.tpl" preview=$preview}
			</div>
	</fieldset>

{/form}


</div>

{include file="layout/backend/footer.tpl"}
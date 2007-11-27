{includeCss file="backend/DatabaseImport.css"}

{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="backend/DatabaseImport.js"}

{pageTitle}{t _import_csv}{/pageTitle}

{include file="layout/backend/header.tpl"}

<div id="import">
{form action="controller=backend.databaseImport action=import" method="POST" handle=$form onsubmit="new Backend.DatabaseImport(this); return false;"}

	<fieldset>
		<legend>{t _select_file}</legend>

		<p class="required">
			{err for="upload"}
				{{label {t _upload_file} }}
				{filefield}
			{/err}
		</p>

		<p class="required">
			{err for="atServer"}
				{{label {t _select_at_server} }}
				{textfield}
			{/err}
		</p>

	</fieldset>
		
	<fieldset class="controls">		
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{tn _import}" />
		{t _or}
		<a class="cancel" href="#">{t _cancel}</a>
	</fieldset>

{/form}
</div>

{include file="layout/backend/footer.tpl"}
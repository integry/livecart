{includeCss file="backend/DatabaseImport.css"}

{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="backend/DatabaseImport.js"}

{% block title %}{t _import_database}{{% endblock %}

[[ partial("layout/backend/header.tpl") ]]

<p>{t _import_description|branding}</p>

<p class="importWarning">{t _import_warning|branding}</p>

<div id="import">
{form action="backend.databaseImport/import" method="POST" handle=$form onsubmit="new Backend.DatabaseImport(this); return false;"}

	<fieldset>
		<legend>{t _begin_import}</legend>

		[[ selectfld('cart', '_shopping_cart', carts) ]]

		[[ selectfld('dbType', '_database_type', dbTypes) ]]

		[[ textfld('dbServer', '_database_server') ]]

		[[ textfld('dbName', '_database_name') ]]

		[[ textfld('dbUser', '_database_user') ]]

		[[ pwdfld('dbPass', '_database_pass') ]]

		[[ textfld('filePath', '_file_path') ]]
	</fieldset>

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{tn _import}" />
		{t _or}
		<a class="cancel" href="{link controller="backend.databaseImport"}">{t _cancel}</a>
	</fieldset>

{/form}
</div>

<div id="importProgress" style="display: none;">

	<div id="completeMessage" class="yellowMessage stick" style="display: none;">
		<div>{t _import_completed}</div>
	</div>

	<fieldset>
		<legend>Importing</legend>

		<ul class="menu">
			<li>
				<a class="cancel" href="{link controller="backend.databaseImport"}">{t _cancel}</a>
			</li>
		</ul>

		<ul id="progressBarContainer">
			{foreach from=$recordTypes item=type}
				<li id="progress_[[type]]" style="display: none;">
					<h2>{translate text=$type}</h2>
					<div class="progressBarIndicator"></div>
					<div class="progressBar" style="display: none;">
						<span class="progressCount"></span>
						<span class="progressSeparator"> / </span>
						<span class="progressTotal"></span>
					</div>
				</li>
			{/foreach}
		</ul>
	</fieldset>
</div>

[[ partial("layout/backend/footer.tpl") ]]

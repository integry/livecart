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
{form action="controller=backend.databaseImport action=import" method="POST" handle=$form onsubmit="new Backend.DatabaseImport(this); return false;"}

	<fieldset>
		<legend>{t _begin_import}</legend>

		{input name="cart"}
			{label}{t _shopping_cart}:{/label}
			{selectfield options=$carts}
		{/input}

		{input name="dbType"}
			{label}{t _database_type}:{/label}
			{selectfield options=$dbTypes}
		{/input}

		{input name="dbServer"}
			{label}{t _database_server}:{/label}
			{textfield}
		{/input}

		{input name="dbName"}
			{label}{t _database_name}:{/label}
			{textfield}
		{/input}

		{input name="dbUser"}
			{label}{t _database_user}:{/label}
			{textfield}
		{/input}

		{input name="dbPass"}
			{label}{t _database_pass}:{/label}
			{textfield type="password"}
		{/input}

		{input name="filePath"}
			{label}{t _file_path}:{/label}
			{textfield}
		{/input}
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

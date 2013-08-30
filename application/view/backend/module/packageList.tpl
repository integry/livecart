<fieldset class="slide">
	<legend>{t _add_modules}</legend>
	{% if !empty(packages) %}
		<form method="POST" action="[[ url("backend.module/fetch") ]]">
			<p>
				<label>{t _select_module}</label>
				<select name="module">
					{foreach from=$packages key=domain item=packages}
						<optgroup label="[[domain]]">
							{foreach from=$packages key=id item=package}
								<option value="[[id]]">[[package.name]] ([[package.version]])</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
				<input type="hidden" name="repos" value="[[repos]]" />
				<input type="submit" class="submit" value="{t _install_module}" />
			</p>
		</form>
	{% else %}
		<div class="errorMessage">{t _no_modules_add}</div>
	{% endif %}
</fieldset>

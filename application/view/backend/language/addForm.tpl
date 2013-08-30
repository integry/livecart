<div>
	<fieldset class="addForm">
		<legend>[[ capitalize({t _add_language}) ]]</legend>
		<form onSubmit="lng.add(this); return false;" action="[[ url("backend.language/add") ]]">
			<select name="id" class="select" id="addLang-sel">
			   {html_options options=$languages_select}
			</select>
			<span class="progressIndicator" id="addLangFeedback" style="display: none;"></span>
			<input type="submit" value="{t _add_lang_button}" name="sm" class="submit" />
			<span>{t _or} </span>
			<a href="#" class="cancel" onClick="Backend.LanguageIndex.prototype.hideAddForm(); return false;">{t _cancel}</a>
		</form>
	</fieldset>
</div>
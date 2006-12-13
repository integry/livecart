<div>	
	<form onSubmit="lng.add(this.getElementsByTagName('select')[0].value); return false;" action="">
		<select name="new_language" class="select" id="addLang-sel">
		   {html_options options=$languages_select}
		</select>
		<img src="image/indicator.gif" id="addLangFeedback" />
		<input type="submit" value="{t _add_lang_button}" name="sm" class="submit" />
		<span>{t _or} </span>
		<a href="#" class="cancel" onClick="restoreMenu('addLang', 'langPageMenu'); return false;">{t _cancel}</a>
	</form>	
</div>
<div>	
	<form onSubmit="curr.add(this.getElementsByTagName('select')[0].value); return false;" action="">
		<select name="id" class="select" id="addLang-sel">
		   {html_options options=$currencies}
		</select>
		<img src="image/indicator.gif" id="addCurrIndicator" />
		<input type="submit" value="{t _add_curr_button}" name="sm" class="submit" />
		<span>{t _or} </span>
		<a href="#" class="cancel" onClick="restoreMenu('addCurr', 'currPageMenu'); return false;">{t _cancel}</a>
	</form>	
</div>
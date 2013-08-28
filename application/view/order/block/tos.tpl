<div id="tosContainer">
	{checkbox class="checkbox" name="tos" id="tos"}
	<label class="checkbox" for="tos">[[ config('TOS_MESSAGE') ]]</label>
	<div class="text-danger hidden"></div>
	{error for="tos"}<div class="text-danger hidden">{t _err_agree_to_tos}</div>{/error}
</div>

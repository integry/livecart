<div id="tosContainer">
	{checkbox class="checkbox" name="tos" id="tos"}
	<label class="checkbox" for="tos">{'TOS_MESSAGE'|config}</label>
	<div class="text-error hidden"></div>
	{error for="tos"}<div class="text-error hidden">{t _err_agree_to_tos}</div>{/error}
</div>

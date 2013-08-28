<div class="form-actions">
	<button type="submit" class="btn btn-primary">{translate text=$caption}</button>
	<span class="progressIndicator" style="display: none;"></span>

	{if $cancelRoute}
		<a class="btn cancel" href="{link route=$return}">{t _cancel}</a>
	{elseif $cancel}
		<a class="btn cancel" href="{link controller=$cancel}">{t _cancel}</a>
	{elseif $cancelHref}
		<a class="btn cancel" href="[[cancelHref]]">{t _cancel}</a>
	{/if}
</div>
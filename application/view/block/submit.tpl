<div class="form-actions">
	<button type="submit" class="btn btn-primary">[[ t(caption) ]]</button>
	<span class="progressIndicator" style="display: none;"></span>

	{% if !empty(cancelRoute) %}
		<a class="btn cancel" href="{link route=$return}">{t _cancel}</a>
	{% elseif !empty(cancel) %}
		<a class="btn cancel" href="{link controller=$cancel}">{t _cancel}</a>
	{% elseif !empty(cancelHref) %}
		<a class="btn cancel" href="[[cancelHref]]">{t _cancel}</a>
	{% endif %}
</div>
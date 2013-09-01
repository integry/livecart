<div class="form-actions">
	<submit>[[ t(caption) ]]</submit>
	<span class="progressIndicator" style="display: none;"></span>

	{% if !empty(cancelRoute) %}
		<a class="btn cancel" href="[[ url(cancelRoute) ]]">{t _cancel}</a>
	{% elseif !empty(cancel) %}
		<a class="btn cancel" href="[[ url(cancel) ]]">{t _cancel}</a>
	{% elseif !empty(cancelHref) %}
		<a class="btn cancel" href="[[cancelHref]]">{t _cancel}</a>
	{% endif %}
</div>
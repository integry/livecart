{% if $allLanguages|@count > 1 %}
<div id="language" class="btn-group">
	{% if 'LANG_SELECTION'|config == 'LANG_DROPDOWN' %}
		<button class="btn btn-small btn-info dropdown-toggle" data-toggle="dropdown">
			[[current.originalName]] <span class="caret"></span>
		</button>
		<ul class="dropdown-menu btn-small">
			{foreach from=$allLanguages item="language"}
				<li><a href="[[language.url]]">[[language.originalName]]</a></li>
			{/foreach}
		</ul>
	{% else %}
		{foreach from=$allLanguages item="language"}
			{% if 'LANG_SELECTION'|config == 'LANG_NAMES' || !$language.image %}
				<a href="[[language.url]]" class="btn btn-default btn-small {% if $language.ID == $current.ID %}btn-info{% endif %} lang-sel-[[language.ID]]">[[language.originalName]]</a>
			{% else %}
				<a href="[[language.url]]" class="lang-sel-[[language.ID]]"><img src="{static url=$language.image}" alt="[[language.originalName]]" title="[[language.originalName]]" /></a>
			{% endif %}
		{/foreach}
	{% endif %}
</div>
{% endif %}
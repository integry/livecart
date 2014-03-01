{% if !empty(pages) %}
<div class="panel panel-info informationMenu">
	<div class="panel-heading">
		<span class="glyphicon glyphicon-info-sign"></span>
		{t _information}
	</div>

	<div class="content">
		<ul class="nav nav-list">
		{% for page in pages %}
			<li id="static_[[page.ID]]"><a href="{pageUrl data=page}">[[page.title()]]</a></li>
			{% if page.children %}
				<ul class="nav nav-list">
					{foreach from=page.children item=subPage}
						<li id="static_[[subPage.ID]]"><a href="{pageUrl data=subPage}">[[subPage.title()]]</a></li>
					{% endfor %}
				</ul>
			{% endif %}
		{% endfor %}
		</ul>
	</div>
</div>
{% endif %}

<div id="categorySection" class="productSection categories">
<h2>{t _find_similar_by_category}</h2>
<ul class="foundCategories additionalCategories">
	{% for path in additionalCategories %}
		<li>
			{% for node in path %}
				{% if node.parentNodeID > 1 %}&gt;{% endif %}
				<a href="{categoryUrl data=node}">[[node.name()]]</a>
			{% endfor %}
		</li>
	{% endfor %}
</ul>
</div>
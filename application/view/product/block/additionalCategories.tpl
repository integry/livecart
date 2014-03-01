<div id="categorySection" class="productSection categories">
<h2>{t _find_similar_by_category}</h2>
<ul class="foundCategories additionalCategories">
	{foreach from=$additionalCategories item=path}
		<li>
			{foreach from=$path item=node}
				{% if $node.parentNodeID > 1 %}&gt;{% endif %}
				<a href="{categoryUrl data=$node}">[[node.name()]]</a>
			{/foreach}
		</li>
	{/foreach}
</ul>
</div>
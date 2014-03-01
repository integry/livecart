{% if item.Product.ID %}
	{% if item.Product.isDownloadable && downloadLinks %}
		<a href="[[ url("user/item/" ~ item.ID) ]]">[[item.Product.name()]]</a>
	{% else %}
		<a href="{productUrl product=item.Product}">[[item.Product.name()]]</a>
	{% endif %}
{% else %}
	<span>[[item.Product.name()]]</span>
{% endif %}

{% if item.Product.variations %}
	<span class="variations">
		(&rlm;[[ partial("order/itemVariationsList.tpl") ]])
	</span>
{% endif %}

[[ partial('user/itemOptions.tpl', ['options': item.options]) ]]

{sect}
	{header}
		<ul class="subItemList">
	{/header}
	{content}
		{foreach item.subItems as subItem}
			{% if subItem.Product.isDownloadable %}
				<li>
					<a href="[[ url("user/item/" ~ subItem.ID) ]]">[[subItem.Product.name()]]</a>
					[[ partial('user/itemOptions.tpl', ['options': subItem.options]) ]]
				</li>
			{% endif %}
		{% endfor %}
	{/content}
	{footer}
		</ul>
	{/footer}
{/sect}
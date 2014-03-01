{php}ob_end_clean();{/php}
<rss version="2.0">
	<channel>
		<title><![CDATA[[[category.name()]]]]></title>
		<link><![CDATA[{categoryUrl data=category}]]></link>
		<description>[[category.description()]]</description>

		{% for product in feed %}
			{% if product.ID %}
				<item>
					<title><![CDATA[[[product.name()_safe]]]]></title>
					<link><![CDATA[{productUrl product=product full=true}]]></link>
					<description><![CDATA[[[product.shortDescription()]]]]></description>
					{*
						<price>{% if product.price %}[[product.price]]{% endif %}</price>
						<image>{% if product.DefaultImage.ID %}<![CDATA[[[product.DefaultImage.urls.4]]]]>{% endif %}</image>
						<manufacturer><![CDATA[[[product.Manufacturer.name]]]]></manufacturer>
						<model><![CDATA[{product.name()_safe|replace:product.Manufacturer.name:''|trim}]]></model>
						<in_stock>{% if product.stockCount > 0 %}[[product.stockCount]]{% else %}0{% endif %}</in_stock>
					*}
				</item>
			{% endif %}
		{% endfor %}
	</channel>
</rss>
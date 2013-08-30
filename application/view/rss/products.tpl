{php}ob_end_clean();{/php}
<rss version="2.0">
	<channel>
		<title><![CDATA[[[category.name_lang]]]]></title>
		<link><![CDATA[{categoryUrl data=$category}]]></link>
		<description>[[category.description_lang]]</description>

		{foreach from=$feed item=product}
			{% if $product.ID %}
				<item>
					<title><![CDATA[[[product.name_lang_safe]]]]></title>
					<link><![CDATA[{productUrl product=$product full=true}]]></link> 
					<description><![CDATA[[[product.shortDescription_lang]]]]></description>
					{*
						<price>{% if $product.price %}[[product.price]]{% endif %}</price>
						<image>{% if $product.DefaultImage.ID %}<![CDATA[[[product.DefaultImage.urls.4]]]]>{% endif %}</image>
						<manufacturer><![CDATA[[[product.Manufacturer.name]]]]></manufacturer>
						<model><![CDATA[{$product.name_lang_safe|replace:$product.Manufacturer.name:''|trim}]]></model>
						<in_stock>{% if $product.stockCount > 0 %}[[product.stockCount]]{% else %}0{% endif %}</in_stock>
					*}
				</item>
			{% endif %}
		{/foreach}
	</channel>
</rss>
{php}ob_end_clean();{/php}
<rss version="2.0">
	<channel>
		<title><![CDATA[{$category.name_lang|utf8_decode}]]></title>     
		<link><![CDATA[{categoryUrl data=$category}]]></link>
		<description>{$category.description_lang|utf8_decode}</description>

		{foreach from=$feed item=product}
			{if $product.ID}
				<item>
					<title><![CDATA[{$product.name_lang_safe|utf8_decode}]]></title>
					<link><![CDATA[{productUrl product=$product full=true}]]></link> 
					<description><![CDATA[{$product.shortDescription_lang|utf8_decode}]]></description>
					{*
						<price>{if $product.price}{$product.price}{/if}</price>
						<image>{if $product.DefaultImage.ID}<![CDATA[{$product.DefaultImage.urls.4}]]>{/if}</image>
						<manufacturer><![CDATA[{$product.Manufacturer.name}]]></manufacturer>
						<model><![CDATA[{$product.name_lang_safe|replace:$product.Manufacturer.name:''|trim}]]></model>
						<in_stock>{if $product.stockCount > 0}{$product.stockCount}{else}0{/if}</in_stock>
					*}
				</item>
			{/if}
		{/foreach}
	</channel>
</rss>
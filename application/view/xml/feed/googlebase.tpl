<?xml version="1.0"?>
{assign var="priceCurrency" value=$priceCurrency|default:"price_USD"}
<rss version="2.0">
	<channel>
		<title>{'STORE_NAME'|config}</title>
		<link>{link url=true}</link>
		<description></description>

		{foreach from=$feed item=product}
			<item>
				<title><![CDATA[{$product.name_lang}]]></title>
				<description><![CDATA[{$product.longDescription_lang}]]></description>

				<link><![CDATA[{productUrl product=$product full=true}]]></link>

				{if $product.Manufacturer.name}
					<g:brand><![CDATA[{$product.Manufacturer.name}]]></g:brand>
				{/if}

				{if $product.DefaultImage.ID}
				<g:image_link><![CDATA[{$product.DefaultImage.urls.1}]]></g:image_link>
				{/if}

				<g:price><![CDATA[{$product.$priceCurrency}]]></g:price>

				{if $product.shippingWeight}
				<g:weight><![CDATA[{$product.shippingWeight} kg]]></g:weight>
				{/if}
			</item>
		{/foreach}

	</channel>
</rss>
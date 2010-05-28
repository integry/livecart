<?xml version="1.0" encoding="UTF-8" ?>
{assign var="priceCurrency" value=$priceCurrency|default:"price_USD"}
<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">

	<channel>
		<title>{'STORE_NAME'|config}</title>
		<link>{link url=true}</link>
		<description></description>

		{foreach from=$feed item=product}
			<item>
				<title><![CDATA[{$product.name_lang_utf8}]]></title>
				<description><![CDATA[{$product.shortDescription_lang_utf8}]]></description>

				<link><![CDATA[{productUrl product=$product full=true}]]></link>

				{if $product.Manufacturer.name}
					<g:brand><![CDATA[{$product.Manufacturer.name|@htmlentities}]]></g:brand>
				{/if}

				{if $product.DefaultImage.ID}
				<g:image_link><![CDATA[{$product.DefaultImage.urls.4}]]></g:image_link>
				{/if}

				<g:price><![CDATA[{$product.$priceCurrency}]]></g:price>

				{if $product.shippingWeight}
				<g:weight><![CDATA[{$product.shippingWeight} kg]]></g:weight>
				{/if}
				<g:condition>new</g:condition>
				<g:product_type><![CDATA[{$product.Category.name_lang|@htmlentities}]]></g:product_type>

			</item>
		{/foreach}

	</channel>
</rss>

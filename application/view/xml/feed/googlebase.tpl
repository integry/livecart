<?xml version="1.0" encoding="UTF-8" ?>
{assign var="priceCurrency" value=$priceCurrency|default:"price_USD"}
{assign var="listPriceCurrency" value=$listPriceCurrency|default:"listPrice_USD"}
<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">

	<channel>
		<title>[[ config('STORE_NAME') ]]</title>
		<link>{link url=true}</link>
		<description></description>

		{foreach from=$feed item=product}
			<item>
				<title><![CDATA[[[product.name_lang_utf8]]]]></title>

				{if $product.shortDescription_lang_utf8}
					<description><![CDATA[[[product.shortDescription_lang_utf8]]]]></description>
				{elseif $product.longDescription_lang_utf8}
					<description><![CDATA[[[product.longDescription_lang_utf8]]]]></description>
				{/if}

				<link><![CDATA[{productUrl product=$product full=true}]]></link>

				<g:id><![CDATA[{$product.sku|@htmlentities}]]></g:id>
				{if $product.Manufacturer.name}
					<g:brand><![CDATA[{$product.Manufacturer.name|@htmlentities}]]></g:brand>
				{/if}

				{if $product.DefaultImage.ID}
					<g:image_link><![CDATA[[[product.DefaultImage.urls.4]]]]></g:image_link>
				{/if}

				{if $product.$listPriceCurrency}
					<g:price><![CDATA[{$product.$listPriceCurrency} USD]]></g:price>
					<g:sale_price><![CDATA[{$product.$priceCurrency} USD]]></g:sale_price>
				{else}
					<g:price><![CDATA[{$product.$priceCurrency|default:0} USD]]></g:price>
				{/if}

				{if $product.shippingWeight}
					<g:shipping_weight><![CDATA[[[product.shippingWeight]] kg]]></g:weight>
				{/if}

				<g:condition>new</g:condition>
				<g:product_type><![CDATA[{$product.Category.name_lang|@htmlentities}]]></g:product_type>
				<g:availability><![CDATA[{if $product.isAvailable}in stock{else}out of stock{/if}]]></g:availability>

			</item>
		{/foreach}

	</channel>
</rss>
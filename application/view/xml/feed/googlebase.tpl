<?xml version="1.0" encoding="UTF-8" ?>
{assign var="priceCurrency" value=priceCurrency|default:"price_USD"}
{assign var="listPriceCurrency" value=listPriceCurrency|default:"listPrice_USD"}
<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">

	<channel>
		<title>[[ config('STORE_NAME') ]]</title>
		<link>[[ fullurl("/") ]]</link>
		<description></description>

		{% for product in feed %}
			<item>
				<title><![CDATA[[[product.name()_utf8]]]]></title>

				{% if product.shortDescription()_utf8 %}
					<description><![CDATA[[[product.shortDescription()_utf8]]]]></description>
				{% elseif product.longDescription()_utf8 %}
					<description><![CDATA[[[product.longDescription()_utf8]]]]></description>
				{% endif %}

				<link><![CDATA[{productUrl product=product full=true}]]></link>

				<g:id><![CDATA[{product.sku|@htmlentities}]]></g:id>
				{% if product.Manufacturer.name %}
					<g:brand><![CDATA[{product.Manufacturer.name|@htmlentities}]]></g:brand>
				{% endif %}

				{% if product.DefaultImage.ID %}
					<g:image_link><![CDATA[[[product.DefaultImage.urls.4]]]]></g:image_link>
				{% endif %}

				{% if product.listPriceCurrency %}
					<g:price><![CDATA[{product.listPriceCurrency} USD]]></g:price>
					<g:sale_price><![CDATA[{product.priceCurrency} USD]]></g:sale_price>
				{% else %}
					<g:price><![CDATA[{product.priceCurrency|default:0} USD]]></g:price>
				{% endif %}

				{% if product.shippingWeight %}
					<g:shipping_weight><![CDATA[[[product.shippingWeight]] kg]]></g:weight>
				{% endif %}

				<g:condition>new</g:condition>
				<g:product_type><![CDATA[{product.Category.name()|@htmlentities}]]></g:product_type>
				<g:availability><![CDATA[{% if product.isAvailable %}in stock{% else %}out of stock{% endif %}]]></g:availability>

			</item>
		{% endfor %}

	</channel>
</rss>
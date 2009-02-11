<?xml version="1.0" encoding="utf-8" ?>
<SHOP>
	{foreach from=$feed item=product}
		<SHOPITEM>
			<PRODUCT><![CDATA[{$product.name_lang}]]></PRODUCT>
			<DESCRIPTION><![CDATA[{$product.longDescription_lang}]]></DESCRIPTION>
			<URL><![CDATA[{productUrl product=$product}]]></URL>

			{if $product.DefaultImage.ID}
			<IMGURL><![CDATA[{$product.DefaultImage.urls.1}]]></IMGURL>
			{/if}
			<PRICE_VAT><![CDATA[{$product.price_CZK|default:$product.price_EUR}]]></PRICE_VAT>
		</SHOPITEM>
	{/foreach}
</SHOP>
<?xml version="1.0" encoding="utf-8" ?>
<root>
	{foreach from=$feed item=product}
	<item>
		<name><![CDATA[[[product.name_lang_safe]]]]></name>
		<link><![CDATA[{productUrl product=$product full=true}]]></link>
		<price>{if $product.price_LVL}[[product.price_LVL]]{/if}</price>
		<image>{if $product.DefaultImage.ID}<![CDATA[[[product.DefaultImage.urls.4]]]]>{/if}</image>
		<category_full><![CDATA[[[product.category_path]]]]></category_full>
		<category_link><![CDATA[{categoryUrl data=$product full=true}]]></category_link>
		<manufacturer><![CDATA[[[product.Manufacturer.name]]]]></manufacturer>
		<model><![CDATA[{$product.name_lang_safe|replace:$product.Manufacturer.name:''|trim}]]></model>
		<in_stock>{if $product.stockCount > 0}[[product.stockCount]]{else}0{/if}</in_stock>
	</item>
	{/foreach}
</root>
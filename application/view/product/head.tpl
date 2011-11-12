{include file="block/message.tpl"}

<h1>{$product.name_lang}</h1>

{block PRODUCT-ATTRIBUTE-SUMMARY}		{* product/block/attributeSummary.tpl *}

<div class="clear"></div>

{blocks id="PRODUCT-HEAD" blocks="
		PRODUCT-NAVIGATION				//product/block/navigation.tpl
		PRODUCT-IMAGES					//product/block/images.tpl
		PRODUCT-SUMMARY					//product/block/summary.tpl
"}

<div class="clear"></div>
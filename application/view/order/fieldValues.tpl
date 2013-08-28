{sect}
	{header}
		<form class="eavAttributes" class="form-horizontal">
	{/header}
	{content}
		{foreach $order.attributes as $attr}
			{if $attr.EavField.isDisplayedInList && ($attr.values || $attr.value || $attr.value_lang)}
				<p>
					<label class="attrName">[[attr.EavField.name_lang]]:</label>
					<label class="attrValue">{include file="product/attributeValue.tpl" attr=$attr field="EavField"}</label>
				</p>
			{/if}
		{/foreach}
	{/content}
	{footer}
		</form>
		<div class="clear"></div>
	{/footer}
{/sect}
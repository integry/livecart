{capture assign="body"}
	[[ partial("order/changeMessages.tpl") ]]

	{% if !$cart.cartItems %}
		<div class="emptyBasket">
			{t _empty_basket}. <a href="[[ url(return) ]]">{t _continue_shopping}</a>.
		</div>
	{% else %}
		[[ partial('order/cartItems.tpl', ['hideNav': true]) ]]
	{% endif %}
{/capture}

{capture assign="footer"}
	[[ partial('order/block/navigationButtons.tpl', ['hideTos': true]) ]]
{/capture}

[[ partial('block/modal.tpl', ['title': "_your_basket", 'body': body, 'footer': footer]) ]]

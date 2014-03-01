<li>
	{% if order.basketCount %}
		[[ partial('order/miniCartBlock.tpl', ['hidePanel': true]) ]]
	{% else %}
		<div id="miniCart" class="cartEmpty">
			<p>{t _empty_basket}</p>
		</div>
	{% endif %}
</li>

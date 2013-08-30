{capture assign="body"}
	[[ partial("order/changeMessages.tpl") ]]

	{% if !empty(error) %}
		<div class="errorMessage">[[error]]</div>
	{% endif %}

	<p class="addedToCart">[[msg]]</p>
{/capture}

{capture assign="footer"}
	[[ partial('order/block/navigationButtons.tpl', ['hideTos': true]) ]]
{/capture}

[[ partial('block/modal.tpl', ['title': "_item_added_title", 'body': body, 'footer': footer]) ]]

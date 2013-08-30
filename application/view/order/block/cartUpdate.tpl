{% if !$cartUpdateDisplayed %}
	<td id="cartUpdate">
		<button type="submit" class="btn btn-default btn-small">{tn _update}</button>
	</td>
	{assign var="cartUpdateDisplayed" value=true scope=global}
{% else %}
	<td></td>
{% endif %}

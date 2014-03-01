{% extends "layout/frontend.tpl" %}

{% title %}{t _compare_products}{% endblock %}
[[ partial("layout/frontend/header.tpl") ]]
{% block content %}

<a href="[[ url(return) ]]" class="btn btn-primary return"><span class="glyphicon glyphicon-arrow-left"></span> {t _continue_shopping}</a>

{% for category in products %}
	<h2>[[category.category.name()]]</h2>
	<table class="compareData table table-striped table-hover">
		<thead>
			<tr>
				<th></th>
				{foreach from=category.products item=product}
					<th>
						<a href="{productUrl product=product}">[[product.name()]]</a>
						[[ partial("product/block/smallImage.tpl") ]]
					</th>
				{% endfor %}
			</tr>
		</thead>
		<tbody>
			<tr class="priceRow">
				<td>{t _price}</td>
				{foreach from=category.products item="product"}
					<td class="value price">
						[[ partial("product/block/productPrice.tpl") ]]
						<div class="cartButton">
							[[ partial("product/block/cartButton.tpl") ]]
						</div>
					</td>
				{% endfor %}
			</tr>
			{foreach from=category.groups item="group" name="groups"}
				{% if group.group %}
					<tr class="specificationGroup heading{% if smarty.foreach.groups.first %} first{% endif %}">
						{assign var="cnt" value=category.products|@count}
						<th colspan="{cnt+1}">[[group.group.name()]]</th>
					</tr>
				{% endif %}

				{foreach from=group.attributes item=attr name="attributes"}
					{% if attr.isDisplayed %}
					<tr>
						<td class="param">[[attr.name()]]</td>
						{foreach from=category.products item="product"}
							<td class="value">
								[[ partial('product/attributeValue.tpl', ['attr': product.attributes[attr.ID]]) ]]
							</td>
						{% endfor %}
					</tr>
					{% endif %}
				{% endfor %}
			{% endfor %}
		</tbody>
	</table>
{% endfor %}

{% endblock %}

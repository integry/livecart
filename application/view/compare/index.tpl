{% extends "layout/frontend.tpl" %}

{% block title %}{t _compare_products}{{% endblock %}
{include file="layout/frontend/header.tpl"}
{% block content %}

<a href="{link route=$return}" class="btn btn-primary return"><span class="glyphicon glyphicon-arrow-left"></span> {t _continue_shopping}</a>

{foreach from=$products item=category}
	<h2>[[category.category.name_lang]]</h2>
	<table class="compareData table table-striped table-hover">
		<thead>
			<tr>
				<th></th>
				{foreach from=$category.products item=product}
					<th>
						<a href="{productUrl product=$product}">[[product.name_lang]]</a>
						{include file="product/block/smallImage.tpl"}
					</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			<tr class="priceRow">
				<td>{t _price}</td>
				{foreach from=$category.products item="product"}
					<td class="value price">
						{include file="product/block/productPrice.tpl"}
						<div class="cartButton">
							{include file="product/block/cartButton.tpl"}
						</div>
					</td>
				{/foreach}
			</tr>
			{foreach from=$category.groups item="group" name="groups"}
				{if $group.group}
					<tr class="specificationGroup heading{if $smarty.foreach.groups.first} first{/if}">
						{assign var="cnt" value=$category.products|@count}
						<th colspan="{$cnt+1}">[[group.group.name_lang]]</th>
					</tr>
				{/if}

				{foreach from=$group.attributes item=attr name="attributes"}
					{if $attr.isDisplayed}
					<tr>
						<td class="param">[[attr.name_lang]]</td>
						{foreach from=$category.products item="product"}
							<td class="value">
								{include file="product/attributeValue.tpl" attr=$product.attributes[$attr.ID]}
							</td>
						{/foreach}
					</tr>
					{/if}
				{/foreach}
			{/foreach}
		</tbody>
	</table>
{/foreach}

{% endblock %}

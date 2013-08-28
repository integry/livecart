{% extends "layout/frontend.tpl" %}

{% block title %}Index{% endblock %}

{% block content %}
test
    <p class="important">Welcome on my awesome {{homepage}}.</p>
{% endblock %}

{#

{pageTitle}{$page.title_lang}{/pageTitle}
{assign var="metaDescription" value=$page.metaDescription_lang|@strip_tags}

<div class="staticPageView staticPage_{$page.ID}">
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	{if $subPages}
		<div class="staticSubpages">
			<h2>{t _subpages}</h2>
			<ul>
				{foreach from=$subPages item=subPage}
					<li id="static_{$subPage.ID}"><a href="{pageUrl data=$subPage}">{$subPage.title_lang}</a></li>
				{/foreach}
			</ul>
		</div>
	{/if}

	<div class="staticPageText">
		{$page.text_lang}
	</div>

	{foreach $page.attributes as $attr}
		<div class="eavAttr eav-{$attr.EavField.handle}">
		<h3 class="attributeTitle">{$attr.EavField.name_lang}</h3>
		<p class="attributeValue">
			{if $attr.values}
				<ul class="attributeList{if $attr.values|@count == 1} singleValue{/if}">
					{foreach from=$attr.values item="value"}
						<li class="fieldDescription"> {$value.value_lang}</li>
					{/foreach}
				</ul>
				{elseif $attr.value_lang}
					{$attr.value_lang}
				{elseif $attr.value}
					{$attr.EavField.valuePrefix_lang}{$attr.value}{$attr.EavField.valueSuffix_lang}
				{/if}
		</p>
		</div>
	{/foreach}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}
</div>

#}
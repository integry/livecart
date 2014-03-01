{% extends "layout/frontend.tpl" %}

{% block title %}[[ page.title ]]{% endblock %}

{% block left %}{% endblock %}
{% block right %}{% endblock %}

{% block content %}
	<h1>[[ page.title ]]</h1>
	<div class="staticPageText">[[ page.text ]]</div>
{% endblock %}

{#

{% block title %}[[page.title()]]{% endblock %}
{assign var="metaDescription" value=page.metaDescription()|@strip_tags}

<div class="staticPageView staticPage_[[page.ID]]">

{% block content %}

	{% if !empty(subPages) %}
		<div class="staticSubpages">
			<h2>{t _subpages}</h2>
			<ul>
				{% for subPage in subPages %}
					<li id="static_[[subPage.ID]]"><a href="{pageUrl data=subPage}">[[subPage.title()]]</a></li>
				{% endfor %}
			</ul>
		</div>
	{% endif %}

	<div class="staticPageText">
		[[page.text()]]
	</div>

	{foreach page.attributes as attr}
		<div class="eavAttr eav-[[attr.EavField.handle]]">
		<h3 class="attributeTitle">[[attr.EavField.name()]]</h3>
		<p class="attributeValue">
			{% if attr.values %}
				<ul class="attributeList{% if attr.values|@count == 1 %} singleValue{% endif %}">
					{foreach from=attr.values item="value"}
						<li class="fieldDescription"> [[value.value()]]</li>
					{% endfor %}
				</ul>
				{% elseif attr.value() %}
					[[attr.value()]]
				{% elseif attr.value %}
					[[attr.EavField.valuePrefix()]][[attr.value]][[attr.EavField.valueSuffix()]]
				{% endif %}
		</p>
		</div>
	{% endfor %}

{% endblock %}

</div>

#}

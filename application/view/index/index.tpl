{% extends "layout/frontend.tpl" %}

{% block title %}[[ config('STORE_HEADLINE') ]]{% endblock %}
{% block metaDescription %}[[ config('INDEX_META_DESCRIPTION') ]]{% endblock %}
{% block metaKeywords %}[[ config('INDEX_META_KEYWORDS') ]]{% endblock %}

{% block content %}
    
	<tabset id="home-products">
		<tab>
			<tab-heading>
				<i class="glyphicon glyphicon-chevron-down"></i> <span>Jaunumi</span>
			</tab-heading>
			
			[[ render('kameja/count') ]]
			
			[[ render('kameja/jaunumi') ]]
		</tab>
		<tab select="resize()">
			<tab-heading>
				<i class="glyphicon glyphicon-chevron-down"></i> <span>Atlaides</span>
			</tab-heading>
			
			[[ render('kameja/count') ]]
			
			[[ render('kameja/atlaides') ]]
		</tab>
	</tabset>

    
{% endblock %}

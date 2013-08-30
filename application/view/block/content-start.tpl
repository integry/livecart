<div id="content" class="col col-lg-[[12 - global('layoutspanLeft') - global('layoutspanRight')]]">

{block BREADCRUMB}

{% if content('title') and !hideTitle %}
	<h1>[[ content('title') ]]</h1>
{% endif %}

[[ partial("block/message.tpl") ]]

<div id="content" class="col col-lg-[[12 - global('layoutspanLeft') - global('layoutspanRight')]]">

{# block BREADCRUMB #}

{% if title() and empty(hideTitle) and !global('hideTitle') %}
	<h1>[[ title() ]]</h1>
{% endif %}

[[ partial("block/message.tpl") ]]

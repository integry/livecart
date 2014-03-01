{assign var="layouts" value=config('ENABLED_LIST_LAYOUTS')}
{% if 'ALLOW_SWITCH_LAYOUT'|@config && (layouts|@count > 1) %}
	<div class="categoryLayoutSwitch btn-group">
		{% if layouts.LIST %}
			<a class="btn btn-default layoutSetList {% if layout == 'LIST' %}active{% endif %}" href="[[layoutUrl]]list" title="{tn _view_as_list}"><span class="glyphicon glyphicon-list"></span></a>
		{% endif %}
		{% if layouts.GRID %}
			<a class="btn btn-default layoutSetGrid {% if layout == 'GRID' %}active{% endif %}" href="[[layoutUrl]]grid" title="{tn _view_as_grid}"><span class="glyphicon glyphicon-th-large"></span></a>
		{% endif %}
		{% if layouts.TABLE %}
			<a class="btn btn-default layoutSetTable {% if layout == 'TABLE' %}active{% endif %}" href="[[layoutUrl]]table" title="{tn _view_as_table}"><span class="glyphicon glyphicon-th-list"></span></a>
		{% endif %}
	</div>
{% endif %}

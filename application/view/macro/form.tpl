{%- macro form(action, params = '') %}
	{% set validator = global('validator') %}

	{% if validator and validator.getAngularValues() %}
		<script type="text/javascript">window.vals = [[ validator.getAngularValues() ]];</script>
	{% endif %}
	<my-form name="form" ng-submit="checkErrors(event, form); {% if !empty(params['ng-submit']) %}[[ params['ng-submit'] ]]{% endif %}" novalidate="" ng-init="isSubmitted=0; {% if empty(params['ng-init']) %}vals={};{% else %} [[ params['ng-init'] ]]{% endif %}" {% if validator %}default-values="vals"{% endif %}

	{% if action %}
		action="[[ url(action) ]]"
	{% endif %}

	{% if !empty(params['method']) %}
		method="[[ params['method'] ]]"
	{% endif %}
{%- endmacro %}

{%- macro startinput(field, type) %}
	<div class="form-group field_[[field]] type_[[type]]">
{%- endmacro %}

{%- macro endinput(field) %}
	{% set validator = global('validator') %}
	{% if validator %}
		{% for val in validator.getValidators(field) %}
			<div ng-show="isSubmitted && form.[[field]].error.[[ validator.getAngularErrType(val) ]]" class="text-danger">[[ val.getOption('message') ]]</div>
		{% endfor %}

		{% for error in validator.getFieldMessages(field) %}
			<div class="text-danger" ng-show="isSubmitted == 0">
				[[ error.getMessage() ]]
			</div>
		{% endfor %}
	{% endif %}
	<custom-errors field="[[ field ]]"></custom-errors>
	</div>
{%- endmacro %}

{%- macro open_label() %}
	<label class="control-label">
{%- endmacro %}

{%- macro label(title) %}
	[[ open_label() ]][[ title ]]</label>
{%- endmacro %}

{%- macro ngvalidation(field) %}
	{% set validator = global('validator') %}
	{% if validator %}
		{% for val in validator.getValidators(field) %}
			[[ validator.getAngularValidation(val) ]]
		{% endfor %}
	{% endif %}
{%- endmacro %}

{%- macro inputattributes(field, params) %}
	name="[[field]]" class="{% if empty(params['noformcontrol']) %}form-control{% endif %}
	{% if !empty(params['class']) %}[[ params['class'] ]]{% endif %}"
	{% if params %}
		<?php unset(params['class']); ?>
		{% for key, value in params %}
			[[key]]="[[value]]"
		{% endfor %}
	{% endif %}
	ng-model="{% if !empty(params['ng-model']) %}[[ params['ng-model'] ]]{% else %}vals.[[field]]{% endif %}"
	[[ ngvalidation(field) ]]
{%- endmacro %}

{%- macro textfld(field, title, params) %}
	{% set validator = global('validator') %}
    [[ startinput(field, 'textfld', params) ]]
        [[ label(t(title)) ]]
		{% if !empty(validator) and validator.hasFilter(field, 'int') %}<?php params['type'] = 'number'; ?>{% endif %}
        <input {% if params and !empty(params['type']) and (params['type'] == 'number') %} filter-number<?php unset(params['type']); ?> {% endif %} type="{% if params and !empty(params['type']) %}[[ params['type'] ]]{% else %}text{% endif %}" [[ inputattributes(field, params) ]] />
    [[ endinput(field) ]]
{%- endmacro %}

{%- macro pwdfld(field, title, params) %}
    <?php params = is_array(params) ? params : array(); params['type'] = 'password'; ?>
    [[ textfld(field, title, params) ]]
{%- endmacro %}

{%- macro filefld(field, title, params) %}
    <?php params = is_array(params) ? params : array(); params['type'] = 'file'; ?>
    [[ textfld(field, title, params) ]]
{%- endmacro %}

{%- macro textareafld(field, title, params) %}
    [[ startinput(field, 'textfld', params) ]]
        [[ label(t(title)) ]]
        <textarea [[ inputattributes(field, params) ]]></textarea>
    [[ endinput(field) ]]
{%- endmacro %}

{%- macro selectfld(field, title, options, params) %}
    [[ startinput(field, 'selectfld') ]]
        [[ label(t(title)) ]]
        <select [[ inputattributes(field, params) ]]>
        {% for key, value in options %}
        	<option value="[[ key ]]">[[ value ]]</option>
        {% endfor %}
        </select>
    [[ endinput(field) ]]
{%- endmacro %}

{%- macro checkbox(field, title, params) %}
    [[ startinput(field, 'checkbox') ]]
        [[ open_label() ]]
        	<?php params = is_array(params) ? params : array(); params['noformcontrol'] = true; ?>
        	<input type="checkbox" [[ inputattributes(field, params) ]] {% if empty(params['skip-ng-defaults']) %}value="1" ng-true-value="1" ng-false-value="0"{% endif %} />
        	[[ t(title) ]]
        </label>
    [[ endinput(field) ]]
{%- endmacro %}

{%- macro radio(field, title, params) %}
    [[ startinput(field, 'radio') ]]
        [[ open_label() ]]
        	<?php params = is_array(params) ? params : array(); params['noformcontrol'] = true; ?>
        	<input type="radio" [[ inputattributes(field, params) ]] />
        	[[ t(title) ]]
        </label>
    [[ endinput(field) ]]
{%- endmacro %}

{%- macro eav(instance, handle, options, default) %}
    {% set attr = instance.handle(handle) %}
    {% if attr %}
    	[[ attr.getFormattedValue(options) ]]
    {% else %}
    	[[ default ]]
    {% endif %}
{%- endmacro %}

{%- macro pageUrl(url, page) %}[[str_replace('___', page, url)]]{%- endmacro %}

{%- macro pageButton(url, page, caption, params) %}
	<a href="[[pageUrl(url, page)]]"{% if !empty(params['ng-click']) %}ng-click="[[ pageUrl(params['ng-click'], page) ]]"{% endif %}>[[ caption ]]</a>
{%- endmacro %}

{%- macro paginator(paginator, url, params = '') %}
	{% if paginator.getNumPages() > 1 %}
		<ul class="pagination">
			
			{% if paginator.getPrev() %}
				<li class="prev">[[ pageButton(url, paginator.getPrev(), '&laquo;', params) ]]</li>
			{% else %}
				<li class="prev disabled"><a>&laquo;</a></li>
			{% endif %}
			
			{% for page in paginator.getCompactNumbers() %}
				<li class="{% if paginator.getCurrentPage() == page %}active{% endif %}">
					{% if page %}
						[[ pageButton(url, page, page, params) ]]
					{% else %}
						<a>...</a>
					{% endif %}
				</li>
			{% endfor %}

			{% if paginator.getNext() %}
				<li class="next">[[ pageButton(url, paginator.getNext(), '&raquo;', params) ]]</li>
			{% else %}
				<li class="next disabled"><a>&raquo;</a></li>
			{% endif %}
		</ul>
	{% endif %}
{%- endmacro %}

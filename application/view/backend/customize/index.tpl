{pageTitle help="customize"}{t _live_customization}{/pageTitle}
{includeCss file="backend/Customize.css"}

[[ partial("layout/backend/header.tpl") ]]

<ul id="customizeMenu">

	<li{% if !empty(isCustomizationModeEnabled) %} class="active"{% endif %}>

		<a href="[[ url("backend.customize/mode") ]]" class="customizeControl {% if !empty(isCustomizationModeEnabled) %}on{% endif %}">
		{% if !empty(isCustomizationModeEnabled) %}
			{tn _turn_off}
		{% else %}
			{tn _turn_on}
		{% endif %}
		</a>

		<div class="modeDescr">
			{t _live_custom_descr}
		</div>

		<a href="[[ url("/") ]]" target="_blank" id="goToFrontend">{tn _go_frontend}</a>
		<div class="clear"></div>

	</li>

</ul>

[[ partial("layout/backend/footer.tpl") ]]
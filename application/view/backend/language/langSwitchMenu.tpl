<div id="langMenu">
	<div>
		{% for item in languages %}
		<div class="" onMouseOver="this.className = 'langMenuHover';" onMouseOut="Element.removeClassName(this, 'langMenuHover');" onClick="window.location.href = '[[ url("backend.language/changeLanguage/" ~ item.ID, "returnRoute=returnRoute") ]]'"{% if item.ID == currentLanguage %} id="langMenuCurrent"{% endif %}>
			<span{% if 0 == item.isEnabled %} class="disabled"{% endif %}>
				{% if file_exists(item.image) %}
					{img src=item.image}
				{% endif %}
				[[item.originalName]]
			</span>
		</div>
		{% endfor %}
	</div>

	<a href="#" class="cancel" onClick="showLangMenu(false); return false;">{t _cancel}</a>
</div>
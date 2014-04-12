<ul class="nav navbar-nav">
	{% for item in menu.getItems() %}
		<li id="menuitem-[[ item.getID() ]]">
			<a href="[[ item.getLink() ]]">[[ item.getTitle() ]]</a>
		</li>
	{% endfor %}
</ul>


<tree id="[[rootID]]">
	{foreach item="node" from=tree}
	   <item child="[[node.childrenCount]]" id="[[node.ID]]" text="{node.name|escape:'html'}"></item>
	{% endfor %}
</tree>
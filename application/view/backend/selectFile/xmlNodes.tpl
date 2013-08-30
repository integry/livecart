{% if !empty(tree) %}
	{% if $tree.ID %}<item child="[[tree.childrenCount]]" id="[[tree.ID]]" text="{$tree.name|escape:'html'}" {% if !$doNotTouch && $tree.ID == $targetID %}selected="true" call="true"{% endif %}>{% endif %}
		{foreach from=$tree.children key="name" item="subtree"}
			  [[ partial('backend/selectFile/xmlNodes.tpl', ['tree': subtree]) ]]
		{/foreach}
	{% if $tree.ID %}</item>{% endif %}
{% endif %}

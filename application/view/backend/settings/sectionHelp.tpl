{assign var="key" value="-"|explode:$key}
{assign var="key" value=$key[1]|default:$key[0]}
{capture assign="hlp"}{translate text="`$key`_help" eval=true noDefault=true}{/capture}
{% if !empty(hlp) %}
	<div class="sectionHelp">
		[[hlp]]
	</div>
{% endif %}
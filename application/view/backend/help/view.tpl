{defun name="topicTree" node=false}
	{if $node}
		<ul>			
		{foreach from=$node item=topic}
			{if $topic.ID == $currentId}
				<li class="current">
					<span>{$topic.name}</span>
				</li>	
			{else}
				<li>
					<a href="{link controller="backend.help" action="view" id=$topic.ID}">{$topic.name}</a>
				</li>	
			{/if}
			{if $topic.sub}
				{fun name="topicTree" node=$topic.sub}
			{/if}
		{/foreach}
		</ul>
	{/if}	
{/defun}

{include file=layout/help/header.tpl}

<div id="helpNav" style="background-color: #ABCDEF;">
{foreach from=$path item=item name=breadCrumb}
	{if !$smarty.foreach.breadCrumb.last}
		<a href="{help $item.ID}">{$item.name}</a> &gt;
	{else}
		<span id="breadCrumbLast">{$item.name}</span>
	{/if}
{/foreach}
</div>

<div id="helpContent">

	<fieldset id="helpTopicTree" style="border: 1px solid black; background-color: white; padding: 5px; float: left; width: 200px; background-color: #EEEEEE;">
		{fun name="topicTree" node=$topicTree}
	</fieldset>

	<div style="margin-left: 220px;">
	
		{if '' != $PAGE_TITLE}
			<h1>{$PAGE_TITLE}</h1>
		{/if}

		<div style="padding-left: 20px; padding-top: 10px;">
			{include file=$helpTemplate}
		</div>
	
		<div id="helpRelated">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
		</div>

	</div>

</div>

<div id="helpFooter">
	<div id="helpFooterContent">
		{if '' != $prev}
			<a href="{help $prev.ID}">&lt; {$prev.name}</a>
			{if '' != $next}
			:
			{/if}
		{/if}
		{if '' != $next}
			<a href="{help $next.ID}">{$next.name} &gt; </a>
		{/if}
	</div>
</div>

{include file=layout/help/footer.tpl}
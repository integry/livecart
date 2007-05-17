<div id="top">
</div>
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

{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/prototype/prototype.js"}
{includeJs file="library/livecart.js"}
{include file=layout/help/header.tpl}

{literal}
<script type="text/javascript">

	function expand(param)
	{
	param.style.display=(param.style.display=="none")?"":"none";
	}

	function addComment(form)
	{
		new LiveCart.AjaxUpdater(form, 'commentContainer', 'commentIndicator', 'bottom'); 
		form.reset(); 
		form.style.display = 'none';
	}

	function editComment(id)
	{
		var container = $('comment_' + id);
		var username = document.getElementsByClassName('username', container)[0];
		var text = document.getElementsByClassName('commentText', container)[0];
		var form = container.getElementsByTagName('form')[0];
		form.onsubmit = 
			function() 
			{ 
		 		new LiveCart.AjaxUpdater(this, this.parentNode.id, document.getElementsByClassName('progressIndicator', this)[0].id);  
				return false;
			}
		
		var formControl = $('editControlTemplate').cloneNode(true);
		formControl.style.display = '';
		document.getElementsByClassName('progressIndicator', formControl)[0].id = 'commentProgress_' + id;
		form.appendChild(formControl);
		
		formControl.getElementsByTagName('a')[0].onclick = 
			function()
			{
				var id = document.getElementsByClassName('progressIndicator', this.parentNode)[0].id.substr(16, 20);
				cancelEditComment(id);
				return false;
			}
		
		var user = document.createElement('input');
		user.name = 'username';
		user.value = username.firstChild.nodeValue;
		user.originalValue = user.value;
		username.replaceChild(user, username.firstChild);
		
		var comment = document.createElement('textarea');
		comment.name = 'text';
		var value = text.innerHTML;
		text.innerHTML = '';
		comment.originalValue = value;
		value = value.replace(/\<br\>/g, '');
		comment.value = value;
		text.appendChild(comment);
	}
	
	function cancelEditComment(id)
	{
		var container = $('comment_' + id);
		var username = document.getElementsByClassName('username', container)[0];
		var text = document.getElementsByClassName('commentText', container)[0];
		var form = container.getElementsByTagName('form')[0];
		
		var user = username.firstChild.originalValue;
		var comment = text.firstChild.originalValue;
		
		var userNode = document.createTextNode(user);
		text.innerHTML = comment;
		
		username.replaceChild(userNode, username.firstChild);
		
		form.removeChild(form.lastChild);
	}

	function saveComment(id)
	{
		
	}

	function deleteComment(id)
	{
		{/literal}new Ajax.Request('{link controller=backend.help action=deleteComment}/' + id);{literal} 
		$('comment_' + id).style.display = 'none';
	}

</script>
{/literal}

<div id="helpNav" style="background-color: #ABCDEF;">
{foreach from=$path item=item name=breadCrumb}
	{if !$smarty.foreach.breadCrumb.last}
		<a href="{help $item.ID}">{$item.name}</a> &gt;
	{else}
		<span id="breadCrumbLast">{$item.name}</span>
	{/if}
{/foreach}
</div>

<div id="helpContent" style="padding-bottom: 40px;">

	<fieldset id="helpTopicTree" style="border: 1px solid black; background-color: white; padding: 5px; float: left; width: 200px; background-color: #EEEEEE;">
		{fun name="topicTree" node=$topicTree}
	</fieldset>

	<div style="margin-left: 220px;">
	
		{if '' != $PAGE_TITLE}
			<h1>{$PAGE_TITLE}</h1>
		{/if}

		<div style="padding-left: 20px; padding-top: 10px;">
			{include file=$helpTemplate}
	
			<div id="helpComments">
				<h2>{t User Contributed Notes}</h2>
				
				<div id="commentStats">
					{maketext text="[quant,_1,comment,comments,No comments] received" params="$commentCount"}. <a href="#" onclick="document.getElementById('commentForm').style.display = ''; return false;">{t Add your comment}</a>.
					<span class="progressIndicator" id="commentIndicator" style="display: none;"></span>
				</div>
						
				{form handle=$commentForm action="controller=backend.help action=addComment id=0" method="POST" style="display:none;" id="commentForm" onsubmit="addComment(this); return false;"}
					{hidden name="topicId" value=$topic.ID}
	
						<label for="username">{t _name}:</label>
						<fieldset class="error">
							{textfield name="username"}
							<div class="errorText hidden"></div> 
						</fieldset>
	
						<label for="text">{t _comment}:</label>
						<fieldset class="error">
							{textarea name="text"}
							<div class="errorText hidden"></div> 
						</fieldset>						
						
						<input type="submit" value="{tn _add_comment}" class="submit" /> {t _or} <a href="#" onclick="document.getElementById('commentForm').style.display = 'none'; return false;" class="cancel">{t _cancel}</a>
				{/form}

				<ul id="commentContainer">
				{foreach from=$comments item="comment"}
					<a name="c{$comment.ID}"></a>
					<li id="comment_{$comment.ID}">
						{include file="backend/help/comment.tpl"}
					</li>
				{/foreach}
				</ul>
				
				<div id="editControlTemplate" style="display: none;">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" value="{tn _save}" class="submit" />
					{t _or}
					<a href="#" onclick="return false;" class="cancel">{t _cancel}</a>				
				</div>

			</div>

		</div>

	</div>

</div>

<div id="helpFooter">
	<div id="helpFooterContent">
		{if '' != $prev}
			<a href="{help /`$prev.ID`}">&lt; {$prev.name}</a>
			{if '' != $next}
			:
			{/if}
		{/if}
		{if '' != $next}
			<a href="{help /`$next.ID`}">{$next.name} &gt; </a>
		{/if}
	</div>
</div>

{include file=layout/help/footer.tpl}
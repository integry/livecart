<form action="{link controller=backend.help action=saveComment id=$comment.ID}">

	<div class="commentHeader">
		<fieldset class="container">
			<div class="username">{$comment.username}</div> 
			<div class="time">
				<a href="#" onclick="editComment({$comment.ID}); return false;">{t _edit}</a>
				| 
				<a href="#" onclick="deleteComment({$comment.ID}); return false;">{t _delete}</a>		
				|
				<a href="{self}#c{$comment.ID}">{$comment.timeAdded}</a>
			</div>
		</fieldset>
	</div>
	
	<div class="commentText">{$comment.text|escape:"html"|nl2br}</div>

</form>
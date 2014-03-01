<li class="responder_[[note.isAdmin]]">
	
	<div class="responseUser">
		<span class="responderType">
		{% if note.isAdmin %}
			{t _respond_admin}:
		{% else %}
			{t _respond_customer}:
		{% endif %}
		</span>
		
		[[note.User.fullName]]
	</div>
		
	<div class="noteDate">
		[[note.formatted_time.date_full]] [[note.formatted_time.time_full]]
	</div>
		
	<div class="clear"></div>
		
	<div class="noteText">
		{note.text|nl2br}
	</div>
	
</li>		

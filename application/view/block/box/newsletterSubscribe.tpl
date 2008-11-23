{loadJs form=true}
<div class="box newsletter">
	<div class="title">
		<div>{t _subscribe_to_newsletter}</div>
	</div>

	<div class="content">

	<p>{t _enter_your_email_to_subscribe}</p>

	{form handle=$form action="controller=newsletter action=subscribe" method="POST"}
		{{err for="email"}}
			{textfield style="width: 130px;"}
			<input type="submit" class="submit" value="OK" style="width: 20%;" />
		{/err}

	{/form}

	</div>

	<div class="clear"></div>
</div>

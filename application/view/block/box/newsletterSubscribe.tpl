{loadJs form=true}
<div class="panel newsletter">
	<div class="panel-heading">{t _subscribe_to_newsletter}</div>

	<div class="content">

		<p>{t _enter_your_email_to_subscribe}</p>

		{form handle=$form action="controller=newsletter action=subscribe" method="POST" class="form-horizontal"}
			{input name="email"}
				<div class="input-group">
					{textfield}
					<span class="input-group-btn">
						<button class="btn" type="button">OK</button>
					</span>
				</div>
			{/input}
		{/form}
	</div>
</div>

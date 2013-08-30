<div class="panel newsletter">
	<div class="panel-heading">
		<span class="glyphicon glyphicon-envelope"></span>
		{t _subscribe_to_newsletter}
	</div>

	<div class="content">

		<p class="subscribeInfo">{t _subscribe_info}</p>

		{form handle=$form action="newsletter/subscribe" method="POST" class="form-horizontal"}
			{input name="email"}
				<div class="input-group">
					{textfield placeholder="_email_placeholder" noFormat=true}
					<span class="input-group-btn">
						<button class="btn btn-default" type="submit">OK</button>
					</span>
				</div>
			{/input}
		{/form}
	</div>
</div>

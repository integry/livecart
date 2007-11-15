<h1>Set Up Administrator User Account</h1>

<div>

	{form action="controller=install action=setAdmin" method="POST" handle=$form}

	<p>
		{err for="firstName"}
			{{label First name:}}
			{textfield class="text"}
		{/err}	
	</p>

	<p>
		{err for="lastName"}
			{{label Last name:}}
			{textfield class="text"}
		{/err}	
	</p>

	<p>
		{err for="email"}
			{{label E-mail address:}}
			{textfield class="text"}
		{/err}	
	</p>

	<p>
		{err for="password"}
			{{label Password:}}
			{textfield type="password" class="text password"}
		{/err}	
	</p>

	<p>
		{err for="confirmPassword"}
			{{label Confirm password:}}
			{textfield type="password" class="text password"}
		{/err}	
	</p>

	<div class="clear"></div>
	<input type="submit" value="Continue installation" />
	{/form}
</div>
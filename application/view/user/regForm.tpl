{form action="controller=user action=doRegister" method="POST" handle=$regForm}

	<p class="required">
	    <label for="firstName">{t _your_first_name}:</label>
	    
		<fieldset class="error">
			{textfield name="firstName" class="text"}
			<div class="errorText hidden{error for="firstName"} visible{/error}">{error for="firstName"}{$msg}{/error}</div>
		</fieldset>
	</p>
	
	<p class="required">
	    <label for="lastName">{t _your_last_name}:</label>
	    
		<fieldset class="error">
			{textfield name="lastName" class="text"}
			<div class="errorText hidden{error for="lastName"} visible{/error}">{error for="lastName"}{$msg}{/error}</div>
		</fieldset>
	</p>
	
	<p>
	    <label for="companyName">{t _company_name}:</label>
	    
		<fieldset class="error">
			{textfield name="companyName" class="text"}
			<div class="errorText hidden{error for="companyName"} visible{/error}">{error for="companyName"}{$msg}{/error}</div>
		</fieldset>
	</p>
	
	<p class="required">
	    <label for="email">{t _your_email}:</label>
	    
		<fieldset class="error">
			{textfield name="email" class="text"}
			<div class="errorText hidden{error for="email"} visible{/error}">{error for="email"}{$msg}{/error}</div>
		</fieldset>
	</p>

	<p class="required">
	    <label for="password">{t _password}:</label>
	    
		<fieldset class="error">
			{textfield type="password" name="password" class="text"}
			<div class="errorText hidden{error for="password"} visible{/error}">{error for="password"}{$msg}{/error}</div>
		</fieldset>
	</p>

	<p class="required">
	    <label for="confpassword">{t _conf_password}:</label>
	    
		<fieldset class="error">
			{textfield type="password" name="confpassword" class="text"}
			<div class="errorText hidden{error for="confpassword"} visible{/error}">{error for="confpassword"}{$msg}{/error}</div>
		</fieldset>
	</p>
	
	<label></label>
	<input type="submit" class="submit" value="{tn Complete Registration}" />

{/form}
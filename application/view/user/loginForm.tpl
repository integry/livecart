<form action="{link controller=user action=doLogin}" method="POST" />
    <p>
       <label for="email">{t Your e-mail address}:</label>
       <input type="text" class="text" id="email" name="email" value="{$email|escape}" />
    </p>
    <p>
        <label for="password">{t Your password}:</label>
        <fieldset class="container">
            <input type="password" class="text" id="password" name="password" />
            <a href="{link controller=user action="remindPassword" query="return=$return"}" class="forgottenPassword">
                {t _remind_password}
            </a>            
        </fieldset>
    </p>	

   	<p>
		<label></label>
		<input type="submit" class="submit" value="{tn Login}" />
	</p>
    
	<input type="hidden" name="return" value="{$return}" />	
	
</form>
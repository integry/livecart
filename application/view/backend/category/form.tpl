{includeCss file="form.css"}

{assign var="action" value="create"}

{form handle=$catalogForm action="controller=backend.catalog action=$action" method="post"}
	<fieldset>
		<legend>Category details {$ID}</legend>
	
		<label for="name">Category name:</label> 
		{textfield name="name" id="name"}
		<br/>
		
		<label for="details">Details:</label> 
		{textarea name="details" id="details"}
		<br/>
		
		<label for="keywords">Keywords:</label> 
		{textarea name="keywords" id="keywords"}
		<br/>
			
		<label for="isActive"> </label> 
		{checkbox name="isActive"} Category is activated
		<br/>
			
		<label for="submit"> </label> 
		<input type="submit" class="submit" id="submit" value="save"/>
	
	</fieldset>
	
	<fieldset>
		<legend>Translate to: </legend>
	</fieldset>

{/form}
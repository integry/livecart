{form handle=$catalogForm action="controller=backend.catalog action=save" method="post"}
	<fieldset>
		<legend>Category details {$ID}</legend>
	
		<label for="name">Category name:</label> 
		{textfield name="name" id="name"}
		<br/>
		
		<label for="details">Details:</label> 
		{textarea name="details" id="details"}
		<br/>
			
		<label for="handle">Handle:</label> 
		{textfield name="handle" id="handle"}
		<br/>
			
		<label for="submit"> </label> 
		<input type="submit" class="submit" id="submit" value="Update details"/>
	
	</fieldset>
	
	<fieldset>
		<legend>Translate Catalog Details</legend>
		
	</fieldset>

{/form}
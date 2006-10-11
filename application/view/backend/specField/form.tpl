{form handle=$specFieldForm}
<fieldset>
	<legend>Add new field</legend>
	<div>
		Field name: <br/>
		{textfield name="name" style="padding: 5px;"}
	</div>
	
	<div>
		Field handle: <br/>
		{textfield name="handle"}
	</div>
	
	<div>
		Field type:<br/>
		{selectfield name="type"}
	</div>
	
	<input type="submit" value="Add field &raquo;"/>
</fieldset>
{/form}
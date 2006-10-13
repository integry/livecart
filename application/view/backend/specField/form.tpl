{includeJs file="library/prototype.js"}
{includeCss file="base.css"}

{form handle=$specFieldForm action="controller=backend.specField action=save" method="post"}
<fieldset>
	<legend>Add new field</legend>
	<div>
		Field name: <br/>
		{textfield name="name" style="padding: 5px;"}
	</div>
	
	<div>
		Field Description: <br/>
		{textarea name="description"}
	</div>
	
	<div>
		Field handle: <br/>
		{textfield name="handle" style="padding: 5px;"}
	</div>
	
	<div>
		Field type:<br/>
		{selectfield name="type" options=$typeList}
	</div>
	
	<input type="submit" value="Add field &raquo;"/>
</fieldset>
{/form}
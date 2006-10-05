<h1>ADD LANGUAGE</h1>
<hr/>
<form name="Form" method="post" action="{link controller=backend.language action=add id=0}">
	<p>
		Code:<br/>
		<input style="width: 120px;" type="text" name="code" value="" maxlenght="10"/><br/>
	</p>
	<p>
		Abbreviature:<br/>
		<input style="width: 120px;" type="text" name="abbr" value="" maxlength="10"/><br/>
	</p>
	<p>
		Name:<br/>
		<input style="width: 240px;" type="text" name="name" value="" maxlength="255"/><br/>
	</p>
	<p>
		Active:<br/>
		<input type="checkbox" name="active" checked/><br/>
	</p>	
	<p>
		<input type="submit" value="Add"/>
	</p>
<hr/>

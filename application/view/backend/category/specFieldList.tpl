<h1>Specification field list</h1>

	<a href>Add new field &raquo;</a>
	{foreach name=specList from=$specFieldList item=field}
		<div style="border: 3px solid #F1F1F1; margin-bottom: 10px;">
			<div style="padding: 5px;">
				#{$smarty.foreach.specList.iteration} 
				<br/>EN: {$field.lang.en.name}
				<br/>LT: {$field.lang.lt.name}
			</div>
			<div class="toolbal" style="font-size: 10px; background-color: #F1F1F1; padding: 2px;">
				<a href="">Edit</a> | <a href="">Remove</a> | 
				{if $field.type == 1}<strong>input field</strong>{/if}
				{if $field.type == 2}<strong>One choice</strong> - <a href="">manage values</a>{/if}
				{if $field.type == 3}<strong>multiple choice</strong> - <a href="">manage values</a>{/if}
			</div>
		</div>
	{foreachelse}
	There are no specification fields created for this catalog
	{/foreach}
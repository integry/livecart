<table>
	<tr height=30>
		<td>
		</td>
		<td >
			<b>{$title}</b>
		</td>
	</tr>
	<tr height=30>
		<td></td>
		<td>
			{if $list}
				{foreach  from=$list item=item}
					<a href="">{$item.name}</a> &nbsp;
				{/foreach}
			{/if}
		</td>
	</tr>	
	<tr>
		<td width="10">
		</td>
		<td>
			<font size="-1"><b>Name</b></font>
		</td>
	</tr>
	<tr>
		<td width="10">
		</td>
		<td>
			<input type="textbox" name="name" style="width: 200px" value="{$name|escape}">	
		</td>
	</tr>
	<tr>
		<td width="10">
		</td>
		<td>
			<font size="-1"><b>Descripton</b></font>
		</td>
	</tr>
	<tr>
		<td width="10">
		</td>
		<td>
			<input type="textbox" name="description" style="width: 300px" value="{$description|escape	}">
		</td>
	</tr>
	<tr>
		<td width="10">
		</td>
		<td>
			<input value="Save changes" type="submit">
			 &nbsp;			
		</td>
	</tr>	
</table>
<br>
<br>
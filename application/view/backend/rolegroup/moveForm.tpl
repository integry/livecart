<span style="position: absolute; left:200px; top:100px; height: 300px; width: 250px;		
		border: solid;
		border-width: thin;
		background-color: #fafafa;
		overflow: auto; ">	
	<table>
		<tr>
			<td width=10>				
			</td>
			<td>
				<b>Move To Node</b>
				<a href="javascript: eventCancel();">(Cancel)</a>
			</td>
		</tr>
		<tr>	
			<td></td>
			<td>
				<div id="move_div"></div>
			</td>
		</tr>
	</table>	
	<form name="move_form" method="post" action="{link controller=backend.rolegroup action=move id=$id}">
		<input type="hidden" name="moveto">
	</form>
</span>
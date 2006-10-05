<form method="post" action="{link controller=backend.categories action=addaction id=$id}">
	<table>		
		<tr valign=top>	
			<td style='width: 400px;'>
				<!--<div  style='background-color:#f9f9f9; overflow: auto;' id="target"></div>-->				
					{include file='backend/categories/node.tpl'}
			</td>
		</tr>	
	</table>
</form>
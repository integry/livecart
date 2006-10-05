{literal}
<style>
/* Tree menu part*/	
.treeMenuNode {      	                    
    font-size: 10pt; 	   	    
}            
.treeMenuNodeSelected {      	              
   
	font-size: 10pt;         	
}
.treeMenuNodeSelected a {
   background-color:#aaaaaa; 		  	
}
</style>
{/literal}
{if $file_content}
<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td>
			<table style="border-collapse: collapse" >
				<tr> 
					<td class="{if $action == "index"}tabpageselected{else}tabpage{/if}"><a href="{link controller=backend.catalog action=index}">
						{translate text="_mainDetails"}
						</a>
					</td>
					<td class="{if $action == "fields"}tabpageselected{else}tabpage{/if}"><a href="{link controller=backend.catalog action=fields}">
						{translate text="_fields}
						</a>
					</td>
					<td class="{if $action == "filters"}tabpageselected{else}tabpage{/if}"><a href="{link controller=backend.catalog action=filters}">
						{translate text="_filters"}
						</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<div style="position: relative; overflow: auto; height: 435px; width: 600px; border: 1px solid #000000;">
				<table border="0">
					<tr>
						<td width="10">
						</td>
						<td>							
							{include file="$file_content"}							
						</td>
					</tr>
				</table>	
			</div>
		</td>
	</tr>
</table>
<br>
{/if}

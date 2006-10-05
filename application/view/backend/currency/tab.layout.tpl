<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td>
			<table style="border-collapse: collapse" >
				<tr> 
					<td class="{if $action=="index"}tabpageselected{else}tabpage{/if}">
						<a href="{link controller=backend.currency action=index}">
							{translate text="_manageCurrencies"}
						</a>
					</td>
					<td class="{if $action=="ratesForm"}tabpageselected{else}tabpage{/if}">
						<a href="{link controller=backend.currency action=ratesForm}">
							{translate text="_adjustRates"}
						</a>
					</td>					
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<div style="position: relative; overflow: auto; height: 535px; width: 600px; border: 1px solid #000000;">
				<table border="0">
					<tr>
						<td width="10">
						</td>
						<td>
							{include file="backend/currency/$tabPanelFile"}							
						</td>
					</tr>
				</table>	
			</div>
		</td>
	</tr>
</table>

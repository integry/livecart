<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td>
			<table style="border-collapse: collapse" >
				<tr> 
					<td class="{if $action == "viewForm"}tabpageselected{else}tabpage{/if}">
						<a href="{link controller=backend.product action=viewForm id=$id}">
							{translate text="_basicData"}</a>
					</td>
					<td class="{if $action == "priceForm"}tabpageselected{else}tabpage{/if}">
						<a href="{link controller=backend.product action=priceForm id=$id}">
							{translate text="_pricingShipping"}</a>
					</td>
					<td class="{if $action == "imagesForm"}tabpageselected{else}tabpage{/if}">
						<a href="{link controller=backend.product action=imagesForm id=$id}">
							{translate text="_images"}</a>
					</td>
					<td class="{if $action == "relatedProducts"}tabpageselected{else}tabpage{/if}">
						<a href="{link controller=backend.product action=relatedProducts id=$id}">
							{translate text="_relatedProducts"}</a>
					</td>
					<td class="{if $action == "options"}tabpageselected{else}tabpage{/if}">
						<a href="{link controller=backend.product action=options id=$id}">
							{translate text="_options"}</a>
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
							{include file="backend/product/$tabPanelFile"}							
						</td>
					</tr>
				</table>	
			</div>
		</td>
	</tr>
</table>

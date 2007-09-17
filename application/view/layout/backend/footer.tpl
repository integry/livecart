
    <div class="clear"></div>
    </div>
	
	</div>
	<div id="pageFooter">
	
		<table id="workareaBottomShadeContainer">
			<tr>
				<td>
					{img src="image/backend/layout/spacer.gif" id="workareaBottomShadeLeft"}
				</td>
				<td id="workareaBottomShade">
					&nbsp;
				</td>
				<td id="workareaShadeCorner">
					{img src="image/backend/layout/workarea_shade_corner.jpg" id="workareaShadeCornerImage"}
				</td>
			</tr>
		</table>
		<table id="footerContainer">
			<tr>
				<td id="footerLeft">
					&copy; UAB <a href="http://integry.com" target="_blank">Integry Systems</a>, 2007
				</td>
				<td id="footerStretch">
					&nbsp; 
					<a href="{link}" target="_blank" id="frontendLink">{t Store Frontend}</a>
				</td>
			</tr>
		</table>
					
	</div>
</div>	
	

<script type="text/javascript">
    Backend.internalErrorMessage = '{t _internal_error_have_accurred}';
    
    try
    {ldelim}
    	new Backend.LayoutManager();
    {rdelim}
    catch(e)
    {ldelim}
        console.info(e);
    {rdelim}
</script>

{liveCustomization action="menu"}
	
</body>
</html>
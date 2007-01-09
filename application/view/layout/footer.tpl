
        </div>
		
		</div>

		<div id="pageFooter">
		
			<table id="workareaBottomShadeContainer">
				<tr>
					<td>
						<img src="image/backend/layout/spacer.gif" id="workareaBottomShadeLeft">
					</td>
					<td id="workareaBottomShade">
						&nbsp;
					</td>
					<td id="workareaShadeCorner">
						<img src="image/backend/layout/workarea_shade_corner.jpg" id="workareaShadeCornerImage">
					</td>
				</tr>
			</table>
		
			<table id="footerContainer">
				<tr>
					<td id="footerLeft">
						(C) <a href="http://integry.net" target="_blank">UAB "Integry Systems"</a>, 2006
					</td>
					<td id="footerStretch">
						&nbsp; 
					</td>
				</tr>
			</table>
						
		</div>
	</div>	
	
	
<div id="transDialogBox" style="position: absolute;z-index: 10000; display: none;">
	<div class="menuLoadIndicator" id="transDialogIndicator"></div>
	<div id="transDialogContent">
	</div>
</div>

<div id="transDialogMenu" style="display:none;position: absolute;z-index: 60000; background-color: yellow; border: 1px solid black; padding: 3px;"><a href="#" id="transLink">{t _live_translate notranslate=true}</a></div>

<script type="text/javascript">
	var cust = new Backend.Customize();
	cust.setControllerUrl('{link controller=backend.language action=index}');
	cust.initLang();
	new Draggable('transDialogBox');
	Event.observe('transDialogBox', 'mousedown', cust.stopTransCancel.bind(cust), false);
	Event.observe('transLink', 'click', cust.translationMenuClick.bind(cust), false);
	
	new Backend.LayoutManager();
</script>
	
</body>
</html>
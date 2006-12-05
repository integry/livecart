		</div>
	</div>
	<div id="clearfooter"></div>
</div>
<!-- end outer div -->

<div id="footer">

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
				(C) UAB "Integry Systems", 2006
			</td>
			<td id="footerStretch">
				&nbsp;
			</td>
		</tr>
	</table>

</div>

<div id="headerContainer">

	<div id="pageTop">
		<div id="topAuthInfo">
			Logged in as: <span id="headerUserName">rinalds</span> <a href="/logout">(logout)</a>
		</div>
	
		<div id="topBackground">
			<div id="topBackgroundLeft">
				&nbsp; 	 
			</div> 
		</div>

		<div id="navContainer">			
			<div style="float: left;">
				<table>
					<tr>
						<td>
							{backendMenu} &nbsp;			
						</td>
					</tr>
				</table>			
			</div>

			<div id="topLogoContainer">
				<div style="float: right;">
				 	<a href="{link controller=backend.index action=index}">
					 	<img src="image/backend/layout/logo_tr.png" id="topLogoImage">
					</a>
				</div>			
			</div>			
		</div>
		
		<div style="width: 100%;">
			<div style="float: left;">
				<div id="homeButtonWrapper">
					<a href="{link controller=backend.index action=index}">
						<img src="image/backend/layout/top_home_button.jpg" id="homeButton"> 
					</a>
				</div>		
			</div>
			<div id="systemMenu">
					{t _base_help} | <a href="#" onClick="showLangMenu(true);return false;">{t _change_language}</a>
					{backendLangMenu}								
			</div>	
		</div>


		<div id="pageTitleContainer">
			<div id="pageTitle">{$PAGE_TITLE}</div>
		</div>

</div>

<div id="transDialogBox" style="position: absolute;z-index: 10000; display: none;">
	<div class="menuLoadIndicator" id="transDialogIndicator"></div>
	<div id="transDialogContent">
	</div>
</div>

<div id="transDialogMenu" style="position: absolute;z-index: 10000; display: none; background-color: yellow; border: 1px solid black; padding: 3px;"><a href="#" onClick="cust.translationMenuClick(event); return false;">{t _live_translate notranslate=true}</a></div>

<script type="text/javascript">
	var cust = new Backend.Customize();
	cust.setControllerUrl('{link controller=backend.language action=index}');
	cust.initLang();
	new Draggable('transDialogBox');
	Event.observe('transDialogBox', 'mousedown', cust.stopTransCancel, false);
</script>

</body>
</html>
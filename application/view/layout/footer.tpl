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
		<div id="topLogoContainer">
		 	<a href="{link controller=backend.index action=index}">
			 	<img src="image/backend/layout/logo.jpg" id="topLogoImage">
			</a>
		</div> 
	</div>

	<table id="headerContent">
		<tr>
			<td id="headerMenuContainer">
				<table style="width: 100%;">
					<tr>	
						<td id="homeButtonWrapper">
							<a href="{link controller=backend.index action=index}">
								<img src="image/backend/layout/top_home_button.jpg" id="homeButton"> 
							</a>
							<a href="{link controller=backend.index action=index}">
								{t _home}
							</a>
						</td>
						<td id="headerTopMenuContainer">							
											
							
						</td>
					</tr>
					<tr>
						<td colspan="2">
							{foreach from=$MENU item=block}{$block}{/foreach}
						</td>
				</table>				
			</td>
			<td id="systemMenu">
				{t _base_help} | {t _base_change_language}
			</td>
		</tr>	
	</table>

	<div id="pageTitleContainer">
		<div id="pageTitle">{$PAGE_TITLE}</div>
	</div>

</div>

</body>
</html>
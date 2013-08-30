<div id="orderGroupsManagerContainer" class="treeManagerContainer">
	<div id="loadingOrder" style="display: none; position: absolute; text-align: center; width: 100%; padding-top: 200px; z-index: 50000;">
		<span style="padding: 40px; background-color: white; border: 1px solid black;">{t _loading_order}<span class="progressIndicator"></span></span>
	</div>
	<div class="tabContainer" id="orderGroupsTabContainer">
		<ul class="tabList tabs">
			<li id="tabOrders" class="tab inactive">
				<a href="[[ url("backend.customerOrder/orders") ]]?id=_id_">{t _orders}</a>
				<span class="tabHelp">orders</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer"></div>
</div>
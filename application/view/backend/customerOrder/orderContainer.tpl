<div class="yellowMessage" id="orderConfirmation" style="left: 20; top: 180px; position: absolute; display: none;">
   	<div>{t _order_information_has_been_successfully_updated}</div>
</div>
<div class="yellowMessage" id="orderAddressConfirmation" style="left: 20; top: 180px; position: absolute; display: none;">
   	<div>{t _order_address_information_has_been_successfully_updated}</div>
</div>



<div id="orderManagerContainer" class="managerContainer" style="display: none;">
	<fieldset class="container">
		<ul class="menu">
			<li><a href="#cancelEditing" id="cancel_order_edit" class="cancel">{t _cancel_editing_order_info}</a></li>
		</ul>
	</fieldset>
	
	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabOrderInfo" class="tab active">
				<a href="{link controller=backend.customerOrder action=info id=_id_}"}">{t _order_info}</a>
				<span class="tabHelp">orders.edit</span>
			</li>
		</ul>
	</div>
    <div class="sectionContainer maxHeight h--50"></div>
    
    {literal}
    <script type="text/javascript">
        Event.observe($("cancel_order_edit"), "click", function(e) {
            Event.stop(e); 
            var order = Backend.CustomerOrder.Editor.prototype.getInstance(Backend.CustomerOrder.Editor.prototype.getCurrentId(), false);   
            order.cancelForm();
            SectionExpander.prototype.unexpand(order.nodes.parent);
            Backend.hideContainer();
        });
    </script>
    {/literal}
</div>
<div id="orderManagerContainer" class="managerContainer" style="display: none;">
	<fieldset class="container">
		<ul class="menu">
			<li><a href="#cancelEditing" id="cancel_order_edit" class="cancel">{t _cancel_editing_order_info}</a></li>
		</ul>
	</fieldset>
	
	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUserInfo" class="tab active">
				<a href="{link controller=backend.order action=info id=_id_}"}">{t _order_info}</a>
				<span class="tabHelp">orders.edit</span>
			</li>
		</ul>
	</div>
    <div class="sectionContainer maxHeight h--50"></div>
    
    {literal}
    <script type="text/javascript">
        Event.observe($("cancel_order_edit"), "click", function(e) {
            Event.stop(e); 
            var order = Backend.User.Editor.prototype.getInstance(Backend.User.Editor.prototype.getCurrentId(), false);   
            order.cancelForm();
            SectionExpander.prototype.unexpand(order.nodes.parent);
            Backend.User.Editor.prototype.showGroupsContainer();
        });
    </script>
    {/literal}
</div>

<p>All the transactions made in your e-store are considered as <i>orders</i> which are placed directly by customers in the storefront or by administrators (phone, email order).</p>


<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#view">View and Sort Orders</a></li>
	<li><a href="{self}#view">Find Orders</a></li>
	<li><a href="{self}#manage">Manage Orders</a></li>
	<li><a href="{self}#create">Create a new Order</a></li>
</ul>
</fieldset>
</div>

<h3 id="view">View and Sort Orders</h3>

<p>When you access the orders section, all order are displayed. You can select one of the order groups by clicking it in the group tree:</p>

<img src="image/doc/orders/tree.png"/>

<ul>
	<li>New - the most recent orders.</li>
	<li>Backordered - orders that can't be fulfilled because of stock shortage.</li>
	<li>Awaiting Shipment - orders that have been approved.</li>
	<li>Shipped - orders that have been sent to a customer.</li>
	<li>Returned orders usually fail to reach the recipient or for some reason are returned by a customer.</li>
</ul>

<p>Orders are displayed in a table similar to this:</p>
<img src="image/doc/orders/orders.png"/>

<p>By default orders are displayed from the latest to the oldest as they were placed. To <strong>sort</strong> orders, click the "arrow" icon next to appropriate attribute:</p>
<img src="image/doc/orders/sort.png"/>

<div id="attributes"></div>
<p>You can also define what attributes should be displayed in the menu.</p>
<ol>
	<li>Click the "Columns" link at the right:</li>
	<img src="image/doc/orders/columns.bmp"/>
	<li>Add or remove attributes by marking or clearing the checkboxes:</li>
	<img src="image/doc/orders/checkboxes.bmp"/>
	<li>Click the "Change columns" button.</li>
</ol>

<h3 id="view">Find Orders</h3>

<p>You can search for orders using one of the attributes on the toolbar.</p>
<ul>
	<li><ins>Select order category?</ins></li>
	<li>Click an attribute to activate its field:</li>
	<img src="image/doc/orders/search1.bmp"/>
	<li>Supply search criteria and press enter:</li>
	<img src="image/doc/orders/search2.bmp"/>
</ul>
<p>Results that match your criteria appear below. You can as well define attributes displayed in the toolbar. <a href="{self}#attributes"><small>Tell me how</small></a>.</p>

<h3 id="manage">Manage Orders</h3>
<p>You can quickly manage your orders by selecting multiple orders for processing:</p>
<ul>
	<li>Select orders by marking a checkbox at the left:</li>
	<img src="image/doc/orders/orders.png"/>
	<li>With selected - click a drop-down list and select an action to apply.</li>
	<li>Click the "Process" button to save changes.</li>
</ul>

<p>To edit a <strong>single order</strong>:</p>
<ul>
	<li><ins>Click the "view order" link.</ins></li>
	<p>Order info page opens. Here you can edit all the orders details as well as shipments, payments and feedback.</p>
	<p><ins>...</ins></p>	
</ul>

<p class="note"><strong>Note</strong>: Shipments are different part of the same order. Sometimes if any of the items are not available at the moment, according to the customer's preference it might be chosen to split an order into separate shipments. Usually the rest of the parcel is sent when the the products become available.</p>

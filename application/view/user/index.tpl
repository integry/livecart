{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_account} ([[user.firstName]]){% endblock %}

{% block left %}
	[[ partial('user/userMenu.tpl', ['current': "homeMenu"]) ]]
{% endblock %}

{% block content %}

	<h2>{t _recent_orders}</h2>
	<div ng-init="orders = [[ json(orders) ]]">
	<div ng-repeat="order in orders">
		
		<div class="well">
			<div class="row">
				<div class="col-sm-3">
					<div><strong>{{ order.invoiceNumber }}</strong></div>
					<div>{{ order.dateCompleted }}</div>
					<div><strong>{{ order.formattedTotal[order.currencyID] }}</strong></div>
				</div>
				<div class="col-sm-9">
					<ul class="list-unstyled">
						<li ng-repeat="item in order.OrderedItems">
							{{ item.count }} x {{ item.name }}
						</li>
					</ul>
				</div>

			</div>
		</div>
		
	</div>
	</div>

{% endblock %}

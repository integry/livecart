<p>
	LiveCart code is structured into a <a class="external" href="http://en.wikipedia.org/wiki/Model-view-controller">Model-View-Controller</a> (MVC) architecture, which means that the application is basically structured into three distinct layers that provide separation of presentation logic, business logic and data:
</p>

<ul>
	<li>
		<a href="{help .model}">Model</a> - represents business entities: products, categories, orders, users, etc. - and provides means to access and manipulate object data. LiveCart model classes implement <a class="external" href="http://en.wikipedia.org/wiki/ActiveRecord">Active Record pattern</a>, which allows to completely abstract the database operations.
	</li>
	<li>
		<a href="{help .view}">View</a> - generates application output. In LiveCart views are simply <a class="external" href="http://smarty.php.net/">Smarty</a> templates.
	</li>
	<li>
		<a href="{help .controller}">Controller</a> - works as a glue between a model and a view and defines application behavior. In essence, main controller responsibilities are to read/change model state and pass model data to view for displaying.
	</li>
</ul>	
	
<p>
	Such architecture allows to achieve a great code separation by responsibility, which provides several additional benefits:
</p>

<ul>
	<li>
		Enforces clean and organized code structure.
	</li>
	<li>
		Easy to make changes - one doesn't even need to touch business or model logic code when it's only necessary to modify the presentation template.
	</li>
	<li>
		Different methods of presentation for the same data are possible (just change the view to return data in XML, for example).
	</li>
	<li>
		Possible to extend application behavior without a need to change Controller code. The Controller passes the data to View through an intermediary Response object, which can be altered before it reaches the View.
	</li>


</ul>
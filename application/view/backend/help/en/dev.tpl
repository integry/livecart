<p>
	LiveCart supports two methods of integration with other systems and extending it's functionality - SOAP API and PHP API.
</p>

<h3>SOAP API</h3>

<ul>
	<li>Useful when integrating LiveCart into remote business systems like external inventory, product management, accounting and others</li>
	<li>Basically used to exchange data (create, retrieve, update, delete) with other systems</li>
</ul>

<h3>PHP API</h3>

<ul>
	<li>Allows to modify and extend LiveCarts behaviour without changing the underlying code</li>
	<li>Allows to use LiveCart data model classes, thus providing the ability to perform any native LiveCart data operation</li>
</ul>

<h3>Combined usage</h3>

<p>
	The two APIs can be combined and used together, for example, by using a PHP API to catch a system event (incoming order, etc.) and send a SOAP request to 3rd party application.
</p>
<p>
	LiveCart has been developed keeping developers needs in mind. Most of currently available shopping cart programs are a pain to modify, because they lack clear structure, contain a lot of ad-hoc <em>spaghetti</em> code all over the place, do no provide clear interfaces for hooking in your own functionality, no APIs, not to even mention a system documentation or at least well commented code. 
</p>

<p>
	We have attempted to solve this problem for once by creating a truly developer friendly application, with well structured code, which follows strict conventions, providing means to extend or interact with the system without a need to change the underlying code as well as providing as much documentation as possible to make it easier to get around in LiveCart code.
</p>

<h3>Application</h3>
<p>
	If you're looking to do mode complex customizations, you should introduce yourself with <a href="{help .app}">LiveCart architecture and basic principles</a> first.
</p>

<h3>Plugins</h3>
<p>
	LiveCart provides standard interfaces for plugging in new classes for additional payment processor/gateway support, real-time shipping cost calculation and automatic currency rate updates.
</p>

<h3>APIs</h3>
<p>
	LiveCart provides <a href="{help .api}">two APIs</a> to make it easier to integrate with other systems - <a href="{help .api.soap}">SOAP API</a> and <a href="{help .api}">PHP API</a>. SOAP API is useful for exchanging data with remote systems, however PHP API can be used to do system level customizations without modifying the original code.
</p>

<h3>But I only need to modify the look of my pages?</h3>
<p>
	Unless you need your store to support some special features or integrate with other systems, you won't need to touch any code or, in fact, write any code.
</p>
<p>
	There are many other ways to customize your LiveCart installation. You'll find more information on this in <a href="{help /cust}">Customization</a> section of this manual.
</p>
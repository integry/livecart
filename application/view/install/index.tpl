<h1>Verifying Environment and System Requirements</h1>

<p>
	Some problems were detected that need to be resolved before the installation may continue:
</p>

<dl class="requirements">
	{% for req, result in requirements %}
		{% if 1 != result %}
			<div class="{% if 1 == result %}pass{% else %}fail{% endif %}">
				<dt>[[ t(req) ]]</dt>
				<dd>
				{% if 1 == result %}
					<img src="image/silk/gif/tick.gif" />
				{% else %}
					{% if 'checkWritePermissions' == req %}
						The following directories are not writable:
						<ul id="notWritable">
						{% for dir in result %}
							<li>[[dir]]</li>
						{% endfor %}
						</ul>		
						<p>
							{t _writePermissionsFix}							
						</p>				
					{% else %}
						<img src="image/silk/gif/delete.gif" />
						{% if 0 == result %}
							<div class="reqError">
								{translate text="`req`_error"}
							</div>
						{% endif %}
					{% endif %}
				{% endif %}
				</dd>
			</div>
		{% endif %}
	{% endfor %}
</dl>

<p>
	Please reload this page when the issues listed above have been resolved.
</p>
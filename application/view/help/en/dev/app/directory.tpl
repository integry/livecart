{literal}
<style>
.directory_structure, .directory_structure ul
{
	background: none;
	padding: 0;		
}

.directory_structure li
{
	font-weight: bold;
}

.directory_structure li p
{
	font-weight: normal;
	margin-top: 5px;
	padding: 0;
}

</style>
{/literal}

<ul class="directory_structure">
	<li>
		application
		<p>
			LiveCart program code
		</p>		
		<ul>
			<li>
				configuration
				<p>
					Various application configuration data
				</p>
				<ul>
					<li>
						backend_menu
						<p>
							Backend navigation menu	
						</p>
					</li>
					<li>
						language
						<p>
							Default translation definitions	
						</p>
					</li>				
					<li>
						registry
						<p>
							Default configuration values (settings)	
						</p>
					</li>
					<li>
						route
						<p>
							Page URL configuration	
						</p>
					</li>				
				</ul>
			</li>
			<li>
				controller
				<p>
					Controller classes	
				</p>			
			</li>
			<li>
				event
				<p>
					Event hooks for custom integration	
				</p>			
			</li>
			<li>
				helper
				<p>
					Various application-specific helper classes - custom validation rules, view helpers, etc.	
				</p>			
			</li>
			<li>
				model
				<p>
					Model classes
				</p>			
			</li>
			<li>
				view
				<p>
					Default view templates
				</p>			
			</li>			
		</ul>
	</li>
	
	<li>
		cache
		<p>
			Temporary data
		</p>
	</li>
	
	<li>
		framework
		<p>
			Application kernel, which handles application flow by implementing lower-level functionality - requests, routing, validation, controller responses, etc.
		</p>
	</li>

	<li>
		install
		<p>
			Contains database schemas and setup scripts
		</p>
	</li>

	<li>
		library
		<p>
			Various libraries
		</p>
	</li>

	<li>
		public
		<p>
			The <em>public_html</em> content
		</p>
	</li>

	<li>
		storage
		<p>
			Used to store permanent files (instead of temporary files, which are stored in the <em>cache</em> directory), which are written/created by LiveCart. Mostly used for storing modified configuration data (language translations, settings, templates, etc.)
		</p>
	</li>
</ul>
<ul style="display: none;">
	<li id="navTopItem-template">
		<div>
			<div>
				<div>
					<a href=""></a>
					<ul>
					</ul>
				</div>
			</div>
		</div>
	</li>		
	<li id="navSubItem-template">
		<div>
			<div>
				<div>
					<a href=""></a>
				</div>
			</div>
		</div>
	</li>		
</ul>

{literal}
<script type="text/javascript">
{/literal}	
	new Backend.NavMenu({$menuArray}, '{$controller}', '{$action}');
</script>
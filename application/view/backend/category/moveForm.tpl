<h3>Move To Node</h3>
{$group}
{literal}
<script language="javascript">
function makeMove(id) {
	  
  	document.move_form.moveto.value = id;	  	
	document.move_form.submit();
}
</script>
{/literal}
<form name="move_form" method="post" action="{link controller=backend.catalog action=move id=$id}">
	<input type="hidden" name="moveto">
</form>
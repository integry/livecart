<div style="font-size: 18px;">Modify category details</div>
<hr/>
{form handle=$catalogForm action="controller=backend.catalog action=save"}
<label for="name">Category name:</label> 
{textfield name="name" id="name"}

<label for="details">Details:</label> 
{textarea name="name" id="details"}

<label for="handle">Handle:</label> 
{textfield name="name" id="handle"}
{/form}
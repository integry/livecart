<h1>MySQL Database Information</h1>

<div>

    {form action="controller=install action=setDatabase" method="POST" handle=$form}

	{error for="connect"}
		<div class="fail" style="float: left;">
			{$msg}
		</div>
		<div class="clear"></div>
	{/error}

    <p>
        {err for="server"}
            {{label Database server:}}
            {textfield class="text"} <div style="margin-top: -5px;"><small>Usually <em>localhost</em></small></div>
        {/err}    
    </p>

    <p>
        {err for="name"}
            {{label Database name:}}
            {textfield class="text"}
        {/err}    
    </p>

    <p>
        {err for="username"}
            {{label Database user name:}}
            {textfield class="text"}
        {/err}    
    </p>

    <p>
        {err for="password"}
            {{label Password:}}
            {textfield type="password" class="text password"}
        {/err}    
    </p>

    <div class="clear"></div>
    <input type="submit" value="Continue installation" />
    {/form}
</div>
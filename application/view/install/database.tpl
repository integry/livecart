<h1>MySQL Database Information</h1>

<div style="margin-left: 50px;">

    {form action="controller=install action=setDatabase" method="POST" handle=$form}

    <p>
        {err for="server"}
            {{label Database server:}}
            {textfield}
        {/err}    
    </p>

    <p>
        {err for="name"}
            {{label Database name:}}
            {textfield}
        {/err}    
    </p>

    <p>
        {err for="username"}
            {{label User name:}}
            {textfield}
        {/err}    
    </p>

    <p>
        {err for="password"}
            {{label Password:}}
            {textfield type="password"}
        {/err}    
    </p>

    <div class="clear"></div>
    <input type="submit" value="Continue installation" />
    {/form}
</div>
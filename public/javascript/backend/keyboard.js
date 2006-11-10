var KEY_TAB	  = 9;
var KEY_ENTER = 13;
var KEY_SHIFT = 16;
var KEY_ESC   = 27;
var KEY_UP    = 38;	
var KEY_DOWN  = 40;	
var KEY_DEL   = 46;

/** 
 * Determines which key was pressed
 * @return int Key number 
 */
function getPressedKey(e)
{
    // IE
	if (window.event) 
    {
    	keynum = e.keyCode;
    }

    // Netscape/Firefox/Opera
	else if (e.which) 
    {
    	keynum = e.which;
    }	  	

    return keynum;
}

/** 
 * Deselects any window text (except in controls) 
 */
function deselectText() 
{
	if (document.selection)
	{
		document.selection.empty();
	}
	else if (window.getSelection)
	{
	    window.getSelection().removeAllRanges();
	}
}
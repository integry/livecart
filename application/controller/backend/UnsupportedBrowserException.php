<?php

/**
 *  At this time the backend only supports Mozilla Firefox browser and this exception is thrown
 *  when the backend is accessed with other browsers
 */
class UnsupportedBrowserException extends ApplicationException
{
}

?>
<?php
/**
 * A class that all provides a base class for any class that wants to implement a site action
 *
 * Common functions used across the various sub-classes should go in here
 *
 * The constants are used in index.php to indicate how a particular URL should be handled
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    abstract class Siteaction
    {
/**
 * Indicates that there is an Object that handles the call
 */
	const OBJECT	= 1;
/**
 * Indicates that there is only a template for this URL.
 */
	const TEMPLATE	= 2;
/**
 * Handle an action
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function handle($context, $local)
	{ # should never get called really
	    $context->divert('/');
	    /* NOT REACHED */
	}
    }
?>

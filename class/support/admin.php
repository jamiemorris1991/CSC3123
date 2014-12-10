<?php
/**
 * A class that contains code to handle any /admin related requests.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Admin extends Siteaction
    {
/**
 * Handle various admin operations /admin/xxxx
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function handle($context, $local)
	{
	    $tpl = 'admin.twig';
	    $rest = $context->rest();
	    switch ($rest[0])
	    {
	    case 'hack':
		R::freeze(FALSE);
		include('hack.php');
		break;

	    case 'fail': # this lets you test error handling
		$x = 2 / 0;
		break;

	    case 'throw': # this lets you test exception handling
		throw new Exception('Unhandled Exception Test');

	    case 'pages':
		$tpl = 'admin/pages.twig';
		break;

	    case 'users':
		$tpl = 'admin/users.twig';
		break;

	    case 'info':
	        phpinfo();
		exit;

	    default :
		break;
	    }
	    return $tpl;
	}
    }
?>

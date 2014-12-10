<?php
/**
 * A class that contains code to implement a contact page
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Contact extends Siteaction
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
            if ($context->postpar('details', '') != '')
            { # there is a post
                $local->addval('done', TRUE);
            }
            return 'contact.twig';
        }
    }
?>

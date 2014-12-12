<?php
/**
 * A class that contains code to implement a view of all logs
 *
 * @author Jamie Morris
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
            
            return 'logs.twig';
        }
    }
?>
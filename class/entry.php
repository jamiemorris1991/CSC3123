<?php
/**
 * A class that contains code to implement a contact page
 *
 *@Author Jamie Morris
 *
 */
    class Entry extends Siteaction
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
            return 'entry.twig';
        }
        
        public function newEntry($contect,$local)
        {
            $l = R::dispense('log');
            $l->title = $context->mustpostpar('title');
            $l->body = $context->mustpostpar('body');
            $l->created = R::isoDateTime();
            $l->lastedit = R::isoDateTime();
            $l->attachment =$context->postpar('attachment');;
        }

    }
?>

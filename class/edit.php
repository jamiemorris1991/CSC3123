<?php
/**
 * A class that contains code to modify log entr
 *
 *@Author Jamie Morris
 *
 */
    class Edit extends Siteaction
    {
/**
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 */
        public function handle($context, $local)
        {
            $l = R::load('log',$context->rest()[0]);
            if($l->id)
            {
                $local->addval('log',$l);
                return 'entry.twig';
            }
        }
    }
?>

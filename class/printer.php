<?php
/**
 * A class that contains code to modify log entr
 *
 *@Author Jamie Morris
 *
 */
    class printer extends Siteaction
    {
/**
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 */
        public function handle($context, $local)
        {
            
            $u =$context->user;
            if($l->id)
            {
                $local->addval('log',$l);
                return 'entry.twig';
            }
        }
    }
?>
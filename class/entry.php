<?php
/**
 * A class that contains code to modify log entr
 *
 *@Author Jamie Morris
 *
 */
    class Entry extends Siteaction
    {
/**
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 */
        public function handle($context, $local)
        {
            if($context->action()=='newentry')
            {
                return $this->newentry($context, $local);
            }
            else
            {
            return 'entry.twig';
            }
        }
        
/**
 * Handle creation of a new entry
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return index
 */
        public function newentry($context,$local)
        {
            $l = R::dispense('log');
            $l->title = $context->postpar('title','NULL');
            $l->body = $context-> postpar('body','NULL');
            $l->created = R::isoDateTime();
            $l->lastedit = R::isoDateTime();
            $l->attachment =$context->postpar('attachment','NULL');
            R::store($l);
            $user = $context->user();
            $user->xownLog[] = $l;
            R::store($user);
            $local->addval('message', 'Log Entry Added!');
            return 'index.twig';
        }

    }
?>

<?php
/**
 * A class that contains code to return info needed in various places on the site
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014 Newcastle University
 *
 */
    class SiteInfo
    {
/**
 * Get all the user beans
 *
 * @return array
 */
        public function users()
        {
            return R::findAll('user', 'order by login');
        }
/**
 * Get all the page beans
 *
 * @return array
 */
        public function pages()
        {
            return R::findAll('page', 'order by name');
        }
/**
 * Get all the log beans
 *
 * @return array
 */
        public function logs()
        {
            return R::findALL('log','order by lastedit DESC');
        }
    }
?>

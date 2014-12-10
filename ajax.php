<?php
/**
 * Ajax entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    error_reporting(E_ALL|E_STRICT);
/*
 * Setup the autoloader
 */
    set_include_path(
        implode(PATH_SEPARATOR, array(
            implode(DIRECTORY_SEPARATOR, array(__DIR__, 'class')),
            implode(DIRECTORY_SEPARATOR, array(__DIR__, 'class/support')),
            implode(DIRECTORY_SEPARATOR, array(__DIR__, 'lib')),
            get_include_path()
        ))
    );
    spl_autoload_extensions('.php');
    spl_autoload_register();

    $local = new Local(__DIR__, TRUE, TRUE, TRUE); # Ajax, debug on, load twig
    (new Ajax)->handle(new Context($local), $local);
?>

<?php
/**
 * Main entry point of the system
 *
 * The framework assumes a self contained directory structure for a site like this :
 *
 * DOCUMENT_ROOT
 *    /sitename             This can be omitted if the site is the only one present and at the root
 *        /assets
 *            /css          CSS files
 *            /i18n         Any internationalisation files you may need
 *            /images       Image files
 *            /js           JavaScript
 *            /...          Any other stuff that can be accessed without intermediation through PHP
 *        /class            PHP class definition files named "classname.php"
 *        /errors           Files used for generating error pages.
 *        /lib              PHP files containing non-class definitions
 *        /twigcache        If twig cacheing is on this is where it caches
 *        /twigs            TWIG template files go in here
 *        /vendor           If you are using composer then it puts stuff in here.
 *
 * The .htaccess file directs
 *         anything in /assets to be served by Apache.
 *         anything beginning "ajax" to be called directly i.e. ajax.php (this may or may not be useful - remove it if not)
 *         everything else gets passed into this script where it treats the URL thus:
 *                 /                        =>        /home and then
 *                 /action/r/e/st/          =>        Broken down in Context class. An action and an array of parameters.
 *
 *         Query strings and/or post fields are in the $_ arrays as normal.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2014 Newcastle University
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

    $local = new Local(__DIR__, FALSE, TRUE, TRUE); # Not Ajax, debug on, load twig
    $context = new Context($local);

    $action = $context->action();
    if ($action === '')
    {
        $action = 'home';
    }

    $page = R::findOne('page', 'name=? and active=?', array($action, 1));
    if (!is_object($page))
    { # No such page or it is marked as inactive
        $context->divert('/error/404?page='.urlencode($action));
        # does not return
    }

    if (($page->needlogin) && !$context->hasuser())
    { # not logged in or not an admin
        $context->divert('/login');
    }

    if ($page->admin && !$context->hasadmin())
    { # not logged in or not an admin
        (new Web)->noaccess('You must be an administrator');
    }

    if ($page->devel && !$context->hasdeveloper())
    { # not logged in or not a developer
        (new Web)->noaccess('You must be a developer');
    }

    $local->addval('context', $context);
    $local->addval('page', $action);
    $local->addval('siteinfo', new SiteInfo($local));

    switch ($page->kind)
    {
    case Siteaction::OBJECT:
        $op = new $page->source;
        $tpl = $op->handle($context, $local);
        break;

    case Siteaction::TEMPLATE:
        $tpl = $page->source;
        break;

    default :
        (new Web)->internal('Weird error');
    }

    $local->render($tpl);
?>

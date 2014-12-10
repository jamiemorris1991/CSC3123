<?php
/**
 * This is a class that maintains values about the local environment and does error handling
 *
 * Template rendering is done in here also so TWIG is initialised in this class. This allows TWIG
 * to be used for things like generating nice offline pages.
 *
 */
    class Local
    {
/**
 * @var	string		The absolute path to the site directory
 */
        private $basepath;
/**
 * @var	string		The name of the site directory
 */
        private $basedname	= '';

/**
 * @var	boolean		If TRUE then ignore trapped errors
 */
        private $errignore	= FALSE;	# needed for checking preg expressions....
/**
 * @var	boolean		Set to TRUE if an error was trapped and ignored
 */
        private $wasignored	= FALSE;
/**
 * @var array		A list of errors that have been emailed to the user. Only send a message once.
 */
        private $senterrors	= array();
/**
 * @var	boolean		If TRUE then we are in developer mode and doing debugging
 */
        private $debug		= FALSE;
/**
 * @var	boolean		If TRUE then we are in ajax code and so error reporting is different
 */
        private $ajax		= FALSE;
/**
 * @var	array		An array of email addresses for system administrators
 */
        private $sysadmin	= array(Config::SYSADMIN);
/**
 * @var	object		the Twig renderer
 */
        private $twig		= NULL;
/**
 * @var	array		Key/value array of data to pass into template renderer
 */
        private $tvals		= array();
/**
 * Tell sysadmin there was an error
 *
 * @param string	$msg	An error messager
 * @param string	$type	An error type
 * @param string 	$file	file in which error happened
 * @param string	$line	Line at which it happened
 *
 * @return void
 */
        private function telladmin($msg, $type, $file, $line)
        {
            if ($this->debug)
            {
//		require 'kint/Kint.class.php';
//		Kint::dump(1);
            }
            $ekey = $file.'/'.$line.'/'.$type.'/'.$msg;
            if (!isset($this->senterrors[$ekey]))
            {
                $ve = @var_export(debug_backtrace(), TRUE);
                mail(implode(',', $this->sysadmin),
                    date('c').' System Error - '.$msg.' ',
                    'Type : '.$type."\n".
                    $file.' Line '.$line."\n".$ve);
                $this->senterrors[$ekey] = TRUE;
            }
        }
/**
 * Generate a 500 and possibly an error page
 *
 * @return void
 */
        private function make500()
        {
            if (!headers_sent())
            { # haven't generated any output yet.
                header('HTTP/1.1 500 Internal Server Error');
                if (!$this->ajax)
                { # not in an ajax page so try and send a pretty error
                    include($this->basepath.'/errors/syserror.php');
                }
            }
        }
/**
 * Shutdown function - this is used to catch certain errors that are not otherwise trapped and
 * generate a clean screen as well as an error report to the developers.
 *
 * It also closes the RedBean connection
 */
        public function shutdown()
        {
            if ($error = error_get_last())
            { # are we terminating with an error?
                if (isset($error['type']) && ($error['type'] == E_ERROR || $error['type'] == E_PARSE || $error['type'] == E_COMPILE_ERROR))
                { # tell the developers about this
                    $this->telladmin(
                	$error['message'],
                        $error['type'],
                        $error['file'],
                	$error['line']
                    );
                    $this->make500();
                }
                else
                {
                    echo '<div>There has been a system error</div>';
                }
            }
            R::close(); # close RedBean connection
        }
/**
 * Deal with untrapped exceptions - see PHP documentation
 *
 * @param Exception	$e
 */
        public function exception_handler($e)
        {
            $this->telladmin(
                $e->getMessage().' ',
                get_class($e),
                $e->getFile(),
                $e->getLine()
            );
            $this->make500();
            exit;
        }
/**
 * Called when a PHP error is detected - see PHP documentation for details
 *
 * Note that we can chose to ignore errors. At the moment his is a fairly rough mechanism.
 * It could be made more subtle by allowing the user to specifiy specific errors to ignore.
 * However, exception handling is a much much better way of dealing with this kind of thing
 * whenever possible.
 *
 * @param integer	$errno
 * @param string	$errstr
 * @param string	$errfile
 * @param integer	$errline
 * @param string	$errcontext
 *
 * @return boolean
 */
        public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
        {
            if ($this->errignore)
            { # wanted to ignore this so just return
                $this->wasignored = TRUE; # remember we did ignore though
                return TRUE;
            }

            $this->telladmin(
                $errno.' '.$errstr,
                'Error',
                $errfile,
                $errline
            );
            if ($this->debug || in_array($errno, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR)))
            { # this is an internal error or we are debugging, so we need to stop
                $this->make500();
                exit;
            }
/*
 * If we get here it's a warning or a notice, so we arent stopping
 *
 * Change this to an exit if you don't want to continue on any errors
 */
            return TRUE;
        }
/**
 * Allow system to ignore errors
 *
 * This always clears the wasignored flag
 *
 * @param boolean	$ignore	If TRUE then ignore the error otherwise stop ignoring
 *
 * @return boolean	The last value of the wasignored flag
 */
        public function eignore($ignore)
        {
            $this->errignore = $ignore;
            $wi = $this->wasignored;
            $this->wasignored = FALSE;
            return $wi;
        }
/**
 * Join the arguments with DIRECTORY_SEPARATOR to make a path name
 *
 * @return string
 */
        public function makepath()
        {
            return implode(DIRECTORY_SEPARATOR, func_get_args());
        }
/**
 * Initialise twig template engine
 *
 * @param boolean	$debug	if TRUE then enable in the TWIG debug mode
 * @param boolean	$cache	if TRUE then enable the TWIG cache
 *
 * @return void
 */
        public function setuptwig($debug = TRUE, $cache = FALSE)
        {
            require_once('Twig/Autoloader.php');
            Twig_Autoloader::register();
            $this->twig = new Twig_Environment(
                new Twig_Loader_Filesystem($this->makepath($this->basepath, 'twigs')),
                array('cache' => $cache ? $this->makepath($this->basepath, 'twigcache') : FALSE, 'debug' => $debug)
            );
            if ($debug)
            {
                $this->twig->addExtension(new Twig_Extension_Debug());
            }
/*
 * A set of basic values that get passed into the TWIG renderer
 *
 * Add new key/value pairs to this array to pass values into the twigs
 */
            $this->tvals = array(
                'base'          => $this->base(),
                'assets'	=> $this->base().'/assets', # for HTML use so the / is OK here
            );
        }
/**
 * Render a twig and return the string - do nothing if the template is the empty string
 *
 * @param string	$tpl	The template
 *
 * @return string
 */
        public function getrender($tpl)
        {
            return $tpl != '' ? $this->twig->loadtemplate($tpl)->render($this->tvals) : '';
        }
/**
 * Render a twig - do nothing if the template is the empty string
 *
 * @param string	$tpl	The template
 */
        public function render($tpl)
        {
            echo $this->getrender($tpl);
        }
/**
 * Add a value into the values stored for rendering the template
 *
 * @param string	$vname		The name to be used inside the twig
 * @param mixed		$value		The value to be stored
 *
 * @return void
 */
        public function addval($vname, $value)
        {
            $this->tvals[$vname] = $value;
        }
/**
 * Return the name of the directory for this site
 *
 * @return string
 */
        public function base()
        {
            return $this->basedname;
        }
/**
 * Return the path to the directory for this site
 *
 * @return string
 */
        public function basedir()
        {
            return $this->basepath;
        }
/**
 * Set up local information in the constructor
 *
 * @param string	$basedir	The full path to the site directory
 * @param boolean	$debug		If TRUE then we are developing the system
 * @param boolean	$loadtwig	if TRUE then load in Twig.
 */
        public function __construct($basedir, $ajax, $debug, $loadtwig)
        {
#
# For a fixed place production system you probably just want to replace all the directory munging with constants!
#
            $this->basepath = $basedir;
        #    $bd = $basedir;
        #    $bdr = array();
        #    while ($bd != $_SERVER['DOCUMENT_ROOT'])
        #    {
        #	$pp = pathinfo($bd);
        #	$bd = $pp['dirname'];
        #	$bdr[] = $pp['basename'];
        #    }
            $this->basedname = Config::BASEDNAME;
            $this->ajax = $ajax;
            $this->debug = $debug;
/*
 * Set up all the system error handlers
 */
            set_exception_handler(array($this, 'exception_handler'));
            set_error_handler(array($this, 'error_handler'));
            register_shutdown_function(array($this, 'shutdown'));

            if ($loadtwig)
            { # we want twig - there are some autoloader issues out there that addign twig seems to fix....
                $this->setuptwig($debug, FALSE);
            }

            if (file_exists($this->makepath($this->basepath, 'offline')))
            { # go offline before we try to do anything else...
                $this->render('offline.twig', array('msg' => file_get_contents($this->makepath($basedir, 'offline'))));
                exit;
            }
/*
 * Initialise database access
 */
            require('rb.php'); # RedBean interface
            if (Config::DBHOST != '')
            { # looks like there is a database configured
                R::setup('mysql:host='.Config::DBHOST.';dbname='.Config::DB, Config::DBUSER, Config::DBPW); # mysql initialiser
                R::freeze(!$debug); # freeze DB for production systems
            }
        }
    }
?>

<?php
/**
 * A class that stores various useful pieces of data for access throughout the rest of the system.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Context
    {
/**
 * @var object		an instance of Local
 */
        private $local		= NULL;		# singleton local object
/**
 * @var object		NULL or an object decribing the current logged in User
 */
        private $luser		= NULL;		# Current user bean if we have logins....
/**
 * @var integer		Counter used for generating unique ids
 */
        private $idgen = 0;			# used for generating unique ids
/**
 * @var string		The first component of the current URL
 */
        private $reqaction	= 'home';	# the first segment of	the URL
/**
 * @var array		The rest of the current URL exploded at /
 */
        private $reqrest	= array();	# the rest of the URL

/*
 ***************************************
 * $_GET and $_POST fetching methods
 ***************************************
 */
/**
 * Is the key in the $_GET array?
 *
 * @param string	$name	The key
 *
 * @return boolean
 */
        public function hasgetpar($name)
        {
            return filter_has_var(INPUT_GET, $name);
        }
/**
 * Is the key in the $_POST array?
 *
 * @param string	$name	The key
 *
 * @return boolean
 */
        public function haspostpar($name)
        {
            return filter_has_var(INPUT_POST, $name);
        }
/**
 * Look in the _GET array for a key and return its trimmed value
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return mixed
 */
        public function mustgetpar($name, $fail = TRUE)
        {
            if (filter_has_var(INPUT_GET, $name))
            {
                return trim($_GET[$name]);
            }
            if ($fail)
            {
                (new Web)->bad();
            }
            return NULL;
        }

/**
 * Look in the _POST array for a key and return its trimmed value
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return mixed
 */
        public function mustpostpar($name, $fail = TRUE)
        {
            if (filter_has_var(INPUT_POST, $name))
            {
                return trim($_POST[$name]);
            }
            if ($fail)
            {
                (new Web)->bad();
            }
            return NULL;
        }
/**
 * Look in the _GET array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function getpar($name, $dflt)
        {
            return filter_has_var(INPUT_GET, $name) ? trim($_GET[$name]) : $dflt;
        }
/**
 * Look in the _POST array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function postpar($name, $dflt)
        {
            return filter_has_var(INPUT_POST, $name) ? trim($_POST[$name]) : $dflt;
        }
/**
 * Look in the _GET array for a key and apply filters
 *
 * @param string	$name		The key
 * @param int		$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filtergetpar($name, $filter, $options = '')
        {
            return filter_input(INPUT_GET, $name, $filter, $options);
        }
/**
 * Look in the _POST array for a key and  apply filters
 *
 * @param string	$name		The key
 * @param int		$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filterpostpar($name, $filter, $options = '')
        {
            return filter_input(INPUT_POST, $name, $filter, $options);
        }
 /*
  ***************************************
  * REST functions
  ***************************************
  */
/**
 * Return the main action part of the URL as set by .htaccess
 *
 * @return string
 */
        public function action()
        {
            return $this->reqaction;
        }
/**
 * Return the part of the URL after the main action as set by .htaccess
 *
 * @return string
 */
        public function rest()
        {
            return $this->reqrest;
        }
/*
 ***************************************
 * User related functions
 ***************************************
 */
/**
 * Return the current logged in user if any
 *
 * @return object
 */
        public function user()
        {
            return $this->luser;
        }
/**
 * Do we have a logged in user?
 *
 * @return boolean
 */
        public function hasuser()
        {
            return is_object($this->luser);
        }
/**
 * Do we have a logged in admin user?
 *
 * @return boolean
 */
        public function hasadmin()
        {
            return $this->hasuser() && $this->luser->isadmin();
        }
/**
 * Do we have a logged in developer user?
 *
 * @return boolean
 */
        public function hasdeveloper()
        {
            return $this->hasuser() && $this->luser->isdeveloper();
        }
/*
 ***************************************
 * Miscellaneous utility functions
 ***************************************
 */
/**
 * Return a name for this site
 *
 * @string
 */
        public function sitename()
        {
            return Config::SITENAME;
        }
/**
 * Generates a new, unique, sequential id value
 *
 * @param string	$id The prefix for the id
 *
 * @return string
 */
        public function newid($str = 'id')
        {
            $this->idgen += 1;
            return $str.$this->idgen;
        }
/**
 * Check to see if there is a session and return a specific value form it if it exists
 *
 * @param string	$var	The variable name
 * @param boolean	$fail	If TRUE then exit with an error returnif the value  does not exist
 *
 * @return mixed
 */
        public function sessioncheck($var, $fail = TRUE)
        {
            if (isset($_COOKIE[ini_get('session.name')]))
            {
                session_start();
                if (isset($_SESSION[$var]))
                {
                    return $_SESSION[$var];
                }
            }
            if ($fail)
            {
                (new Web)->noaccess();
            }
            return NULL;
        }
/**
 * Generate a Location header for within this site
 *
 * @param string		$where	The page to divert to
 */
        public function divert($where)
        {
            (new Web)->relocate($this->local->base().$where);
        }

/**
 * Load a bean or fail with a 400 error
 *
 * @param string		$table	A bean type name
 * @param integer	$id	A bean id
 *
 * @return object
 */
        public function load($bean, $id)
        {
            $foo = R::load($bean, $id);
            if ($foo->getID() == 0)
            {
                (new Web)->bad($bean.' '.$id);
            }
            return $foo;
        }

/*
 ***************************************
 * The constructor
 ***************************************
 */
 /**
 * Initialise the context
 *
 * @param boolean	$local	The singleton local object
 */
        public function __construct($local)
        {
            $this->local = $local;
            $this->luser = $this->sessioncheck('user', FALSE); # see if there is a user variable in the session....
            if (isset($_SERVER['REDIRECT_URL']) && !preg_match('/index.php/', $_SERVER['REDIRECT_URL']))
            {
/**
 * @todo There seems to be varying behavious across systems that generate REDIRECT_URL
 * that needs to be investigated and handled properly. Some systems seems to have the original in here
 * and others the rewritten. Weird.
 */
                $uri = $_SERVER['REDIRECT_URL'];
            }
            else
            {
                $uri = $_SERVER['REQUEST_URI'];
                if ($_SERVER['QUERY_STRING'] != '')
                { # there is a query string so get rid it of it from the URI
                    list($uri) = explode('?', $uri);
                }
            }
            $req = array_filter(explode('/', $uri)); # array_filter removes empty elements - trailing / or multiple /
/*
 * If you know that the base directory is empty then you can delete the next test block.
 *
 * You can also optimise out the loop if you know how deep you are nested in sub-directories
 *
 * The code here is to make it easier to move your code around within the hierarchy. If you don't need
 * this then optimise the hell out of it.
 */
            if ($this->local->base() != '')
            { # we are in at least one sub-directory
                $bsplit = array_filter(explode('/', $this->local->base()));
                foreach (range(1, count($bsplit)) as $c)
                {
                    array_shift($req); # pop off the directory name...
                }
            }
            if (!empty($req))
            { # there was something after the domain name so split it into action and rest...
                $this->reqaction = strtolower(array_shift($req));
                $this->reqrest = empty($req) ? array('') : array_values($req);
            }
        }
    }
?>

<?php
/**
 * A class to handle the /login, /logout, /register, /forgot and /resend actions. There is no constructor.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2014 Newcastle University
 *
 */
    class Userlogin extends Siteaction
    {
/**
 * Find a user based on either a login or an email address
 *
 * @param string	$eorl
 *
 * @return object	The user or NULL
 */
	private function eorl($lg)
	{
	    return R::findOne('user', (filter_var($lg, FILTER_VALIDATE_EMAIL) !== FALSE ? 'email' : 'login').'=?', array($lg));
	}
/**
 * Make a confirmation code and store it in the database
 *
 * @param object	$bn	A User bean
 *
 * @return string
 */
	private function makecode($bn, $kind)
	{
	    R::trashAll(R::find('confirm', 'user_id=?', array($bn->getID())));
	    $code = hash('sha256', $bn->getID.$bn->email.$bn->login.uniqid());
	    $conf = R::dispense('confirm');
	    $conf->code = $code;
	    $conf->issued = R::isodatetime();
	    $conf->kind = $kind;
	    $conf->user = $bn;
	    R::store($conf);
	    return $code;
	}
/**
 * Mail a confirmation code
 *
 * @param object	$bn	A User bean
 *
 * @return string
 */
	private function sendconfirm($bn)
	{
	    $code = $this->makecode($bn, 'C');
	    mail($bn->email, 'Please confirm your email address for '.Config::SITENAME,
		"Please use this link to confirm your email address\n\n\n".
		Config::SITEURL.'/confirm/'.$code."\n\n\nThank you,\n\n The ".Config::SITENAME." Team\n\n",
		'From: '.Config::SITENOREPLY
	    );
	}
/**
 * Mail a password reset code
 *
 * @param object	$bn	A User bean
 *
 * @return string
 */
	private function sendreset($bn)
	{
	    $code = $this->makecode($bn, 'P');
	    mail($bn->email, 'Reset your '.Config::SITENAME.' password',
		"Please use this link to reset your password\n\n\n".
		Config::SITEURL.'/forgot/'.$code."\n\n\nThank you,\n\n The ".Config::SITENAME." Team\n\n",
		'From: '.Config::SITENOREPLY
	    );
	}
/**
 * Handle a logout
 *
 * Clear all the session material if any and then divert to the /login page
 *
 * Code taken directly from the PHP session_destroy manual page
 *
 * @link	http://php.net/manual/en/function.session-destroy.php
 *
 * @param object	$context	The context object for the site
 */
	public function logout($context, $local)
	{
	    $_SESSION = array(); # Unset all the session variables.

	    # If it's desired to kill the session, also delete the session cookie.
	    # Note: This will destroy the session, and not just the session data!
	    if (ini_get('session.use_cookies'))
	    {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
		    $params["path"], $params["domain"],
		    $params["secure"], $params["httponly"]
		);
	    }
	    if (session_status() == PHP_SESSION_ACTIVE)
	    { # no session started yet
	        session_destroy(); # Finally, destroy the -session.
            }
	    $context->divert('/');
	}
/**
 * Handle a login
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function login($context, $local)
	{
	    if ($context->hasuser())
	    { # already logged in
		$local->addval('message', 'Please log out before trying to login');
	    }
	    else
	    {
		if (($lg = $context->postpar('login', '')) !== '')
		{
		    $pw = $context->postpar('password', '');
		    $local->addval('logerr', 'Please try again');
		    if ($pw !== '')
		    {
			$user = $this->eorl($lg);
			if (is_object($user) && $user->pwok($pw) && $user->confirm)
			{
			    if (session_status() != PHP_SESSION_ACTIVE)
			    { # no session started yet
				session_start();
			    }
			    $_SESSION['user'] = $user;
			    $context->divert('/'); # success - divert to home page
			}
		    }
		    $local->addval('message', 'Please try again.');
		}
	    }
	    return 'login.twig';
	}
/**
 * handle a registration
 *
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function register($context, $local)
	{
	    $login = $context->postpar('login', '');
	    if ($login !== '')
	    {
                $errmess = array();
		$x = R::findOne('user', 'login=?', array($login));
		if (!is_object($x))
		{
		    $pw = $context->mustpostpar('password');
		    $rpw = $context->mustpostpar('repeat');
		    $email = $context->mustpostpar('email');
		    if ($pw != $rpw)
		    {
			$errmess[] = 'The passwords do not match';
		    }
		    if (preg_match('/[^a-z0-9]/i', $login))
		    {
			$errmess[] = 'Your username can only contain letters and numbers';
		    }
		    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		    {
			$errmess[] = 'Please provide a valid email address';
		    }
		    else
		    {
			$x = R::dispense('user');
			$x->login = $login;
			$x->email = $email;
			$x->confirm = 0;
                        $x->active = 1;
			$x->joined = R::isodatetime();
			R::store($x);
			$x->setpw($pw);
			$this->sendconfirm($x);
			$local->addval('warnmessage', 'A confirmation link has been sent to your email address.');
			return 'index.twig';
		    }
		}
		else
		{
		    $errmess[] = 'That user name is already in use';
		}
                if (!empty($errmess))
                {
                    $local->addval('errmessage', $errmess);
                }
	    }
	    return 'register.twig';
	}
/**
 * Handle things to do with email address confirmation
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function confirm($context, $local)
	{
	    if ($context->hasuser())
	    { # logged in, so this stupid....
		$context->divert('/');
	    }
	    $tpl = 'index.twig';
	    $rest = $context->rest();
	    if ($rest[0] == '' || $rest[0] == 'resend')
	    { # asking for resend
		$lg = $context->postpar('eorl', '');
		if ($lg == '')
		{ # show the form
		    $tpl = 'resend.twig';
		}
		else
		{ # handle the form
		    $user = $this->eorl($lg);
		    if (!is_object($user))
		    {
			$local->addval('errmessage', array('Sorry, there is no user with that name or email address.'));
		    }
		    elseif ($user->confirm)
		    {
			$local->addval('warnmessage', 'Your email address has already been confirmed.');
		    }
		    else
		    {
			$this->sendconfirm($user);
			$local->addval('message', 'A new confirmation link has been sent to your email address.');
		    }
		}
	    }
	    else
	    { # confirming the email
		$x = R::findOne('confirm', 'code=? and kind=?', array($rest[0], 'C'));
		if (is_object($x))
		{
		    $interval = (new DateTime(R::isodatetime()))->diff(new DateTime($x->issued));
		    if ($interval->days <= 3)
		    {
			$x->user->doconfirm();
			R::trash($x);
			$local->addval('message', 'Thank you for confirming your email address. You can now login.');
		    }
		    else
		    {
			$local->addval('errmessage', array('Sorry, that code has expired!'));
		    }
		}
	    }
	    return $tpl;
	}
/**
 * Handle things to do with password reset
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function forgot($context, $local)
	{
	    if ($context->hasuser())
	    { # logged in, so this stupid....
		$context->divert('/');
	    }
	    $tpl = 'index.twig';
	    $rest = $context->rest();
	    if ($rest[0] == '')
	    {
		$lg = $context->postpar('eorl', '');
		$tpl = 'reset.twig';
		if ($lg != '')
		{
		    $user = $this->eorl($lg);
		    if (is_object($user))
		    {
			$this->sendreset($user);
			$local->addval('message', 'A password reset link has been sent to your email address.');
			$tpl = 'index.twig';
		    }
		    else
		    {
			$local->addval('errmessage', array('Sorry, there is no user with that name or email address.'));
			$tpl = 'reset.twig';
		    }
		}
	    }
	    elseif ($rest[0] == 'reset')
	    {
		$tpl = 'pwreset.twig';
		$user = $context->load('user', $context->mustpostpar('uid'));
		$code = $context->mustpostpar('code');
		$xc = R::findOne('confirm', 'code=? and kind=?', array($code, 'P'));
		if (is_object($xc) && $xc->user_id == $user->getID())
		{
		    $interval = (new DateTime(R::isodatetime()))->diff(new DateTime($xc->issued));
		    if ($interval->days <= 1)
		    {
			$pw = $context->mustpostpar('password');
			$rpw = $context->mustpostpar('repeat');
			if ($pw == $rpw)
			{
			    $xc->user->setpw($pw);
			    R::trash($xc);
			    $local->addval('message', 'You have reset your password. You can now login.');
			    $tpl = 'index.twig';
			}
			else
			{
			    $local->addval('errmessage', array('Sorry, the passwords do not match!'));
			}
		    }
		    else
		    {
			$local->addval('errmessage', array('Sorry, that code has expired!'));
		    }

		}
		else
		{
		    $context->divert('/');
		}
	    }
	    else
	    {
		$x = R::findOne('confirm', 'code=? and kind=?', array($rest[0], 'P'));
		if (is_object($x))
		{
		    $interval = (new DateTime(R::isodatetime()))->diff(new DateTime($x->issued));
		    if ($interval->days <= 1)
		    {
			$local->addval('pwuser', $x->user);
			$local->addval('code', $x->code);
			$tpl = 'pwreset.twig';
		    }
		    else
		    {
			$local->addval('errmessage', array('Sorry, that code has expired!'));
		    }
		}
	    }
	    return $tpl;
	}
/**
 * Handle /login /logout /register /forgot /confirm
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function handle($context, $local)
	{
	    $action = $context->action();
	    return $this->$action($context, $local);
	}
    }
?>

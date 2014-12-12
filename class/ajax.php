<?php
/**
 * Class for handling AJAX calls invoked from ajax.php. You could integrate the
 * AJAX handling calls into the normal index.php RESTful route, but sometimes
 * keeping them separate is a good thing to do.
 *
 * It assumes that ajax calls are made to {{base}}/ajax.php via a POST and that
 * they have at least a parameter called 'op' that defines what is to be done.
 *
 * Of course, this is entirely arbitrary and you can do whatever you want!
 *
 */
    class Ajax
    {
        public function handle($context, $local)
        {
            $lg = $context->getpar('login', '');
            if ($lg != '')
            {
                $x = R::findOne('user', 'login=?', array($lg));
                if (is_object($x))
                {
                    return (new Web)->notfound(); // error if it exists....
                }
            }
            else
            {
                switch ($context->mustpostpar('op'))
                {  
                    
                case 'delentry':
                    R::trash($context->load('log', $context->mustpostpar('id')));
                    break;
                    /*     
 * These ops are part of the admin functions of the framework.
 *
 * Add your ajax operations above here.
 *
 * Change them at your own risk.
 */
                case 'ucheck':
                    $x = R::findOne('user', 'login=?', array($context->mustpostpar('login')));
                    if (is_object($x))
                    {
                	return (new Web)->noaccess(); // error if it exists....
                    }
                    break;

                case 'adduser':
                    if (!$context->hasadmin())
                    {
                	(new Web)->noaccess();
                    }
                    $u = R::dispense('user');
                    $u->login = $context->mustpostpar('login');
                    $u->email = $context->mustpostpar('email');
                    $u->active = 1;
                    $u->confirm = 1;
                    $u->joined = R::isodatetime();
                    R::store($u);
                    $u->setpassword($context->mustpostpar('password'));
                    if ($context->postpar('admin', 0) == 1)
                    {
                        $u->addrole('Site', 'Admin', '', R::isodatetime());
                    }
                    if ($context->postpar('devel', 0) == 1)
                    {
                        $u->addrole('Site', 'Developer', '', R::isodatetime());
                    }
                    echo $u->getID();
                    break;

                case 'addpage':
                    if (!$context->hasadmin())
                    {
                	(new Web)->noaccess();
                    }
                    $p = R::dispense('page');
                    $p->name = $context->mustpostpar('name');
                    $p->kind = $context->mustpostpar('kind');
                    $p->source = $context->mustpostpar('source');
                    $p->active = $context->mustpostpar('active');
                    $p->admin = $context->mustpostpar('admin');
                    R::store($p);
                    echo $p->getID();
                    break;

                case 'delbean':
                    if (!$context->hasadmin())
                    {
                	(new Web)->noaccess();
                    }
                    R::trash($context->load($context->mustpostpar('bean'), $context->mustpostpar('id')));
                    break;

                case 'deluser':
                    if (!$context->hasadmin())
                    {
                	(new Web)->noaccess();
                    }
                    R::trash($context->load('user', $context->mustpostpar('id')));
                    break;

                case 'toggle':
                    if (!$context->hasadmin())
                    {
                	(new Web)->noaccess();
                    }
                    $type = $context->mustpostpar('bean');
                    $field = $context->mustpostpar('field');

                    $bn = $context->load($type, $context->mustpostpar('id'));
                    if ($type == 'user' && ctype_upper($field[0]))
                    { # not simple toggling...
                        if (is_object($bn->hasrole('Site', $field)))
                	{
                	    $bn->delrole('Site', $field);
                	}
                	else
                	{
                	    $bn->addrole('Site', $field, '', R::isodatetime());
                	}
                    }
                    else
                    {
                        $bn->$field = $bn->$field == 1 ? 0 : 1;
                	R::store($bn);
                    }
                    break;
/*
 * End of framework provided operations
 */
                default:
                    (new Web)->bad();
                }
            }
            exit;
        }
    }
?>

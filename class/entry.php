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
            switch($context->action())
            {
            case 'newentry':
                return $this->newentry($context, $local);
                break;
            case 'editentry':
                return $this->editentry($context,$local);
                break;
            default:
                return 'entry.twig';
                break;
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
            $l->body = $context->postpar('body','NULL');
            $l->created = R::isoDateTime();
            $l->lastedit = R::isoDateTime();
            
            $f = $_FILES["attachment"]["name"];        
            if($f != NULL)
            {
                $str = $this->fileupload($context,$local);
                if ($str != '-1')
                {
                  $l->attachment = $str;     
                }
                else
                {
                    return;
                }
            }
            R::store($l);
            $user = $context->user();
            $user->xownLog[] = $l;
            R::store($user);          
            $context->divert('/logs');
        }
        
        public function editentry($context,$local)
        {
            $l = R::load('log',$context->rest()[0]);
            $l->title = $context->postpar('title','NULL');
            $l->body = $context->postpar('body','NULL');   
            $local->addval('message', 'Log Entry Updated!');
            $l->lastedit = R::isoDateTime();    
            R::store($l);
            $context->divert('/logs');
        }
        
        public function fileupload($context,$local)
        {
            $target_dir = "assets/uploads";
            $target_file = $target_dir . '/'. $_FILES["attachment"]["name"];
            $uploadOk = 0;
            $format = pathinfo($target_file,PATHINFO_EXTENSION);
            
            if($format!= "jpg" && $format != "zip" && $format != "doc" && $format != "pdf" )
            {
                $local->addval('errmessage', 'Sorry, only JPG, DOC, ZIP & PDF files can be uploaded.');
                return -1;
            }
            if (file_exists($target_file)) {
                $local->addval('errmessage', "Sorry, file already exists.");
                return -1;
            }
            if ($_FILES["attachment"]["size"] > 10000000) 
            {
                $local->addval('Sorry, your file is too large, limit is 10MB.');
                return -1;
            }
            if(!file_exists($target_dir))
            {
                mkdir($target_dir, 0777, true);
            }
            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file))
            {
                return $target_file;
            }
            else
            {
                $local->addval('errmessage', "Sorry, something went wrong.");
                return -1;
            }
        }    
    }
?>

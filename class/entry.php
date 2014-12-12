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
                return $this->delentry($context,local);
            default:
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
            
            $f = $context->postpar('attachment','No File');
            if($f != 'NULL')
            {
                $target_dir = "assets/uploads/";
                $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
                $uploadOk = 1;
                $format = pathinfo($target_file,PATHINFO_EXTENSION);
                if($format!= "jpg" && $format != "zip" && $format != "doc" && $format != "pdf" )
                {
                    echo "Sorry, only JPG, DOC, ZIP & PDF files are allowed.";
                    $uploadOk = 0;
                }
                $str = $target_dir + $target_file;
                $l->attachment =$str;
            }

            R::store($l);
            $user = $context->user();
            $user->xownLog[] = $l;
            R::store($user);
            $local->addval('message', 'Log Entry Added!');
            $context->divert('/logs');
        }
        public function editentry($context,$local)
        {
            $l = R::load('log', $context->mustpostpar('id'));
            
            $l->lastedit = R::isoDateTime();
            $l->title = $context->postpar('title','NULL');
            $l->body = $context-> postpar('body','NULL');
            $local->addval('message', 'Log Entry Updated!');
            $context->divert('/logs');
        }
        
        public function fileupload($context,$local)
        {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            $uploadOk = 1;
            $format = pathinfo($target_file,PATHINFO_EXTENSION);
            if($format!= "jpg" && $format != "zip" && $format != "doc" && $format != "pdf" )
            {
                echo "Sorry, only JPG, DOC, ZIP & PDF files are allowed.";
                $uploadOk = 0;
            }
        }
        
    }
?>

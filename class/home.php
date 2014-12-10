<?php
    class Home
    {
        public function handle($context, $Local)
        {
            if (!$context->hasuser())
            {
                $context->divert('/login');
            }
            return 'index.twig';
        }
    }
?>

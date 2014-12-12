<?php
/**
 * A class that contains code to modify log entr
 *
 *@Author Jamie Morris
 *
 */
    class printer extends Siteaction
    {
/**
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 */
        public function handle($context, $local)
        {
            $u = R::load('user', $context->user()->id);
            $logs = $u->ownLog;
            
            $pdf = new PDF();
            $pdf->SetTitle('Printout of logs for: '.$u->login);
            foreach($logs as $l){
                $pdf->AliasNbPages();
                $pdf->AddPage();
                $pdf->SetFont('Arial','',16);
                $pdf->MultiCell(0,10,strip_tags($l->title));
                $pdf->SetFont('Arial','',12);
                $pdf->MultiCell(0,10,strip_tags($l->body));
                $pdf->SetFont('Times','',10);
                $pdf->MultiCell(0,10,'Created on: '.strip_tags($l->created).'  Last Edit; '.($l->lastedit));
            }
            //for($i=1;$i<=40;$i++)    

            $pdf->Output();
        }
        
    }
?>
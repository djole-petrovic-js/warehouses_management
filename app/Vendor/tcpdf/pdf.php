<?php
require('tcpdf.php');

class PDF extends TCPDF
{
    var $xheadertext  = 'PDF created by mikroERP'; 
    var $xheadercolor = array(0,0,200); 
    var $xfootertext  = 'Copyright © %d MikroElektronika. All rights reserved.'; 
    var $xfooterfont  = PDF_FONT_NAME_MAIN; 
    var $xfooterfontsize = 8; 
    var $domestic = false;
    var $terms = false;


    /** 
    * Overwrites the default header 
    * set the text in the view using 
    *    $fpdf->xheadertext = 'YOUR ORGANIZATION'; 
    * set the fill color in the view using 
    *    $fpdf->xheadercolor = array(0,0,100); (r, g, b) 
    * set the font in the view using 
    *    $fpdf->setHeaderFont(array('YourFont','',fontsize)); 
    */ 
    function Header() 
    { 
    	/*
        list($r, $b, $g) = $this->xheadercolor; 
        $this->setY(10); // shouldn't be needed due to page margin, but helas, otherwise it's at the page top 
        $this->SetFillColor($r, $b, $g); 
        $this->SetTextColor(0 , 0, 0); 
        $this->Cell(0,20, '', 0,1,'C', 1); 
        $this->Text(15,26,$this->xheadertext ); 
        */
        if(!$this->domestic){
            $this->ImageSVG(WWW_ROOT.'img'.DS.'company'.DS.'mikroe_logo.svg',6,4);
            $this->SetXY(106, 6);
            $this->SetFont('freesans','R',7);
            $this->Cell(30,5,'MIKROELEKTRONIKA Batajnički drum 23, 11000 Belgrade, Serbia');
            $this->SetX(106);
            $this->Cell(30,12,'VAT: SR105917343 Registration No. 20490918');
            $this->SetXY(106, 6);
            $this->Cell(30,19,'Phone: + 381 11 36 28 830 Fax: + 381 11 63 09 644');
            $this->SetXY(106, 6);
            $this->Cell(30,26,'E-mail: office@mikroe.com');
            $this->SetXY(106, 6);
            $this->Cell(30,33,'http://www.mikroe.com/');    
            $this->SetY(40);
        }else{
            $this->ImageSVG(WWW_ROOT.'img'.DS.'company'.DS.'mikroe_logo.svg',6,4);
            $this->SetXY(106, 6);
            $this->SetFont('freesans','R',7);
            $this->Cell(30,5,'MIKROELEKTRONIKA Batajnički drum 23, 11186 Zemun, Beograd, Srbija');
            $this->SetX(106);
            $this->Cell(30,12,'Matični broj: 20490918, PIB: 105917343, Šifra Delatnosti: 2620');
            $this->SetXY(106, 6);
            //$this->Cell(30,19,'Žiro račun: 295-0000001241105-45, EPPDV: 460202086');
            $this->Cell(30,19,'Tekući račun: 265-1630310005061-64, EPPDV: 460202086');
            $this->SetXY(106, 6);
            $this->Cell(30,26,'Telefon: 011/78 57 600, Faks: 011/63 09 644');
            $this->SetXY(106, 6);
            $this->Cell(30,33,'E-mail: office@mikroe.com, Websajt: http://www.mikroe.com/');
            $this->SetY(40);
        }
    } 

    /** 
    * Overwrites the default footer 
    * set the text in the view using 
    * $fpdf->xfootertext = 'Copyright Â© %d YOUR ORGANIZATION. All rights reserved.'; 
    */ 
    function Footer() 
    { 
        $year = date('Y'); 
        
        if($this->terms) $this->xfootertext  = 'You can read our terms and conditions at this address: www.mikroe.com/legal/terms';
        if($this->domestic) $this->xfootertext  = 'Copyright © %d. MikroElektronika d.o.o. zadržava sva autorska prava.';

        $footertext = sprintf($this->xfootertext, $year); 
        $this->SetY(-20); 
        $this->SetTextColor(0, 0, 0); 
        $this->SetFont($this->xfooterfont,'',$this->xfooterfontsize);        
        $this->Cell(0,8, $footertext,'T',0,'L'); 

        if(!$this->domestic){
            // Page number
            $this->Cell(0,8, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(),'T',1,'R');
        }else{
            // Page number
            $this->Cell(0,8, 'Strana '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(),'T',1,'R');
        }
    }//~!

    function checkPB($h=0,$y='',$addpage=true){
        return $this->checkPageBreak($h, $y, $addpage);
    }//~!    
} ?>
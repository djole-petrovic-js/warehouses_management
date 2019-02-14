<?php
require('tcpdf.php');

class PDF extends TCPDF
{

    function checkPB($h=0,$y='',$addpage=true){
        return $this->checkPageBreak($h, $y, $addpage);
    }//~!    
} ?>
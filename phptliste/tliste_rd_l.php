<?php
/*
 ******************************************************************************
 *  Fichier: tliste_rd_l.php
 *  Author : R. BERTHOU
 *  E-Mail : rbl@berthou.com
 *  URL    : http://www.berthou.com  http://www.javaside.com
 ******************************************************************************
 *  Ver  * Author     *  DATE      * Description
 * ------*------------*-MM-DD-YYYY-*-------------------------------------------
 *  1.00 * R.BERTHOU  * 18/01/2008 * Create 
 * ******************************************************************************
 */
class rd_l {
	/** title of item */
	var $sDesc = "" ; // title of item
	
	var	$sURL	     ;	// dest URL
	var	$sTarg	= "" ;	// target windows
	var $sHelp       ;	// Help Message (bullet)
	var $sVal   = "" ;	// sVal for (Date test)
	
	var	$iN	= 0      ;	// Level
	var	$iE	= 10	 ;	// Image Number
	var	$iS	= 0 	 ;	// Open or Close
	var	$i3	= 0		 ;	//
	var	$i4	= 0		 ;	//
	
	var $iKey = -1   ;

	function open_close( ) 
	{
      if ($this->iS === 1)	{ // Open => Close
          $this->iS = 0 ;
      } else {
	        if ($this->iS === 0)	{ // Close => Open
	              $this->iS = 1 ;
	        } 
			}
	}
	function open( ) 
	{
        if ($this->iS === 0)	{ // Close => Open
              $this->iS = 1 ;
        } 
	}
	function close( ) 
	{
        if ($this->iS === 1)	{ // Open => Close
              $this->iS = 0 ;
        } 
	}

	
	function setLine($sLine, $m_t, $cSep = TLISTE_CSEP  ) 
	{
	    $sLine=str_replace("\t"," ",trim($sLine));
	    $sLine=trim($sLine);
	    $xA=explode($cSep,$sLine);
	    $_j=count($xA);

			if ($_j === 0) return 0 ;

			$this->sURL  = "" 		   	; // URL  
			$this->sTarg = $m_t			; // Target  
			$this->sHelp = ""				; // Help Bullet
			$this->iS = -1 ;
  		
			$_k = 0 ;
			$_i = 0 ;

			while($_i < $_j) {

        	if ($_i === 0) $this->iN    = intval($xA[$_i]) ; 
        	if ($_i === 1) $this->sDesc = trim($xA[$_i])   ;  
        	if ($_i === 2) $this->iE    = intval($xA[$_i]) ;  
        	
        	if ($_i > 2) {
	        	if (is_numeric($xA[$_i])) {
	        		$this->i3 = intval($xA[$_i]) ;  
	        	} else {
	        		if ( strpos(trim($xA[$_i]),'?') === 0 ) {
	        			$this->sHelp = trim(str_replace("?"," ",$xA[$_i]));
	        		} else {
		        		if ($_k === 0) $this->sURL  = trim($xA[$_i]);
	    	    		if ($_k === 1) $this->sTarg = trim($xA[$_i]);
	    	    		if ($_k === 2) $this->sVal  = trim($xA[$_i]);
	    	    		$_k++ ;
	        		}
	        	} 
	        }
        	$_i++ ;
        }

	      return $_j ;
	}

	function draw($iNo, &$tli) 
	{echo "<nobr>";
	//	global $iOpen, $imagePath, $lnode, $url, $style ;
		$s = "" ;

		if ($this->iN < $tli->iOpen) $tli->iOpen = $this->iN ;
		
		if ($this->iN === $tli->iOpen) 
		{
				if ($this->iS === 1)		// Niveau Ouvert
				$tli->iOpen++ ;
		}

		if ($tli->iOpen >= $this->iN) 
		{
			if ( $this->iN >= 0 ) 
		 	{
				$x_coord = $this->i4 ;
				$y_coord = $this->iN ;
				$xx = 0 ;
				$ibar = 2 ; // image bar standard pour item
                    // +1 si fin de branche
                    // +2 si node

				if ( $this->iS === 0 )	$ibar = 6 ;
				if ( $this->iS === 1 )	$ibar = 4 ;

        /**
         * fin de branche bar +1 (3 , 5, 7)
         */
				if ( ($x_coord & 1) === 1 ) 
				{
					$ibar++ ;
					$x_coord-- ;
				}

				$x_coord = $x_coord - (1 << $y_coord) ;
				$y_coord-- ;
				

				while ($y_coord > 0) 
				{
				
					if ($x_coord >= (1 << $y_coord)) 
					{
						$xx = 1 ;
						$x_coord = $x_coord - (1 << $y_coord) ;
					}
	        else 
	        {
	         	$xx = 99 ;
	        }
	
					$y_coord-- ;
	        $s   =  "<img  src=\"". $tli->imagePath . $xx . "n.gif\" width=\"18\" align=\"top\" border=\"0\">" .$s ;
				}

				if ( $this->iN > 0 ) 
				{
					$s = $s. "<img src=\"" . $tli->imagePath . $ibar . "n.gif\" width=\"18\" align=\"top\" border=\"0\">" ;
				}

				$ibar = $this->iE ;

				if ($this->iS > -1) 
				{
					$l = $tli->lnode ;
					if ($this->iS === 0) 
					{
						$l = $tli->lnode | (1 << $iNo) ;
					}
					else 
					{
						$l = $tli->lnode & ~(1 << $iNo) ;
					}
					$s = $s. "<a class=\"jq\" href=\"". $tli->url . $tli->_id."node=" . $l ."\">" ;
					$ibar += $this->iS ;
				}
				$s = $s . "<img  src=\""  . $tli->imagePath . $ibar . ".gif\" align=\"top\" border=\"0\">" ;

				if ($this->iS > -1) 
				{
					$s = $s . "</a>" ;
				}

				$s = $s."&nbsp;" ;
	
				if ($this->sURL === "") 
				{
       	  $s = $s . "<span class=\"". $tli->style ."\">".$this->sDesc."</span>" ; /*OJO: aqui escribe la sección*/
				}
				else
				{
        	$s = $s."<a class=\"section_name\"";/* href=\""  ; //OJO: ===== NO LINK ======
					if ($this->sTarg === "_reload") 
					{
							$tt = $tli->_id."file=".$tli->sFile ;
						  $tmp = str_replace("&".$tt,"",$tli->url) ;
							$tmp = str_replace($tt."&","",$tmp) ;
							$tmp = str_replace($tt,"",$tmp) ;
							$s = $s . $tmp . $tli->_id."file=".$this->sURL ."\"" ;
          }
          else 
          {
          	$s = $s. $this->sURL ."\" " ;
            	if ($this->sTarg != "") 
            	{
              	$s = $s . "target=\"" . $this->sTarg . "\" " ;
              }
          }*/ //OJO: ===== NO LINK ======
          if ($this->sHelp != "") 
          {
              $s = $s ." title=\"" . $this->sHelp . "\" " ;
          }
          $s = $s . " class=\"" . $tli->style . "\">" . $this->sDesc . "</a>" ; /*OJO: aqui escribe el nombre del modelo*/
        }

       $s = $s."</nobr><br/>" ;
		 }
	 }
	 echo $s."\r\n" ;
	}
}
?>

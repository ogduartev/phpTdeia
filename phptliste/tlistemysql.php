<?php
require_once("tliste_rd_l.php"); 
/*
 ******************************************************************************
 *  Fichier: tliste.php
 *  Author : R. BERTHOU
 *  E-Mail : rbl@berthou.com
 *  URL    : http://www.javaside.com  http://www.berthou.com
 ******************************************************************************
 *  Ver  * Author     *  DATE      * Description
 * ------*------------*-MM-DD-YYYY-*-------------------------------------------
 *  1.00 * R.BERTHOU  * 18/01/2008 * Create 
 * ******************************************************************************
 */
 // ----- Constantes for tListe
  define("TLISTE_CSEP", "�");

class tlistemysql {
  var $_id   = "" ;
  var $style ;
  var $imagePath ;
  var $sFile     ;
  var $sTarget   ;
  var $node      ;
  
  var $url       ;
  var $param     ;
  
// Tableau d'elements
  var $arElt  ;
  var $nbElt = 0 ;  
   
  var $lnode = 0 ;
  var $iOpen = 0 ;
  
  var $condition="";

  function tlistemysql($link,$idd = "", $sf = "", $cc = "p1",$sqldata,$condition="") 
  {  
    $this->condition=$condition;
    $this->arElt = array() ;
    $this->nbElt = 0 ;
    
    $this->_id = $idd ;
    
    $this->style = $sqldata['class'] ;
    
    $this->getParameter($sf) ;

    $this->readMysql($link,$sqldata,0,0);

    $this->initBar();
    $this->restoreNode();
    $this->saveNode();
  }  
  
  function getParameter( $sf ) 
  {  
    $this->param = "" ;
    $this->url = $_SERVER['REQUEST_URI'] ;

    if (isset($_REQUEST[$this->_id.'img'])) 
    {
      $this->imagePath=$_REQUEST[$this->_id.'img'];
      $this->param .= "&".$this->_id."img=".$this->imagePath ;
    }
    else 
    {
      $this->imagePath = "./phptliste/img/ot";
    }
  
  
    if (isset($_REQUEST[$this->_id.'file'])) 
    {
      $this->sFile=$_REQUEST[$this->_id.'file'];
      $this->param .= "&".$this->_id."file=".$this->sFile ;
    }
    else 
    {
      if ($sf === "") {
        $this->sFile="./phptliste/txt/tliste.txt";
      } 
      else
      {
        $this->sFile=$sf;
      }
    }
  
    // recherche de la target de default
    if (isset($_REQUEST[$this->_id.'target'])) 
    {
      $this->sTarget=$_REQUEST[$this->_id.'target'];
      $this->param .= "&".$this->_id."target=".$this->sTarget ;
    }
    else 
    {
      $this->sTarget="_blank";
    }
  
    // recherche de l etat des noeuds
    if (isset($_REQUEST[$this->_id.'node'])) 
    {
      $this->node=$_REQUEST[$this->_id.'node'];
      $tmp=$this->_id."node=".$this->node ;

      $this->url = str_replace("&".$tmp,"",$this->url) ;
      $this->url = str_replace($tmp."&","",$this->url) ;
      $this->url = str_replace($tmp,"",$this->url) ;
      
    }
    else 
    {
      $this->node="";
    }
    if ( !strpos($this->url,'?')  ) {
        $this->url = $this->url . "?" ;
    } else {
        $this->url = $this->url . "&" ;
    }
  }

  function readMysql($link,$sqldata,$id,$level)
  {
    $sql="SELECT * FROM ".$sqldata['table1']. " WHERE ".$sqldata['up_id1'];
    if($id==0)
    {
      $sql.=" IS NULL";
    }else
    {
      $sql.="=".$id;
    }
    $sql.=$this->condition;
    $sql.=" ORDER BY ".$sqldata['name1'];
    $result=mysqli_query($link,$sql) or die(mysqli_error($link).": ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $elt=new rd_l();
        $elt->sDesc=$linea[$sqldata['name1']];
        $elt->sURL="";
        if(strlen($sqldata['link_id1'])>1)
        {
          $elt->sURL=$linea[$sqldata['url']]."?".$sqldata['link_id1']."=".$linea['id'];
        }
        $elt->sHelp=$linea[$sqldata['help1']];
        $elt->sTarg="";
        $elt->iN=$level;
        $elt->iE= '0n';        // Image Number
        $elt->iS= 1;        // Open or Close
        $this->arElt[$this->nbElt] = $elt ;
        $this->nbElt++ ;
        $this->readMysql($link,$sqldata,$linea['id'],$level+1);
     }
    }
    
    $sql="SELECT * FROM ".$sqldata['table2']. " WHERE ".$sqldata['up_id2'];
    if($id==0)
    {
      $sql.=" IS NULL";
    }else
    {
      $sql.="=".$id;
    }
    $sql.=$this->condition;
    $sql.=" ORDER BY ".$sqldata['name2'];
    $result=mysqli_query($link,$sql) or die(mysqli_error($link).": ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $elt=new rd_l();
        $elt->sDesc=$linea[$sqldata['name2']];
//        $elt->sURL=$linea[$sqldata['url']]."?modelid=".$linea['id'];
        $elt->sURL=$linea[$sqldata['url']]."?".$sqldata['link_id2']."=".$linea['id'];
        $elt->sHelp=$linea[$sqldata['help2']];
        $elt->sTarg="";
        $elt->iN=$level;
        $elt->iE= 90;        // Image Number
        $elt->iS= 1;        // Open or Close
        $this->arElt[$this->nbElt] = $elt ;
        $this->nbElt++ ;
     }
    }
    return;
  }
   
  function display( ) 
  {
    // global $arElt, $nbElt ;
  
    $iNo = 0 ;
    for ($i=0; $i<count($this->arElt); $i=$i+1)
    {
      $elt = $this->arElt[$i] ;
      $elt->draw($iNo, $this);
      if (($elt->iS === 0) || ($elt->iS === 1)) $iNo++ ;
    }
  }

  function addElt($xlt) 
  {
//    global $arElt, $nbElt, $sTarget ;
    
    $elt = new rd_l() ;
    $elt->setLine( $xlt, $this->sTarget ) ;
  
    if ($elt->iN > -1 && $elt->iN < 90 ) 
    {
      $this->arElt[$this->nbElt] = $elt ;
      $this->nbElt++ ;
    }
  }

  function initBar()
  {
  //  global $arElt  ;
    
    $i=count($this->arElt)-1 ;
    $i4Prev=0;
    $iNPrev=0;
    $iNFin=99;
  
    while ($i >= 0 )
    {
      if ($iNPrev === 0) $iNFin = 99 ;
          
      $elt = $this->arElt[ $i ] ;
      $i-- ;
      
      if ( $elt->iN > $iNPrev ) 
      {  // Fin de branche
        $elt->i4 = 1 + $i4Prev + (1 << $elt->iN ) ;
        $i4Prev += (1 << $elt->iN ) ;
        $iNFin = $elt->iN ;
      }
      else 
      {
        if ( $elt->iN === $iNPrev ) 
        {
          $elt->i4 = $i4Prev ;
        }
        else 
        {
          if ($iNFin != 99) 
          {    // Node
            if ($elt->iS === -1) 
            {
              $elt->iS = ( $elt->iE & 1 ) ;
              $elt->iE -= $elt->iS ;
            }
          }
          $i4Prev = ( $i4Prev - (1 << $iNPrev ) ) ;
          $elt->i4 = $i4Prev  ;
          if ( ($elt->iN != $iNFin) && ($elt->iN > 0 ) ) 
          {
            if ( ($i4Prev & (1 << $elt->iN)) != (1 << $elt->iN) ) 
            {
              $iNFin = $elt->iN ;
              $i4Prev = ( $i4Prev + (1 << ($iNPrev-1) ) ) ;
              $elt->i4 = 1 + $i4Prev;
            }
          }
        }
      }
      $iNPrev = $elt->iN ;
    }
  } 

  /**
   * restoreNode : restore nodes states
   */
  function restoreNode() 
  {
  //  global $lnode, $node, $arElt ;
  
    if (is_null($this->node) || trim($this->node) === "") return ;
          
    $this->lnode = doubleval($this->node) ;
    $iNo = 0 ;
  
    for ($i=0; $i<count($this->arElt); $i=$i+1)
    {
      $elt = $this->arElt[$i] ;
      if ( $elt->iS > -1) 
      {
        $l = $this->lnode & (1 << $iNo) ;                
        if ($l) 
          $elt->open() ;
        else
            $elt->close() ;
        $iNo++ ;
      }
    }
  }

  /**
   * saveNode  : build lnode value
   */
  function saveNode() 
  {
  //  global $lnode, $arElt ;
  
    /* for each elements */
    $iNo = 0 ;
  
    for ($i=0; $i<count($this->arElt); $i=$i+1)
    {
      $elt = $this->arElt[$i] ;
      if ( $elt->iS > -1) 
      {
        if ( $elt->iS > 0) 
        {
          $this->lnode = $this->lnode | (1 << $iNo) ;                
        }
        $iNo++ ;
      }
    }
  }
}
// ------------------------------------ 
//  End Source...
// ------------------------------------
?>

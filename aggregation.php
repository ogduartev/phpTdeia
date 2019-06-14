<?php
require_once("functions.php");
require_once("variable.php");

class aggregation
{
  var $equation;
  var $functionName;
  var $importancesID;
  var $variableID;
  var $link;
  
  function findDown($id,$table,$IDo)
  {
    $ID=$IDo;
    $sql="SELECT id FROM ".$table."s WHERE ".$table."_id='".$id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $ID[]=$linea['id'];
        $ID=$this->findDown($linea['id'],$table,$ID);
      }
    }
    return $ID; 
  }
  
  function getImportancesID($aggregation_id)
  {
    $this->functionName="";
    $sql="SELECT aggregations.id,equation,factor_id,action_id,variables.id AS VID FROM aggregations 
                        INNER JOIN aggregators ON aggregations.aggregator_id=aggregators.id
                        INNER JOIN variables ON variables.aggregator_id=aggregators.id
                        WHERE aggregations.id='".$aggregation_id."' LIMIT 1";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $this->functionName=$linea['equation'];
      $this->variableID=$linea['VID'];
      $factors=array();
      $factors[]=$linea['factor_id'];
      $factors=$this->findDown($linea['factor_id'],"factor",$factors);
      $actions=array();
      $actions[]=$linea['action_id'];
      $actions=$this->findDown($linea['action_id'],"action",$actions);
    }
    $this->importancesID=array();
    foreach($factors as $fid)
    {
      foreach($actions as $aid)
      {
        $sql="SELECT importances.id AS ID FROM importances 
                                               INNER JOIN effects ON importances.effect_id=effects.id
                                               WHERE action_id='".$aid."'
                                               AND factor_id='".$fid."'";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
          {
            $this->importancesID[]=$linea['ID'];
          }
        }
      }
    }    
  }
  
  function getImportancesFN()
  {
    $X=array();
    foreach($this->importancesID as $ID)
    {
      $FN=new fuzzy_number;
      $FN->link=$this->link;
      $FN->read("importance_cuts","importance_id",$ID);
      $X[]=$FN;
    }
    return $X;
  }
  
  function createFunction()
  {
    $numberOfArgs=count($this->importancesID);
    unset($this->equation);
    switch($this->functionName)
    {
      default:
      case "simple_average" : 
           $a0=0;
           $a=array();
           for($i=0;$i<$numberOfArgs;$i++)
           {
             $a[]=1.0/$numberOfArgs;
           }
           $this->equation=new linear_combination($a0,$a);
           break;
      case "weighted_average" : 
           $a0=0;
           $a=array();
           $suma=0;
           foreach($this->importancesID as $iID)
           {
             $sql="SELECT weight FROM factors
                                      INNER JOIN effects ON effects.factor_id=factors.id
                                      INNER JOIN importances ON importances.effect_id=effects.id
                                      WHERE importances.id='".$iID."'";
             $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
             if($result and mysqli_num_rows($result)>0)
             {
               $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
               $a[]=$linea['weight'];
               $suma+=$linea['weight'];
             }
           }
           foreach($a as $k=>$v)
           {
             $a[$k]=$v/$suma;
           }
           $this->equation=new linear_combination($a0,$a);
           break;
      case "maximum" : 
           $a=array();
           for($i=0;$i<$numberOfArgs;$i++)
           {
             $a[]=1.0;
           }
           $this->equation=new maximum($a);
           break;
      case "minimum" : 
           $a=array();
           for($i=0;$i<$numberOfArgs;$i++)
           {
             $a[]=1.0;
           }
           $this->equation=new minimum($a);
           break;
    }
  }
  
  function deleteAggregation($aggregation_id)
  {
    $sql="DELETE FROM aggregations WHERE id='".$aggregation_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
  }
  
  function updateAggregation($aggregation_id,$cuts)
  {
    $this->getImportancesID($aggregation_id);
    $numberOfArgs=count($this->importancesID);
    if($numberOfArgs==0)
    {
      $this->deleteAggregation($aggregation_id);
//      $Y=new fuzzy_number();
//      $Y->trapezoid(0,0,0,0);
    }else
    {
      $X=$this->getImportancesFN();
      $this->createFunction();
      $Y=$this->equation->fuzzy_direct($X,$cuts);
      $Y->link=$this->link;
      $Y->write("aggregation_cuts","aggregation_id",$aggregation_id);
    }
  }  
  
}




?>

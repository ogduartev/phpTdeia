<?php
require_once("input.php");

class variable
{
//  var $consistencies;
//  var $Short;
//  var $Long;
  var $link;
  var $sets=array();
    
  function consistencies($variable_id,$FN)
  {
    $consistencies=array();
    $sets=$this->variable_sets($variable_id);
    foreach($sets as $label=>$FN2)
    {
      $consistencies[$label]=$FN2->consistency($FN,11);
    }
    return $consistencies;
  }
  
  function variable_sets($variable_id)
  {
    $sets=array();
    $sql2="SELECT id,label FROM sets WHERE variable_id=".$variable_id;
    $result2=mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
    if($result2 and mysqli_num_rows($result2)>0)
    {
      while($linea2=mysqli_fetch_array($result2,MYSQLI_ASSOC))
      {
        $FN=new fuzzy_number();
        $FN->link=$this->link;
        $FN->read("cuts","set_id",$linea2['id']);
        $sets[$linea2['label']]=$FN;
      }
    }
    return $sets;
  }
    
  function variable_labels($variable_id)
  {
    $sets=array();
    $sql="SELECT id,label FROM sets WHERE variable_id=".$variable_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $sets[$linea['id']]=$linea['label'];
      }
    }
    return $sets;
  }
    
  function short($variable_id,$FN)
  {
    $max=-1.0;
    $short="";
    $consistencies=$this->consistencies($variable_id,$FN);
    foreach($consistencies as $label => $c)
    {
      if($c>=$max)
      {
        $max=$c;
        $short=$label;
      }
    }
    return $short;
  }
  
  function long($variable_id,$FN)
  {
    $consistencies=$this->consistencies($variable_id,$FN);
    krsort($consistencies);
    $long="";
    foreach($consistencies as $label => $c)
    {
      if($c>=0.66)
      {
        $long.="muy posiblemente (".number_format($c,2).") ".$label.", ";
      }elseif($c>=0.33)
      {
        $long.="posiblemente (".number_format($c,2).") ".$label.", ";
      }
    }
    $long=substr($long,0,strlen($long)-2);
    return $long;
  }

  function readDB($id)
  {
    $sql="SELECT * FROM variables WHERE id='".$id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $this->DB=mysqli_fetch_array($result,MYSQLI_ASSOC);
    }
    $this->sets=$this->variable_sets($id);
  }
  
  function createLabelInDB($variable_id,$label,$set)
  {
    $sql="INSERT INTO sets(variable_id,label) VALUES('".$variable_id."','".$label."')";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $set_id=mysqli_insert_id($this->link);
    
    $set->link=$this->link;
    $set->write("cuts","set_id",$set_id);
  }
  
  function createInDB($name,$description,$prefix,$min,$max,$agg_id=0,$eff_id=0,$sets=3,$cuts=2)
  {
    $aggStr="NULL";if($agg_id>0){$aggStr="'".$agg_id."'";}
    $effStr="NULL";if($eff_id>0){$effStr="'".$eff_id."'";}
    $this->DB['minimum']=$min;
    $this->DB['maximum']=$max;
    
    
    $sql="INSERT INTO variables(name,description,minimum,maximum,aggregator_id,effect_propierty_id)
                      VALUES('".$name."','".$description."','".$min."','".$max."',".$aggStr.",".$effStr.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $variable_id=mysqli_insert_id($this->link);
    if($sets<2){$sets=2;}
    if($cuts<2){$cuts=2;}
    
    $this->autodefine($sets,$prefix,$cuts);
    foreach($this->sets as $label=>$set)
    {
      $this->createLabelInDB($variable_id,$label,$set);
    }
  }
  
  function writeInDB()
  {
    $name=$this->DB['name'];
    $description=$this->DB['description'];
    $min=$this->DB['minimum'];
    $max=$this->DB['maximum'];
    $aggStr='NULL';
    $effStr='NULL';
    
    if($this->DB['aggregator_id']>0){$aggStr="'".$this->DB['aggregator_id']."'";}
    if($this->DB['effect_propierty_id']>0){$effStr="'".$this->DB['effect_propierty_id']."'";}
    $sql="INSERT INTO variables(name,description,minimum,maximum,aggregator_id,effect_propierty_id)
                      VALUES('".$name."','".$description."','".$min."','".$max."',".$aggStr.",".$effStr.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $variable_id=mysqli_insert_id($this->link);

    foreach($this->sets as $label=>$set)
    {
      $this->createLabelInDB($variable_id,$label,$set);
    }
    
  }
  
  function autodefine($sets,$prefix,$cuts=2)
  {
    $min=$this->DB['minimum'];
    $max=$this->DB['maximum'];
    $this->sets=array();
    if($sets<2){$sets=2;}
    if($cuts<2){$cuts=2;}
    
    $Delta=1/(2*$sets - 1)*($max-$min);
    $a=$min+0;$b=$min+0;$c=$min+$Delta;$d=$min+2*$Delta;
    $label=$prefix."1";
    $FN=new fuzzy_number();
    $FN->link=$this->link;
    $FN->trapezoid($a,$b,$c,$d);
    $this->sets[$label]=$FN;    
    for($i=2;$i<$sets;$i++)
    {
      $a=$min+(2*$i-3)*$Delta;$b=$min+(2*$i-2)*$Delta;$c=$min+(2*$i-1)*$Delta;$d=$min+(2*$i)*$Delta;
      $label=$prefix.$i;
      $FN=new fuzzy_number();
      $FN->link=$this->link;
      $FN->trapezoid($a,$b,$c,$d);
      $this->sets[$label]=$FN;    
    }

    $a=$min+(2*$sets-3)*$Delta;$b=$min+(2*$sets-2)*$Delta;$c=$max;$d=$max;
    $label=$prefix.$sets;
    $FN=new fuzzy_number();
    $FN->link=$this->link;
    $FN->trapezoid($a,$b,$c,$d);
    $this->sets[$label]=$FN;    

  }
  
  function changeLimits($min,$max)
  {
    if($max<$min){return;}
    $minOld=$this->DB['minimum'];
    $maxOld=$this->DB['maximum'];
    $this->DB['minimum']=$min;
    $this->DB['maximum']=$max;
   
    $sets=array();
    foreach($this->sets as $K=>$set)
    {
      $FN=new fuzzy_number();
      $FN->link=$this->link;
      foreach($set->alpha_cuts as $cut)
      {
        $alpha=$cut['alpha'];
        $Lold=$cut['L'];
        $Rold=$cut['R'];
        $L=$min + (($Lold-$minOld)*($max-$min)/($maxOld-$minOld));
        $R=$min + (($Rold-$minOld)*($max-$min)/($maxOld-$minOld));
        $FN->alpha_cuts[]=array("alpha"=>$alpha,"L"=>$L,"R"=>$R);
      }
      
      $sets[$K]=$FN;
    }
    $this->sets=$sets;
  }
  
  function extractRGB($color)
  {
    // format without validation #HHHHHH
    $strR=substr($color,1,2);
    $strG=substr($color,3,2);
    $strB=substr($color,4,2);
    $hR=dechex(ord($strR));
    $hG=dechex(ord($strR));
    $hB=dechex(ord($strR));
  }
  
  function color($FN,$colmin,$colmean,$colmax,$optimism=0.5,$r=1)
  {
    $x=$FN->representative_value($optimism,$r);
    $min=$this->DB['minimum'];
    $max=$this->DB['minimum'];
    $mean=($min+$max)/2.0;
    
  }

}
?>

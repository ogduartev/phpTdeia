<?php
require_once("variable.php");
require_once("fuzzy_number.php");

class input
{
  var $cases=array(0=>'crisp',
                   1=>'interval',
                   2=>'fuzzy_number',
                   3=>'label');
  var $modifiers=array(0=>'without',
                       1=>'at_least',
                       2=>'no_greater_than',
                       3=>'anything',
                       4=>'nothing');  // duplicado de fuzzy_number
  
  var $link;

  function create($variable_id,$propierty_id)
  {
    $var=new variable();
    $var->link=$this->link;
    $var->readDB($variable_id);
    
    $set_id=0;
    $description="";
    $type=3;
    $L=$var->DB['minimum'];
    $R=$var->DB['maximum'];
    $crisp=($L+$R)/2;
    $modifier=3;

    $sql="SELECT id FROM sets WHERE variable_id=".$variable_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $set_id=$linea['id'];
    }
     
    $sql="INSERT INTO inputs(propierty_id,set_id,description,type,crisp,L,R,modifier) 
                      VALUES(".$propierty_id.",".$set_id.",'".$description."',".$type.",".$crisp.",".$L.",".$R.",".$modifier.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $input_id=mysqli_insert_id($this->link);
    
    $FN=new fuzzy_number();
    $FN->link=$this->link;
    $FN->trapezoid(0,0,0,0);
    $FN->write("input_cuts","input_id",$input_id);
    
    return $input_id;
  }
                   
  function number($input_id)
  {
    $FN=new fuzzy_number();
    $FN->link=$this->link;
    $sql="SELECT * FROM inputs WHERE id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      switch($this->cases[$linea['type']])
      { 
        case 'crisp' :
                $a=$linea['crisp'];
                $FN->trapezoid($a,$a,$a,$a);
                break;
        case 'interval' :
                $a=$linea['L'];
                $b=$linea['R'];
                $FN->trapezoid($a,$a,$b,$b);
                break;
        case 'fuzzy_number' :
                $FN->read("input_cuts","input_id",$linea['id']);
                break;
        case 'label' :
                $FN->read("cuts","set_id",$linea['set_id']);
                $FN->modifier($linea['modifier'],$linea['set_id']);
                break;
      }
    }
    return $FN;
  }
  
  function resetInput($input_id,$effect_propierty_id,$min,$max)
  {
    $set_id=0;
    $sql="SELECT id FROM sets 
                    INNER JOIN variables ON variables.id=sets.variable_id
                    AND variable.effect_propierty_id='".$effect_propierty_id."' LIMIT 1";
    $sql="SELECT * FROM inputs WHERE id=".$input_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $set_id=$linea['id'];
    }

    $sql="UPDATE inputs SET crisp=(".$min." + ".$max.")/2,L=".$min.",R=".$max.",type=3,modifier=3,set_id=".$set_id." WHERE id='".$input_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $FN=new fuzzy_number();
    $FN->link=$this->link;
    $FN->trapezoid($min,$min,$max,$max);
    $FN->write("input_cuts","input_id",$input_id);
    
  }
  
  function getCrisp($input_id)
  {
    $str="";
    $sql="SELECT crisp FROM inputs WHERE id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $str=$linea['crisp'];
    }
    return $str;
  }
  
  function getL($input_id)
  {
    $str="";
    $sql="SELECT L FROM inputs WHERE id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $str=$linea['L'];
    }
    return $str;
  }
  
  function getR($input_id)
  {
    $str="";
    $sql="SELECT R FROM inputs WHERE id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $str=$linea['R'];
    }
    return $str;
  }
    
  function getset_id($input_id)
  {
    $str="";
    $sql="SELECT set_id FROM inputs WHERE id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $str=$linea['set_id'];
    }
    return $str;
  }
    
  function getmodifier($input_id)
  {
    $str="";
    $sql="SELECT modifier FROM inputs WHERE id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $str=$linea['modifier'];
    }
    return $str;
  }
    
  function gettype($input_id)
  {
    $str=0;
    $sql="SELECT type FROM inputs WHERE id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $str=$linea['type'];
    }
    return $str;
  }
    
  function gettypeStr($input_id)
  {
    $str=$this->cases[$this->gettype($input_id)];
    return $str;
  }
    
  function getFNdef($input_id)
  {
    $FN=new fuzzy_number();
    $FN->link=$this->link;
    $FN->read('input_cuts','input_id',$input_id);
    return $FN;
  }
  
  function getDescription($input_id)
  {
    $str="";
    $sql="SELECT description FROM inputs WHERE id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $str=$linea['description'];
    }
    return $str;
  }
  
  function asText($input_id,$optimism,$r)
  {
    $str="";
    $FN=new fuzzy_number();
    $FN->link=$this->link;
    $sql="SELECT inputs.*,sets.label FROM inputs 
                        INNER JOIN sets ON inputs.set_id=sets.id
                        WHERE inputs.id=".$input_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      switch($this->cases[$linea['type']])
      { 
        case 'crisp' :
                $a=number_format($linea['crisp'],3);
                $str.=$a;
                break;
        case 'interval' : 
                $a=number_format($linea['L'],3);
                $b=number_format($linea['R'],3);
                $str.="[".$a.", ".$b."]";
                break;
        case 'fuzzy_number' : 
                $FN->read("input_cuts","input_id",$linea['id']);
                $str.=number_format($FN->representative_value($optimism,$r),3);
                $str.=" / ";
                $str.=number_format($FN->ambiguity($r),3);
                break;
        case 'label' : 
                $str=$linea['label'];
                
                switch($this->modifiers[$linea['modifier']])
                {
                      default:
                      case 'without' : 
                                      break;
                      case 'at_least' : 
                                      $str=$FN->text('input_at_least')." ".$str; 
                                      break;
                      case 'no_greater_than' : 
                                      $str=$FN->text('input_no_greater_than')." ".$str; 
                                      break;
                      case 'anything' :
                                      $str=$FN->text('input_anything'); 
                                      break;
                      case 'nothing' : 
                                      $str=$FN->text('input_nothing'); 
                                      break;      
                 }
                break;
        default : // LABEL
                $str.="NN";
                break;
                
      }
    }
    return $str;
  }
}

?>

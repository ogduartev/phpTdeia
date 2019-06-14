<?php
//require_once('input.php');

class propierty
{
  var $link;
  
  function create($effect_id,$effect_propierty_id,$cuts)
  {
    $variable_id=0;
    $sql="SELECT id FROM variables WHERE effect_propierty_id='".$effect_propierty_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $variable_id=$linea['id'];
    }
    
    if($variable_id<1){return;}
    
    $sql="INSERT INTO propierties(effect_id,effect_propierty_id)
                      VALUES('".$effect_id."','".$effect_propierty_id."')";                  
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $propierty_id=mysqli_insert_id($this->link);

    $IN=new input();
    $IN->link=$this->link;
    $input_id=$IN->create($variable_id,$propierty_id);
    
    return $propierty_id;
  }
}

?>

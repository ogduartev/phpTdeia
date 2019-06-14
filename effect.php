<?php
require_once('propierty.php');
require_once('importance.php');

class effect
{
  var $link;

  function create($project_id,$factor_id,$action_id)
  {
      // crear efecto
      $name="nn";
      $description="";
      $nature=-1;
      $sql="INSERT INTO effects(project_id,factor_id,action_id,name,description,nature) 
                        VALUES(".$project_id.",".$factor_id.",".$action_id.",'".$name."','".$description."',".$nature.")";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $effect_id=mysqli_insert_id($this->link);
      
      // crear las propiedades
      $sql="SELECT id FROM effect_propierties WHERE project_id='".$project_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
        {
          $P=new propierty();
          $P->link=$this->link;
          $P->create($effect_id,$linea['id'],$cuts);
        }
      }
            
      // crear la importancia
      $IM=new importance();
      $IM->link=$this->link;
      $IM->insert_single_importance($project_id,$effect_id,2);
  }
}

?>

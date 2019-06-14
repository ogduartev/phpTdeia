<?php
require_once('block.php');
require_once('importance.php');

class project
{
  var $link;
  
  function text($str)
  {
    $B=new block();
    return $B->text($str);
  }


  function importAggregatorsCSV($project_id,$fn,$flagDelete=true)
  {
    $table="aggregators";
    if($project_id<0)
    {
      return;
    }
    if($flagDelete)
    {
      $sql="DELETE FROM ".$table." WHERE project_id='".$project_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
    $f=file($fn);
    if(!$f){return;}
    
    $f=array_slice($f,1);
    foreach ($f as $linea)
    {
      $linea=str_replace("\n","",$linea);
      $datos=explode("\t",$linea);
      $nombre=$datos[0];
      if($nombre==""){continue;}
      $descripcion=$datos[1];
      $importancia=$datos[2];
      $ecuacion=$datos[3];
      $min=$datos[4];
      $max=$datos[5];
      $datos=array_slice($datos,6);
      $tam=count($datos);
      for($i=$tam-1;$i>=0;$i--)
      {
        if($datos[$i]==""){unset($datos[$i]);}
      }
      $tam=count($datos);
      $et=(int)($tam/5);
      $etiquetas=array();
      for($i=0;$i<$et;$i++)
      {
        $label=$datos[$i*5 + 0];
        $l0=$datos[$i*5 + 1];
        $r0=$datos[$i*5 + 2];
        $l1=$datos[$i*5 + 3];
        $r1=$datos[$i*5 + 4];
        $set=array("label"=>$label,"L0"=>$l0,"R0"=>$r0,"L1"=>$l1,"R1"=>$r1);
        $etiquetas[]=$set;
      }
      
      $sql="INSERT INTO ".$table."(name,description,importance,equation,project_id) 
                            VALUES('".$nombre."','".$descripcion."','".$importancia."','".$ecuacion."','".$project_id."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $aggregator_id=mysqli_insert_id($this->link);
      
      $sql="INSERT INTO variables(name,description,minimum,maximum,aggregator_id,effect_propierty_id)
                        VALUES('".$nombre."','".$descripcion."','".$min."','".$max."','".$aggregator_id."',NULL)";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $variable_id=mysqli_insert_id($this->link);
      
      foreach($etiquetas as $et)
      {
        $sql="INSERT INTO sets(label,variable_id) VALUES('".$et['label']."','".$variable_id."')";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        $set_id=mysqli_insert_id($this->link);
        
        $sql="INSERT INTO cuts(alpha,L,R,set_id) VALUES('0.0','".$et['L0']."','".$et['R0']."','".$set_id."')";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        $sql="INSERT INTO cuts(alpha,L,R,set_id) VALUES('1.0','".$et['L1']."','".$et['R1']."','".$set_id."')";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);       
      }      
    }
  }

  function importEffectPropiertiesCSV($project_id,$fn,$flagDelete='true')
  {
    $table="effect_propierties";
    if($project_id<0){return;}

    if($flagDelete)
    {
      $sql="DELETE FROM ".$table." WHERE project_id='".$project_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
  
    $f=file($fn);
    if(!$f){return;}
    $f=array_slice($f,1);
    foreach ($f as $linea)
    {
      $linea=str_replace("\n","",$linea);
      $datos=explode("\t",$linea);
      $nombre=$datos[0];
      $descripcion=$datos[1];
      $naturaleza=$datos[2];
      $peso=$datos[3];
      $teta=$datos[4];
      $min=$datos[5];
      $max=$datos[6];
      $datos=array_slice($datos,7);
      $tam=count($datos);
      for($i=$tam-1;$i>=0;$i--)
      {
        if($datos[$i]==""){unset($datos[$i]);}
      }
      $tam=count($datos);
      $et=(int)($tam/5);
      $etiquetas=array();
      for($i=0;$i<$et;$i++)
      {
        $label=$datos[$i*5 + 0];
        $l0=$datos[$i*5 + 1];
        $r0=$datos[$i*5 + 2];
        $l1=$datos[$i*5 + 3];
        $r1=$datos[$i*5 + 4];
        $set=array("label"=>$label,"L0"=>$l0,"R0"=>$r0,"L1"=>$l1,"R1"=>$r1);
        $etiquetas[]=$set;
      }
      
      $sql="INSERT INTO ".$table."(name,description,nature,weight,theta,project_id) 
                            VALUES('".$nombre."','".$descripcion."','".$naturaleza."','".$peso."','".$teta."','".$project_id."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $eff_pro_id=mysqli_insert_id($this->link);
      
      $sql="INSERT INTO variables(name,description,minimum,maximum,aggregator_id,effect_propierty_id)
                        VALUES('".$nombre."','".$descripcion."','".$min."','".$max."',NULL,'".$eff_pro_id."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $variable_id=mysqli_insert_id($this->link);
      
      foreach($etiquetas as $et)
      {
        $sql="INSERT INTO sets(label,variable_id) VALUES('".$et['label']."','".$variable_id."')";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        $set_id=mysqli_insert_id($this->link);
        
        $sql="INSERT INTO cuts(alpha,L,R,set_id) VALUES('0.0','".$et['L0']."','".$et['R0']."','".$set_id."')";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        $sql="INSERT INTO cuts(alpha,L,R,set_id) VALUES('1.0','".$et['L1']."','".$et['R1']."','".$set_id."')";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);       
      }
    }
    
    $total=0;
    $sql="SELECT SUM(weight) AS S FROM ".$table." WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $total=$linea['S'];
    }
    if($total==0){$total=1;}
    $sql="UPDATE ".$table." SET weight=weight/".$total." WHERE project_id='".$project_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);   
  }
  
  function fillSinglePropierty($effect_id,$effect_propierty_id,$cuts)
  {
    $propierty_id=0;
    $sql="SELECT id FROM propierties WHERE effect_id='".$effect_id."' AND effect_propierty_id='".$effect_propierty_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $propierty_id=$linea['id'];
    }else
    {
      $sql="INSERT INTO propierties(effect_id,effect_propierty_id) VALUES('".$effect_id."','".$effect_propierty_id."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $propierty_id=mysqli_insert_id($this->link);
    }
    
    $input_id=0;
    $sql="SELECT id FROM inputs WHERE propierty_id='".$propierty_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $input_id=$linea['id'];
    }else
    {
      $set_id=0;
      $sql="SELECT sets.id FROM sets
                           INNER JOIN variables ON sets.variable_id=variables.id
                           WHERE variables.effect_propierty_id='".$effect_propierty_id."'";   
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $set_id=$linea['id'];
      }
    
      $sql="INSERT INTO inputs(set_id,propierty_id,description,type,crisp,L,R,modifier) 
                        VALUES('".$set_id."','".$propierty_id."','',3,0,0,1,3)";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $input_id=mysqli_insert_id($this->link);
    }
    
    $sql="SELECT id FROM input_cuts WHERE input_id='".$input_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
    }else
    {
      if($cuts<2){$cuts=2;}
      $dx=1.0/($cuts-1);
      for($i=0;$i<$cuts;$i++)
      {
        $alpha=$dx*$i;
        $sql="INSERT INTO input_cuts(input_id,alpha,L,R) VALUES('".$input_id."','".$alpha."',0,1)";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
    }    
  }

  function fillPropierties($project_id,$cuts)
  {
    $sql="SELECT id FROM effects WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $effect_id=$linea['id'];
        $sql2="SELECT id FROM effect_propierties WHERE project_id='".$project_id."'";
        $result2=mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
        if($result2 and mysqli_num_rows($result2)>0)
        {
          while($linea2=mysqli_fetch_array($result2,MYSQLI_ASSOC))
          {
            $effect_propierty_id=$linea2['id'];
            $this->fillSinglePropierty($effect_id,$effect_propierty_id,$cuts);
          }
        }
      }
    }
  }

  function updateAggregations($project_id,$cuts)
  {
    $IM=new importance();
    $IM->link=$this->link;
    $sql="SELECT id FROM effects WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $effect_id=$linea['id'];
        $IM->insert_single_importance($project_id,$effect_id,$cuts);
      }
    }
  }
  
  function create($project_id,$user_id)
  {
    $sql="INSERT INTO projects(name,description,created,modified)
                      VALUES('".$this->text('project_Without_name')."','".$this->text('project_Without_description')."',now(),now())";
//    echo $sql;
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $new_project_id=mysqli_insert_id($this->link);
    
    $sql="SELECT * FROM projects_users WHERE project_id='".$project_id."' and user_id='".$user_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $group_id=$linea['group_id'];
        $sql2="insert INTO projects_users(project_id,user_id,group_id)
                             VALUES(".$new_project_id.",".$user_id.",".$group_id.")";
        mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
      }
    }
    
    $sql="INSERT INTO factors(name,description,level,weight,family_weight,project_id,factor_id)
                      VALUES('".$this->text('project_Without_name')."','".$this->text('project_Without_description')."',1,1,0,".$new_project_id.",NULL)";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    
    $sql="INSERT INTO actions(name,description,level,project_id,action_id)
                      VALUES('".$this->text('project_Without_name')."','".$this->text('project_Without_description')."',0,".$new_project_id.",NULL)";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    
    return $new_project_id;
    
  } 

  function delete($project_id)
  {  
    $sql="DELETE FROM projects WHERE id='".$project_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    
    unset($_SESSION['TDEIA_project_id']);
//    unset($_SESSION['TDEIA_SESSION_TDEIA']);
    
    // ¿borrar variables?
  }
  
  
}

?>
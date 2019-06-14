<?php

class propierty
{
  var $link;
  
  function conectar()
  {
    $this->link=NULL;
    $this->link=mysqli_connect("localhost","root","rootpassword");
    if(!$this->link)
    {
      echo "No_Database_connection";
      return FALSE;
    }else
    {
      $sql="USE tdeia";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
      return $this->link;
    }
    return FALSE;
  }

  function projectExists($project_id)
  {
    $sql="SELECT * FROM projects WHERE id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      return true;
    }
    return false;
  }
  
  function importEffectPropiertiesCSV($project_id,$fn,$flagDelete='true')
  {
    $table="effect_propierties";
    if(!$this->projectExists($project_id))
    {
      $sql="INSERT INTO projects(name,description,created,modified) VALUES ('Proyecto de prueba CSV','una descripción extensa...',now(),now())";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $project_id=mysqli_insert_id($this->link);
    }
    if($flagDelete)
    {
      $sql="DELETE FROM ".$table." WHERE project_id='".$project_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
  
    $f=file($fn);
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
/*      
      $sql="INSERT INTO inputs(description,type,crisp,L,R,modifier,set_id) 
                        VALUES('".$descripcion."','3','".(0.5*($min+$max))."','".$min."','".$max."','1','".$set_id."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $input_id=mysqli_insert_id($this->link);
       
      $sql="INSERT INTO input_cuts(alpha,L,R,input_id) VALUES('0.0','".$min."','".$max."','".$input_id."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $sql="INSERT INTO input_cuts(alpha,L,R,input_id) VALUES('1.0','".$min."','".$max."','".$input_id."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);       
      
      $sql="UPDATE variables SET default_input_id='".$input_id."' WHERE id='".$variable_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);       */
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
  
  function find_id($project_id,$table,$col,$value,$flagProject=true)
  {
    $sql="SELECT id FROM ".$table." WHERE ".$col."='".$value."' ";
    if($flagProject)
    {
      $sql.="AND project_id='".$project_id."'";
    }
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      return $linea['id'];
    }
    return 0;
  }

  function importEffectsCSV($project_id,$dir,$fn,$flagDelete='true')
  {
    if(!$this->projectExists($project_id))
    {
      $sql="INSERT INTO projects(name,description,created,modified) VALUES ('Proyecto de prueba CSV','una descripción extensa...',now(),now())";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $project_id=mysqli_insert_id($this->link);
    }
    if($flagDelete)
    {
      $sql="DELETE FROM effects WHERE project_id='".$project_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
    
    $f=file($fn);
    $accionesId=array();
    $accionesName=array();
    $acciones=explode("\t",str_replace("\n","",$f[0]));
    foreach($acciones as $K=>$a)
    {
      $accionesId[$K]=$this->find_id($project_id,"actions","name",$a);
      $accionesName[$accionesId[$K]]=$a;
    }
    $tam=count($f);
    $efectos=array();
    $nature=array();
    $factoresName=array();
    for($i=1;$i<$tam;$i++)
    {
      $datos=explode("\t",str_replace("\n","",$f[$i]));
      $factor=$this->find_id($project_id,"factors","name",$datos[0]);
      $factoresName[$factor]=$datos[0];
      $efectosFactor=array();
      $natureFactor=array();
      $tam2=count($datos);
      for($j=1;$j<$tam2;$j++)
      {
        if(strlen($datos[$j])>0)
        {
          $efectosFactor[]=$accionesId[$j];
          if($datos[$j]<0)
          {
            $natureFactor[]=-1;
          }else
          {
            $natureFactor[]=1;          
          }
        }
      }
      $efectos[$factor]=$efectosFactor;
      $nature[$factor]=$natureFactor;
    }
    
    $efProp=array();
    $sql="SELECT id,name FROM effect_propierties WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $efProp[$linea['id']]=$linea['name'];
      }
    }
    
    foreach($efectos as $factor_id=>$efs)
    {
      foreach($efs as $K=>$action_id)
      {
        // crear efecto
        $sql="INSERT INTO effects(project_id,action_id,factor_id,name,description,nature)
                           VALUES('".$project_id."','".$action_id."','".$factor_id."',
                           '".($factoresName[$factor_id]."-".$accionesName[$action_id])."','Sin descripción','".$nature[$factor_id][$K]."')";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        $effect_id=mysqli_insert_id($this->link);
        foreach($efProp as $ef_prop_id=>$ef_name)
        {
          // leer el archivo y traer el valor y crear la propiedad
          $this->readSinglePropierty($dir.$ef_name.".csv",$project_id,$factor_id,$action_id,$ef_prop_id,$effect_id);
        }
      }
    }
  }
  
  function readSinglePropierty($fn,$project_id,$factor_id,$action_id,$eff_prop_id,$effect_id)
  {
    $f=file($fn);
    $accionesId=array();
    $acciones=explode("\t",str_replace("\n","",$f[0]));
    foreach($acciones as $K=>$a)
    {
      $accionesId[$K]=$this->find_id($project_id,"actions","name",$a);
    }
    $tam=count($f);
    $efectos=array();
    for($i=1;$i<$tam;$i++)
    {
      $datos=explode("\t",str_replace("\n","",$f[$i]));
      $factor=$this->find_id($project_id,"factors","name",$datos[0]);
      $efectosFactor=array();
      $tam2=count($datos);
      for($j=1;$j<$tam2;$j++)
      {
        if(strlen($datos[$j])>0)
        {
          $efectosFactor[$accionesId[$j]]=$datos[$j];
        }
      }
      $efectos[$factor]=$efectosFactor;
    }
    $PropName= $efectos[$factor_id][$action_id];    
    $sql="SELECT sets.id AS I,variables.name AS N,variables.description AS D,effect_propierties.nature as NA,variables.minimum AS MN,variables.maximum AS MX FROM sets
                         INNER JOIN variables ON variables.id=sets.variable_id
                         INNER JOIN effect_propierties ON effect_propierties.id=variables.effect_propierty_id
                         WHERE effect_propierties.id='".$eff_prop_id."'
                         AND project_id='".$project_id."'
                         AND sets.label='".$PropName."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $set_id=$linea['I'];
      
      $sql2="INSERT INTO propierties(effect_id,effect_propierty_id)
                         VALUES('".$effect_id."','".$eff_prop_id."')";
      mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);             
      $propierty_id=mysqli_insert_id($this->link);
      // OJO: revisar tipo de entrada!!!
      
      $sql2="INSERT INTO inputs(description,type,crisp,L,R,modifier,set_id,propierty_id) 
                        VALUES('".$linea['D']."','3','".(0.5*($linea['MN']+$linea['MX']))."',
                        '".$linea['MN']."','".$linea['MX']."','0','".$set_id."','".$propierty_id."')";
      mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
      $input_id=mysqli_insert_id($this->link);
       
      $sql2="INSERT INTO input_cuts(alpha,L,R,input_id) VALUES('0.0','".$linea['MN']."','".$linea['MX']."','".$input_id."')";
      mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
      $sql2="INSERT INTO input_cuts(alpha,L,R,input_id) VALUES('1.0','".$linea['MN']."','".$linea['MX']."','".$input_id."')";
      mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);       
      
    }
    
  }

  function importAggregatorsCSV($project_id,$fn,$flagDelete='true')
  {
    $table="aggregators";
    if(!$this->projectExists($project_id))
    {
      $sql="INSERT INTO projects(name,description,created,modified) VALUES ('Proyecto de prueba CSV','una descripción extensa...',now(),now())";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $project_id=mysqli_insert_id($this->link);
    }
    if($flagDelete)
    {
      $sql="DELETE FROM ".$table." WHERE project_id='".$project_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
    $f=file($fn);
    $f=array_slice($f,1);
    foreach ($f as $linea)
    {
      $linea=str_replace("\n","",$linea);
      $datos=explode("\t",$linea);
      $nombre=$datos[0];
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

  
}

/*
$dir="/home/ogduartev/public_html/phpTdeia/";
$P=new propierty;
if($P->conectar())
{
//  $P->importEffectPropiertiesCSV(1,$dir."datos/propiedades.csv",false);
//  $P->importEffectsCSV(1,$dir."datos/",$dir."datos/Efectos.csv",false);
  $P->importAggregatorsCSV(1,$dir."datos/agregadores.csv",false);
}
*/
?>

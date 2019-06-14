<?php

require_once("../fuzzy_number.php");
require_once("../importance.php");
require_once("../aggregation.php");
require_once("groups.php");
require_once("users.php");
require_once("projects.php");
require_once("factors.php");
require_once("propierties.php");

class poblar
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
  
  function getSubDirs($dir)
  {
    $subdirs=array();
    if (is_dir($dir))
    {
      if ($dh = opendir($dir))
      {
        while (($file = readdir($dh)) !== false)
        {
          if($file!=="." and $file!==".." and is_dir($file))
          {
            $subdirs[]=$dir.$file."/";
          }
        }
        closedir($dh);
      }
    } 
    return $subdirs;
  }
  
  function groups_permissions($dir)
  {
    $G=new group();
    if($G->conectar())
    {
      $G->importCsv($dir."permisos.csv",true);
    }
  }
  
  function users($dir)
  {
    $U=new user();
    if($U->conectar())
    {
      $U->importCsv($dir."usuarios.csv",false);
    }
  }
  
  function projects($dir)
  {
    $P=new project();
    if($P->conectar())
    {
      $projects=$P->importCsv($dir."proyectos.csv",false);
      return $projects;
    }
    return array();
  }
  
  function actions($project_id,$dir)
  {
    $F=new factor();
    if($F->conectar())
    {
      $F->importCsv($project_id,"action",$dir."acciones.csv",false);
    }
  }

  function factors($project_id,$dir)
  {
    $F=new factor();
    if($F->conectar())
    {
      $F->importCsv($project_id,"factor",$dir."factores.csv",false);
    }
  }

  function effect_propierties($project_id,$dir)
  {
    $P=new propierty;
    if($P->conectar())
    {
      $P->importEffectPropiertiesCSV($project_id,$dir."propiedades.csv",false);
    }
  }
  
  function aggregators($project_id,$dir)
  {
    $P=new propierty;
    if($P->conectar())
    {
      $P->importAggregatorsCSV($project_id,$dir."agregadores.csv",false);
    }
  }
  
  function effects($project_id,$dir)
  {
    $P=new propierty;
    if($P->conectar())
    {
      $P->importEffectsCSV($project_id,$dir,$dir."Efectos.csv",false);
    }
  }
  
  function importances($project_id,$cuts)
  {
    $sql="DELETE FROM importances";
//    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql ="SELECT id FROM effects WHERE project_id=".$project_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $I=new importance();
        $I->link=$this->link;
        $Imp=$I->insert_single_importance($project_id,$linea['id'],$cuts);
      }
    }
  }

}

$dir="/home/ogduartev/public_html/phpTdeia/datos/";
$P=new poblar();
$cuts=2;
if($P->conectar())
{
  echo "Grupos y permisos ".date(" G:i:s")."\n";
  $P->groups_permissions($dir);
  $subdirs=$P->getSubDirs($dir);
  foreach($subdirs as $subdir)
  {
    echo $subdir."\n";
    echo "Usuarios ".date(" G:i:s")."\n";
    $P->users($subdir);
    echo "Proyectos ".date(" G:i:s")."\n";
    $projects=$P->projects($subdir);
    foreach($projects as $project_id)
    {
      echo "Acciones ".date(" G:i:s")."\n";
      $P->actions($project_id,$subdir);
      echo "Factores ".date(" G:i:s")."\n";
      $P->factors($project_id,$subdir);
      echo "Propiedades ".date(" G:i:s")."\n";
      $P->effect_propierties($project_id,$subdir);
      echo "Agregadores ".date(" G:i:s")."\n";
      $P->aggregators($project_id,$subdir);
      echo "Efectos ".date(" G:i:s")."\n";
      $P->effects($project_id,$subdir);
      echo "Importancias ".date(" G:i:s")."\n";
      $P->importances($project_id,$cuts);
      echo "Fin ".date(" G:i:s")."\n";
    }
  }
}

?>

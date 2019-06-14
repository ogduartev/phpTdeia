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
    $link=mysqli_connect("localhost","root","rootpassword");
    if($link)
    {
      $sql="USE `tdeia`;";
      if(!mysqli_query($link,$sql))
      {
        echo "No existe la base de datos ".$this->base;
        $this->link=$link;
        return $link;
      }
    }else
    {
      return FALSE;
    }
  }
  
  function groups_permissions($dir)
  {
    $G=new group();
    $G->importCsv($dir."datos/permisos.csv",true);
  }
  
  function users($dir)
  {
    $U=new user();
    $U->importCsv($dir."datos/usuarios.csv",true);
  }
  
  function projects($dir)
  {
    $P=new project();
    $P->importCsv($dir."datos/proyectos.csv",true);
  }
  
  function usersOLD()
  {
    $sql="DELETE FROM users";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);

    $sql="INSERT INTO users(email_address,password,active,created,modified,firstname,lastname) VALUES ('ogduartev@gmail.com',sha1('password'),1,now(),now(),'Oscar Germán','Duarte Velasco')";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $user_id_1=mysqli_insert_id($this->link);

    $sql="DELETE FROM groups";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO groups(name,created,modified) VALUES ('administrador',now(),now())";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $group_id_1=mysqli_insert_id($this->link);
    $sql="INSERT INTO groups(name,created,modified) VALUES ('visitante',now(),now())";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $group_id_2=mysqli_insert_id($this->link);
/*
    $sql="DELETE FROM groups_users";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO groups_users(group_id,user_id) VALUES (".$group_id_1.",".$user_id_1.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO groups_users(group_id,user_id) VALUES (".$group_id_2.",".$user_id_1.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
*/
    $sql="DELETE FROM permissions";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO permissions(name,created,modified) VALUES ('crear',now(),now())";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $permission_id_1=mysqli_insert_id($this->link);
    $sql="INSERT INTO permissions(name,created,modified) VALUES ('borrar',now(),now())";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $permission_id_2=mysqli_insert_id($this->link);
    $sql="INSERT INTO permissions(name,created,modified) VALUES ('actualizar',now(),now())";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $permission_id_3=mysqli_insert_id($this->link);
    $sql="INSERT INTO permissions(name,created,modified) VALUES ('ver',now(),now())";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $permission_id_4=mysqli_insert_id($this->link);

    $sql="DELETE FROM groups_permissions";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO groups_permissions(group_id,permission_id) VALUES (".$group_id_1.",".$permission_id_1.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO groups_permissions(group_id,permission_id) VALUES (".$group_id_1.",".$permission_id_2.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO groups_permissions(group_id,permission_id) VALUES (".$group_id_1.",".$permission_id_3.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO groups_permissions(group_id,permission_id) VALUES (".$group_id_1.",".$permission_id_4.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO groups_permissions(group_id,permission_id) VALUES (".$group_id_2.",".$permission_id_4.")";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
  }
  
  function projectsOLD()
  {
    $sql="DELETE FROM projects";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="INSERT INTO projects(name,description,created,modified) VALUES ('Proyecto de prueba','una descripción extensa...',now(),now())";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $project_id_1=mysqli_insert_id($this->link);
    
    $sql="SELECT id FROM users";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $sql2="INSERT INTO projects_users(project_id,user_id,group_id) VALUES(".$project_id_1.",".$linea['id'].",1)";
        mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
      }
    }
  }

  function actions($project_id,$dir)
  {
    $F=new factor();
    $F->importCsv($project_id,"action",$dir."datos/acciones.csv",false);
  }

  function factors($project_id,$dir)
  {
    $F=new factor();
    $F->importCsv($project_id,"factor",$dir."datos/factores.csv",false);
  }

  function effect_propierties($project_id,$dir)
  {
    $P=new propierty;
    $P->importEffectPropiertiesCSV($project_id,$dir."datos/propiedades.csv",false);
  }
  
  function aggregators($project_id,$dir)
  {
    $P=new propierty;
    $P->importAggregatorsCSV($project_id,$dir."datos/agregadores.csv",false);
  }
  
  function effects($project_id,$dir)
  {
    $P=new propierty;
    $P->importEffectsCSV($project_id,$dir."datos/",$dir."datos/Efectos.csv",false);
  }
  
  function importances($project_id,$cuts)
  {
    $sql="DELETE FROM importances";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql ="SELECT id FROM effects WHERE project_id=".$project_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $I=new importance();
        $Imp=$I->insert_single_importance($project_id,$linea['id'],$cuts);
      }
    }
  }

}

$dir="/home/ogduartev/public_html/phpTdeia/";
$P=new poblar();
$cuts=2;
if($P->conectar())
{
  echo "Grupos y permisos ".date(" G:i:s")."\n";
  $P->groups_permissions($dir);
  echo "Usuarios ".date(" G:i:s")."\n";
  $P->users($dir);
  echo "Proyectos ".date(" G:i:s")."\n";
  $P->projects($dir);
  $sql="SELECT id FROM projects";
  $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
  if($result and mysqli_num_rows($result)>0)
  {
//    while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
    $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);  // solo se llena el primer proyecto
    {
      echo "Acciones ".date(" G:i:s")."\n";
      $P->actions($linea['id'],$dir);
      echo "Factores ".date(" G:i:s")."\n";
      $P->factors($linea['id'],$dir);
      echo "Propiedades ".date(" G:i:s")."\n";
      $P->effect_propierties($linea['id'],$dir);
      echo "Agregadores ".date(" G:i:s")."\n";
      $P->aggregators($linea['id'],$dir);
      echo "Efectos ".date(" G:i:s")."\n";
      $P->effects($linea['id'],$dir);
      echo "Importancias ".date(" G:i:s")."\n";
      $P->importances($linea['id'],$cuts);
      echo "Fin ".date(" G:i:s")."\n";
    }
  }
}

?>

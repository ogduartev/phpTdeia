<?php

class project
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

  function importCsv($fn,$flagDelete=true)
  {
    $projects=array();
    if($flagDelete)
    {
      $sql="DELETE FROM projects";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $sql="DELETE FROM projects_users";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
    $f=file($fn);
    $f=array_slice($f,1);
    foreach($f as $line)
    {
      $data=explode("\t",str_replace("\n","",$line));
      $name=$data[0];
      $description=$data[1];
      $group=$data[2];
      $user=$data[3];
      
      $project_id=0;
      $group_id=0;
      $user_id=0;
      
      $sql="SELECT id FROM projects WHERE name='".$name."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)." : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $project_id=$linea['id'];
      }else
      {
        $sql="INSERT INTO projects(name,description,created,modified) VALUES('".$name."','".$description."',now(),now())";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        $project_id=mysqli_insert_id($this->link);
        $projects[]=$project_id;
      }
      
      $sql="SELECT id FROM groups WHERE name='".$group."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)." : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $group_id=$linea['id'];
      }
      
      $sql="SELECT id FROM users WHERE email_address='".$user."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)." : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $user_id=$linea['id'];
      }
           
      if($project_id>0 and $group_id>0 and $user_id>0)
      {
        $sql="INSERT INTO projects_users(project_id,group_id,user_id) VALUES('".$project_id."','".$group_id."','".$user_id."')";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
    }
    return $projects;
  }


}


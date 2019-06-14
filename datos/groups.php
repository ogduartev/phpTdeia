<?php

class group
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
    if($flagDelete)
    {
      $sql="DELETE FROM groups";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $sql="DELETE FROM permissions";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $sql="DELETE FROM groups_permissions";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
    $f=file($fn);
    $grupos=explode("\t",str_replace("\n","",$f[0]));
//    $grupos=array_slice($grupos,1);
    $groupId=array();
    foreach($grupos as $K=>$g)
    {
      if($K<3){continue;}
      $sql="INSERT INTO groups(name,created,modified) VALUES('".$g."',now(),now())";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $groupId[$K]=mysqli_insert_id($this->link);
    }    
    $f=array_slice($f,1);
    foreach($f as $line)
    {
      $data=explode("\t",str_replace("\n","",$line));
      $name=$data[0];
      $action=$data[1];
      $object=$data[2];
      $sql="INSERT INTO permissions(name,action,object) VALUES('".$name."','".$action."','".$object."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $p_id=mysqli_insert_id($this->link);
      foreach($groupId as $K=>$g_id)
      {
        $active=0;if(strlen($data[$K])>0){$active=1;}
         $sql="INSERT INTO groups_permissions(group_id,permission_id,active) VALUES('".$g_id."','".$p_id."','".$active."')";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
    }
  }


}


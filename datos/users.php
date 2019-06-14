<?php

class user
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
      $sql="DELETE FROM users";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
    $f=file($fn);
    $f=array_slice($f,1);
    foreach($f as $line)
    {
      $data=explode("\t",str_replace("\n","",$line));
      $email=$data[0];
      $active=$data[1];
      $firstname=$data[2];
      $lastname=$data[3];
      $password=$data[4];
      $sql="SELECT * FROM users WHERE email_address='".$email."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)." : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
      }else
      {
        $sql="INSERT INTO users(email_address,active,firstname,lastname,password,created,modified) VALUES('".$email."','".$active."','".$firstname."','".$lastname."',sha1('".$password."'),now(),now())";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
    }
  }


}


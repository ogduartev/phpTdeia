<?php
require_once('block.php');

class sessionManager extends block 
{
  function verify()
  {
    session_start();
    if($this->connect())
    {
      $sql="SELECT * FROM users WHERE email_address=\"".$_POST['loginname']."\" AND password=SHA1(\"".$_POST['loginpass']."\")";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $_SESSION['TDEIA_user_id']=$linea['id'];
        $_SESSION['TDEIA_project_id']=0;
        $_SESSION['TDEIA_SESSION_TDEIA']=true;
        $sql2="SELECT * FROM projects 
                       INNER JOIN projects_users ON projects.id=projects_users.project_id
                       INNER JOIN users ON projects_users.user_id=users.id
                       WHERE users.id='".$linea['id']."' LIMIT 1";
        $result2=mysqli_query($this->link,$sql2) or die(mysqli_error($this->link).": ".$sql2);
        if($result2 and mysqli_num_rows($result2)>0)
        {
          $linea2=mysqli_fetch_array($result2,MYSQLI_ASSOC);
          $_SESSION['TDEIA_project_id']=$linea2['project_id'];
        }               
        return true;
      }//echo $sql;
    }
    return false;
  }
}

?>

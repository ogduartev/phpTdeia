<?php

class title extends block
{

  function display()
  {
    if(!isset($_SESSION['TDEIA_project_id']))
    {
      $sql="SELECT * FROM projects 
                   INNER JOIN projects_users ON projects_users.project_id=projects.id
                   WHERE user_id='".$_SESSION['TDEIA_user_id']."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $_SESSION['TDEIA_project_id']=$linea['project_id'];
      }
    }

    if($this->allowAny("project"))
    {
      $sql="SELECT * FROM projects WHERE id='".$_SESSION['TDEIA_project_id']."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $onclikck="";
        if($this->allowAny("project") or $this->allowAny("project") or $this->allowAny("project"))
        {
          echo "          <form method=\"post\" action=\"login.php\">\n";
          if(!isset($_POST['edit_project']))
          {
            echo "            <input type=\"hidden\" name=\"edit_project\" value=\"true\"/>\n";
          }
          echo "            <input type=\"button\" class=\"title_project\" onClick=\"submit();\" value=\"".$linea['name']."\"/>\n";
          echo "          </form>\n";
        
        }else
        {
          echo "          <span >".$linea['name']."</span>\n";
        }
      }
    }else
    {
      echo $this->text('title_Project_no_selected');
    }
  }

}

?>

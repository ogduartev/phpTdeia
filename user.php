<?php

class user extends block
{

  function display()
  {

    if($this->allowAny("user"))
    {
      $sql="SELECT * FROM users WHERE id='".$_SESSION['TDEIA_user_id']."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        echo "          <nobr>".$linea['firstname']." ".$linea['lastname']."</nobr>\n";
      }else
      {
        echo $this->text('user_User_not_found');
      }
      
      if($this->allowAny("project"))
      {
        $sql="SELECT * FROM projects 
                     INNER JOIN projects_users ON projects_users.project_id=projects.id
                     WHERE user_id='".$_SESSION['TDEIA_user_id']."'";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          echo "          <form method=\"post\" action=\"login.php\">\n";
          echo "           <select class=\"projects\" name=\"project_id\" onChange=\"submit();\">\n";
          $cnt=0;
          while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
          {
            if(!isset($_SESSION['TDEIA_project_id']) and $cnt==0){$_SESSION['TDEIA_project_id']=$linea['project_id'];}
            $cnt++;
            $sel="";if(isset($_SESSION['TDEIA_project_id']) and $_SESSION['TDEIA_project_id']==$linea['project_id']){$sel="selected=\"selected\"";}
            echo "            <option value=\"".$linea['project_id']."\" ".$sel." >".$linea['name']."</option>";
          }
          echo "           </select>\n";
          echo "          </form>\n";
        }
      }
    }else
    {
      echo $this->text('user_User_not_found');
    }
  }

}

?>
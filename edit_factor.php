<?php
require_once('block.php');
require_once('config/config.class.php');
require_once('factor.php');

class edit_factor extends block
{
  function update($cuts=2)
  {
    if($this->allowUpdate("factor"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $factor_id=$_POST['factor_id'];
      if($factor_id<1){return;}
      
      if(isset($_POST['factor_name']))
      {
        $col='name';
        $val=htmlspecialchars($_POST['factor_name'], ENT_QUOTES, 'UTF-8');
        $sql="UPDATE factors SET ".$col."='".$val."' WHERE id='".$factor_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
      
      $factor=new factor();
      $factor->link=$this->link;
      $factor->updateParent($project_id,$factor_id,$_POST['factor_parent'],$cuts);
      
    }
  }

  function delete()
  {
    if($this->allowDelete("factor"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $factor_id=$_POST['factor_id'];
      if($project_id>0 and $factor_id>0)
      {
        $A=new factor();
        $A->link=$this->link;
        $A->delete($project_id,$factor_id);
      }
    }
  }

  function create()
  {
    if($this->allowCreate("factor"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $factor_id=$_POST['factor_id'];
      if($project_id>0 and $factor_id>0)
      {
        $A=new factor();
        $A->link=$this->link;
        $new_id=$A->create($project_id,$factor_id);
        return $new_id;
      }
    }
  }

  function displayTitle()
  {
    echo "<div class=\"edit_factor_title\">".$this->text('factor_Environment_factor')."</div>\n";
  }

  function displayName($factor)
  {
    echo "          <div class=\"edit_factor_name\">\n";
    echo "            <div class=\"edit_factor_name_label\">".$this->text('factor_Name')." : </div>\n";
    echo "            <input type=\"text\" class=\"edit_factor_name\" name=\"factor_name\" id=\"factor_name\" value=\"".$factor['name']."\"/>\n";
    echo "          </div>\n";
  }
    
  function displayParent($factor)
  {
    $A=new factor();
    $A->link=$this->link;
    $child=$A->findDownIds($factor['id']);

    echo "          <div class=\"edit_factor_parent\">\n";
    echo "            <div class=\"edit_factor_parent_label\">".$this->text('factor_Parent_node')." : </div>\n";
    echo "            <select class=\"edit_factor_parent\" name=\"factor_parent\" id=\"factor_parent\">\n";
    $sql="SELECT * FROM factors WHERE project_id='".$factor['project_id']."' AND id<>'".$factor['id']."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
       while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
       {
         if(!in_array($linea['id'],$child))
         {
           $sel="";if($linea['id']==$factor['factor_id']){$sel="selected";}
           echo "            <option value='".$linea['id']."' ".$sel." >".$linea['name']."</option>\n";
         }
       }
    }
    echo "            </select>\n";
    echo "          </div>\n";
  }
    
  function displayWeight($factor)
  {
    $conf = new configuration();
    $settings=$conf->readconfig("phpTDEIAconfig.txt");
    $width=$settings['weightsWidth'];
    $height=$settings['weightsHeight'];    

    echo "
                   <script type=\"text/javascript\">
                     function weights()
                     {
                       window.open('edit_weights.php?factor_id=".$factor['id']."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";

    echo "          <div class=\"edit_factor_weight\">\n";
    echo "            <div class=\"edit_factor_weight_label\">".$this->text('factor_Weight')." : </div>\n";
    echo "            <input type=\"button\" class=\"edit_factor_weight\" name=\"factor_weight\" id=\"factor_weight\" value=\"".$this->text('factor_Weight_edit')."\" onClick=\"javascript:weights();\"/>\n";  
    echo "          </div>\n";
  }
    
  function displayDescription($factor)
  {
    if($this->allowRead("factor") or $this->allowUpdate("factor"))
    {
      $conf = new configuration();
      $settings=$conf->readconfig("phpTDEIAconfig.txt");
      $width=$settings['longtextWidth'];
      $height=$settings['longtextHeight'];
      $edit='false';
      if($this->allowUpdate("factor"))
      {
        $edit='true';
      }
      echo "
                   <script type=\"text/javascript\">
                     function description()
                     {
                       window.open('edit_longtext.php?table=factors&col=description&id=".$factor['id']."&title=".$factor['name']."&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";
      echo "         <input type=\"button\" name=\"factor_description\" class=\"edit_factor_description\" value=\"".$this->text('factor_Description')."\" onClick=\"javascript:description();\">\n";
    }
  }
  
  function displayHidden()
  {
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_action_")
      {
        echo "<input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_factor_")
      {
        echo "<input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    echo "         <input type=\"hidden\" name=\"factorLevel\" value='".$_POST['factorLevel']."'>\n";
    echo "         <input type=\"hidden\" name=\"actionLevel\" value='".$_POST['actionLevel']."'>\n";
    echo "         <input type=\"hidden\" name=\"matrixType\" value='".$_POST['matrixType']."'>\n";
    echo "         <input type=\"hidden\" name=\"cellType\" value='".$_POST['cellType']."'>\n";
    echo "         <input type=\"hidden\" name=\"varId\" value='".$_POST['varId']."'>\n";
    echo "         <input type=\"hidden\" name=\"edit_factor_id\" value='".$_POST['edit_factor_id']."'>\n";
    echo "         <input type=\"hidden\" name=\"factor_id\" value='".$_POST['factor_id']."'>\n";
  }
  
  function displaySubmit()
  {
    if($this->allowUpdate("factor"))
    {
      echo   "          <input type=\"submit\" name=\"edit_factor_submit\" class=\"edit_factor_submit\" value=\"".$this->text('factor_Update')."\">\n";   
    }
  }
 
  function displayDelete($effect)
  {
    if($this->allowDelete("factor"))
    {
      echo "  <form method=\"post\" factor=\"login.php\">\n";
      echo "    <div class=\"edit_factor_delete\">\n";
      $this->displayHidden($effect);
      echo "      <input type=\"submit\" name=\"factor_delete\" class=\"edit_factor_delete\" value=\"".$this->text('factor_Delete')."\" onClick=\"return confirm('".$this->text("factor_Delete_confirm")."')\">\n";   
      echo "    </div>\n";
      echo "  </form>\n";
    }
  }
 
  
  function displayNew($factor_id)
  {
    if($this->allowCreate("factor"))
    {
      echo "  <form method=\"post\" factor=\"login.php\">\n";
      echo "    <div class=\"edit_factor_new\">\n";
      $this->displayHidden($effect);
      echo "      <input type=\"submit\" name=\"factor_new\" class=\"edit_factor_new\" value=\"".$this->text('factor_New')."\">\n";   
      echo "    </div>\n";
      echo "  </form>\n";
    }
  }


  function display()
  {
    if($this->allowAny("factor"))
    {        
      $factor_id=$_POST['factor_id'];
      
      if($factor_id<1){return;}
      $factor=0;
      $sql="SELECT * FROM factors WHERE id='".$factor_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $factor=$linea;
      }else
      {
        return;
      }
      

      echo "    <form method=\"post\" factor=\"login.php\">\n";
      
      $this->displayTitle();
      $this->displayName($factor);
      $this->displayParent($factor);
      $this->displayWeight($factor);
      $this->displayDescription($factor);
      $this->displayHidden($factor);
      $this->displaySubmit();
      $this->displayNew($factor);
      $this->displayDelete($factor);
     echo "    </form>\n";
   }
  }
}

?>

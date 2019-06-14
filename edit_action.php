<?php
require_once('block.php');
require_once('config/config.class.php');
require_once('action.php');

class edit_action extends block
{
  function update($cuts=2)
  {
    if($this->allowUpdate("action"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $action_id=$_POST['action_id'];
      if($action_id<1){return;}
      
      if(isset($_POST['action_name']))
      {
        $col='name';
        $val=htmlspecialchars($_POST['action_name'], ENT_QUOTES, 'UTF-8');
        $sql="UPDATE actions SET ".$col."='".$val."' WHERE id='".$action_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
      
      $action=new action();
      $action->link=$this->link;
      $action->updateParent($project_id,$action_id,$_POST['action_parent'],$cuts);
      
    }
  }

  function delete()
  {
    if($this->allowDelete("action"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $action_id=$_POST['action_id'];
      if($project_id>0 and $action_id>0)
      {
        $A=new action();
        $A->link=$this->link;
        $A->delete($project_id,$action_id);
      }
    }
  }

  function create()
  {
    if($this->allowCreate("action"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $action_id=$_POST['action_id'];
      if($project_id>0 and $action_id>0)
      {
        $A=new action();
        $A->link=$this->link;
        $new_id=$A->create($project_id,$action_id);
        return $new_id;
      }
    }
  }

  function displayTitle()
  {
    echo "<div class=\"edit_action_title\">".$this->text('action_Project_action')."</div>\n";
  }

  function displayName($action)
  {
    echo "          <div class=\"edit_action_name\">\n";
    echo "            <div class=\"edit_action_name_label\">".$this->text('action_Name')." : </div>\n";
    echo "            <input type=\"text\" class=\"edit_action_name\" name=\"action_name\" id=\"action_name\" value=\"".$action['name']."\"/>\n";
    echo "          </div>\n";
  }
    
  function displayParent($action)
  {
    $A=new action();
    $A->link=$this->link;
    $child=$A->findDownIds($action['id']);

    echo "          <div class=\"edit_action_parent\">\n";
    echo "            <div class=\"edit_action_parent_label\">".$this->text('action_Parent_node')." : </div>\n";
    echo "            <select class=\"edit_action_parent\" name=\"action_parent\" id=\"action_parent\">\n";
    $sql="SELECT * FROM actions WHERE project_id='".$action['project_id']."' AND id<>'".$action['id']."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
       while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
       {
         if(!in_array($linea['id'],$child))
         {
           $sel="";if($linea['id']==$action['action_id']){$sel="selected";}
           echo "            <option value='".$linea['id']."' ".$sel." >".$linea['name']."</option>\n";
         }
       }
    }
    echo "            </select>\n";
    echo "          </div>\n";
  }
    
  function displayDescription($action)
  {
    if($this->allowRead("action") or $this->allowUpdate("action"))
    {
      $conf = new configuration();
      $settings=$conf->readconfig("phpTDEIAconfig.txt");
      $width=$settings['longtextWidth'];
      $height=$settings['longtextHeight'];
      $edit='false';
      if($this->allowUpdate("action"))
      {
        $edit='true';
      }
      echo "
                   <script type=\"text/javascript\">
                     function description()
                     {
                       window.open('edit_longtext.php?table=actions&col=description&id=".$action['id']."&title=".$action['name']."&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";
      echo "         <input type=\"button\" name=\"action_description\" class=\"edit_action_description\" value=\"".$this->text('action_Description')."\" onClick=\"javascript:description();\">\n";
    }
  }
  
  function displayHidden()
  {
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_factor_")
      {
        echo "<input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_action_")
      {
        echo "<input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    echo "         <input type=\"hidden\" name=\"actionLevel\" value='".$_POST['actionLevel']."'>\n";
    echo "         <input type=\"hidden\" name=\"factorLevel\" value='".$_POST['factorLevel']."'>\n";
    echo "         <input type=\"hidden\" name=\"matrixType\" value='".$_POST['matrixType']."'>\n";
    echo "         <input type=\"hidden\" name=\"cellType\" value='".$_POST['cellType']."'>\n";
    echo "         <input type=\"hidden\" name=\"varId\" value='".$_POST['varId']."'>\n";
    echo "         <input type=\"hidden\" name=\"edit_action_id\" value='".$_POST['edit_action_id']."'>\n";
    echo "         <input type=\"hidden\" name=\"action_id\" value='".$_POST['action_id']."'>\n";
  }
  
  function displaySubmit()
  {
    if($this->allowUpdate("action"))
    {
      echo   "          <input type=\"submit\" name=\"edit_action_submit\" class=\"edit_action_submit\" value=\"".$this->text('action_Update')."\">\n";   
    }
  }
 
  function displayDelete($effect)
  {
    if($this->allowDelete("action"))
    {
      echo "  <form method=\"post\" action=\"login.php\">\n";
      echo "    <div class=\"edit_action_delete\">\n";
      $this->displayHidden($effect);
      echo "      <input type=\"submit\" name=\"action_delete\" class=\"edit_action_delete\" value=\"".$this->text('action_Delete')."\" onClick=\"return confirm('".$this->text("action_Delete_confirm")."')\">\n";   
      echo "    </div>\n";
      echo "  </form>\n";
    }
  }
 
  
  function displayNew($action_id)
  {
    if($this->allowCreate("action"))
    {
      echo "  <form method=\"post\" action=\"login.php\">\n";
      echo "    <div class=\"edit_action_new\">\n";
      $this->displayHidden($effect);
      echo "      <input type=\"submit\" name=\"action_new\" class=\"edit_action_new\" value=\"".$this->text('action_New')."\">\n";   
      echo "    </div>\n";
      echo "  </form>\n";
    }
  }


  function display()
  {
    if($this->allowAny("action"))
    {        
      $action_id=$_POST['action_id'];
      
      if($action_id<1){return;}
      $action=0;
      $sql="SELECT * FROM actions WHERE id='".$action_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $action=$linea;
      }else
      {
        return;
      }
      

      echo "    <form method=\"post\" action=\"login.php\">\n";
      
      $this->displayTitle();
      $this->displayName($action);
      $this->displayParent($action);
      $this->displayDescription($action);
      $this->displayHidden($action);
      $this->displaySubmit();
      $this->displayNew($action);
      $this->displayDelete($action);
     echo "    </form>\n";
   }
  }
}

?>

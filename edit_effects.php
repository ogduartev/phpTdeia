<?php
require_once('block.php');
require_once('config/config.class.php');
require_once('effect.php');

class edit_effects extends block
{
  function update()
  {
    if($this->allowUpdate("effect"))
    {
      $effect_id=$_POST['effectId'];
      if($effect_id<1){return;}
      
      if(isset($_POST['effect_name']))
      {
        $col='name';
        $val=htmlspecialchars($_POST['effect_name'], ENT_QUOTES, 'UTF-8');
        $sql="UPDATE effects SET ".$col."='".$val."' WHERE id='".$effect_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
      
      if(isset($_POST['effect_nature']))
      {
        $col='nature';
        $val=$_POST['effect_nature'];
        $sql="UPDATE effects SET ".$col."='".$val."' WHERE id='".$effect_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
    }

    // UPDATE importances
    $IM=new importance();
    $IM->link=$this->link;
    $project_id=$_SESSION['TDEIA_project_id'];
    $cuts=2;
    if($project_id>0 and $effect_id>0 and $cuts>0)
    {
      $IM->insert_single_importance($project_id,$effect_id,$cuts);
    }
    
  }

  function delete()
  {
    if($this->allowDelete("effect"))
    {
      $effect_id=$_POST['effectId'];
      $project_id=$_SESSION['TDEIA_project_id'];
      $factor_id=0;
      $action_id=0;
      $sql="SELECT * FROM effects WHERE id='".$effect_id."' LIMIT 1";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $factor_id=$linea['factor_id'];
        $action_id=$linea['action_id'];
      }
      
      $sql="DELETE FROM effects WHERE id='".$effect_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      
      // importances
      
      if($factor_id > 0 and $action_id >0 and $project_id >0)
      {
        $IM=new importance();
        $IM->link=$this->link;
        $IM->update_parent_aggregations($project_id,$factor_id,$action_id);
      }
    }
  }

  function create()
  {
    if($this->allowCreate("effect"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $idstr=$_POST['varId'];
      $pos=strpos($idstr,'-');
      $factor_id=substr($idstr,0,$pos);
      $action_id=substr($idstr,$pos+1);
      if($project_id>0 and $factor_id>0 and $action_id>0)
      {
        $E=new effect();
        $E->link=$this->link;
        $E->create($project_id,$factor_id,$action_id);
      }
    }
  }

  function displayTitle($factor_id,$action_id)
  {
    $factorName="";
    $sql="SELECT name FROM factors WHERE id='".$factor_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $factorName=$linea['name'];
    }

    $actionName="";
    $sql="SELECT name FROM actions WHERE id='".$action_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $actionName=$linea['name'];
    }
    
    $text=$this->text('effect_title_1')."'".$factorName."'".$this->text('effect_title_2')."'".$actionName."'";
    echo "<div class=\"effect_title\">".$text."</div>\n";

    $numEffects=0;
    $sql="SELECT count(*) AS N FROM effects WHERE factor_id='".$factor_id."' AND action_id='".$action_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $numEffects=$linea['N'];
    }
    $text=$this->text('effect_Number_of_effects').": ".$numEffects;
    echo "<div class=\"effect_subtitle\">".$text."</div>\n";
  }

  function displayName($effect)
  {
    echo "     <input type=\"text\" class=\"effect_name\" name=\"effect_name\" id=\"effect_name_".$effect['id']."\" value=\"".$effect['name']."\"/>\n";
  }
  
  function displayNature($effect)
  {
    echo "     <select class=\"effect_nature\" name=\"effect_nature\" name=\"effect_nature_".$effect['id']."\">\n";
    $sel="";if($effect['nature']<0){$sel="selected";}
    echo "       <option value=\"-1\" ".$sel." >".$this->text('effect_Harmful')."</option>\n";
    $sel="";if($effect['nature']>0){$sel="selected";}
    echo "       <option value=\"1\" ".$sel." >".$this->text('effect_Beneficial')."</option>\n";
    echo "     </select>\n";
  }
  
  
  function displayDescription($effect)
  {
    if($this->allowRead("effect") or $this->allowUpdate("effect"))
    {
      $conf = new configuration();
      $settings=$conf->readconfig("phpTDEIAconfig.txt");
      $width=$settings['longtextWidth'];
      $height=$settings['longtextHeight'];
      $edit='false';
      if($this->allowUpdate("effect"))
      {
        $edit='true';
      }
      echo "
                   <script type=\"text/javascript\">
                     function description()
                     {
                       window.open('edit_longtext.php?table=effects&col=description&id=".$effect['id']."&title=".$effect['name']."&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";
      echo "         <input type=\"button\" name=\"effect_description\" class=\"effect_description\" value=\"".$this->text('effect_Description')."\" onClick=\"javascript:description();\">\n";     
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
  }
  
  function displaySubmit()
  {
    if($this->allowUpdate("effect"))
    {
      echo   "          <input type=\"submit\" name=\"effect_submit\" class=\"effect_submit\" value=\"".$this->text('effect_Update')."\">\n";   
    }
  }
 
  function displayDelete($effect)
  {
    if($this->allowDelete("effect"))
    {
      $this->displayHidden();
      echo "          <input type=\"hidden\" name=\"effectId\" id=\"effectId\" value='".$effect['id']."'>\n";
      echo "          <input type=\"submit\" name=\"effect_delete\" class=\"effect_delete\" value=\"".$this->text('effect_Delete')."\" onClick=\"return confirm('".$this->text("effect_Delete_confirm")."')\">\n";   
    }
  }
 
  function displayEffect($effect,$cnt)
  {
    $conf = new configuration();
    $settings=$conf->readconfig("matrixSettings.txt");
    $offset=$settings['effectCellOffset'];
    $height=$settings['effectCellHeight'];
    $top=$offset + $height*$cnt;
    echo "  <div class=\"effect_single\" style=\"top:".$top."px;\">\n";
    echo "    <form method=\"post\" action=\"login.php\">\n";
    $this->displayNature($effect);
    $this->displayHidden($effect);
    $this->displaySubmit();
    $this->displayDelete($effect);
    $title=$this->displayName($effect);
    echo "    </form>\n";
    $this->displayDescription($effect,$title);
    echo "  </div>\n";
  }
  
  function displayHeader()
  {
    $conf = new configuration();
    $settings=$conf->readconfig("matrixSettings.txt");
    $offset=$settings['effectCellOffset'];
    $height=$settings['effectCellHeight'];
    $top=$offset;
    echo "  <div class=\"effect_single\" style=\"top:".$top."px\";>\n";
    echo   "          <div class=\"effect_name_label\">".$this->text('effect_Name')."</div>\n";   
    echo   "          <div class=\"effect_nature_label\">".$this->text('effect_Nature')."</div>\n";   
    echo "  </div>\n";
  }

  function displayNew($factor_id,$action_id,$cnt)
  {
    $conf = new configuration();
    $settings=$conf->readconfig("matrixSettings.txt");
    $offset=$settings['effectCellOffset'];
    $height=$settings['effectCellHeight'];
    $top=$offset + $height*$cnt;
    echo "  <form method=\"post\" action=\"login.php\">\n";
    echo "    <div class=\"effect_single\" style=\"top:".$top."px\";>\n";
    $this->displayHidden($effect);
    echo "      <input type=\"submit\" name=\"effect_new\" class=\"effect_new\" value=\"".$this->text('effect_New')."\">\n";   
    echo "    </div>\n";
    echo "  </form>\n";
  
  }


  function display()
  {
    if($this->allowAny("effect"))
    {        
      $idstr=$_POST['varId'];
      $pos=strpos($idstr,'-');
      $factor_id=substr($idstr,0,$pos);
      $action_id=substr($idstr,$pos+1);
      
      if(($factor_id<1) or ($action_id<1)){return;}
      
      $this->displayTitle($factor_id,$action_id);
 
      $cnt=0;
      $sql="SELECT * FROM effects WHERE factor_id='".$factor_id."' AND action_id='".$action_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $this->displayHeader();
        while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
        {
          $cnt++;
          $this->displayEffect($linea,$cnt);
        }
      }

      if($this->allowCreate("effect"))
      {        
        $this->displayNew($factor_id,$action_id,$cnt+1);
      }
 
    }
  }
}

?>

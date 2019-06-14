<?php
require_once('config/config.class.php');
require_once('block.php');
require_once('input.php');

class edit_project extends block
{
  function update($cuts=2)
  {
    if($this->allowUpdate("project"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      if($project_id<1){return;}
      
      if(isset($_POST['project_name']))
      {
        $col='name';
        $val=htmlspecialchars($_POST['project_name'], ENT_QUOTES, 'UTF-8');
        $sql="UPDATE projects SET ".$col."='".$val."' WHERE id='".$project_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
      
    }
  }

  function delete()
  {
    if($this->allowDelete("project"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      if($project_id>0)
      {
        $P=new project();
        $P->link=$this->link;
        $P->delete($project_id);
      }
    }
  }

  function create()
  {
    if($this->allowCreate("action"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $user_id=$_SESSION['TDEIA_user_id'];
      if($project_id>0 and $user_id>0)
      {
        $P=new project();
        $P->link=$this->link;
        $new_id=$P->create($project_id,$user_id);
        return $new_id;
      }
    }
  }
  
  function updateVariable($cuts)
  {
    if($this->allowUpdate("variable"))
    {
      $variable_id=$_POST['variable_id'];
      $variable=unserialize($_POST['varSerie']);
      $variable->link=$this->link;
      
     if($variable->DB['effect_propierty_id']>0)
      {
        $effect_propierty_id=$variable->DB['effect_propierty_id'];
        $sql="SELECT inputs.id AS ID,minimum,maximum FROM inputs
                        INNER JOIN propierties ON propierties.id=inputs.propierty_id
                        INNER JOIN effect_propierties ON effect_propierties.id=propierties.effect_propierty_id
                        INNER JOIN variables ON variables.effect_propierty_id=effect_propierties.id
                        WHERE effect_propierties.id='".$effect_propierty_id."'";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
          {
            $input_id=$linea['ID'];
            $min=$linea['minimum'];
            $max=$linea['maximum'];
            $IN=new input();
            $IN->link=$this->link;
            $IN->resetInput($input_id,$effect_propierty_id,$min,$max);
          }
        }
      }
      
      // OJO: se pierden los inputs asociados a la variable effect_propierty!!!!!
      
      // aqui se debe borrar la variable
      $sql="DELETE FROM variables WHERE id='".$variable_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      
      // aquí se debe crear la nueva variable que llega serializada
      
      $variable->link=$this->link;
      $variable->writeInDB();
      
      // aquí se debe acualizar el proyecto
      
      $P=new project();
      $P->link=$this->link;
      $P->updateAggregations($project_id,$cuts);
    }
  }

  function displayTitle()
  {
    echo "          <div class=\"edit_project_title\">".$this->text('project_Project')."</div>\n";
  }

  function displayName($project)
  {
    echo "          <div class=\"edit_project_name\">\n";
    echo "            <div class=\"edit_project_name_label\">".$this->text('project_Name')." : </div>\n";
    echo "            <input type=\"text\" class=\"edit_project_name\" name=\"project_name\" id=\"project_name\" value=\"".$project['name']."\"/>\n";
    echo "          </div>\n";
  }
    
  function displayDescription($project)
  {
    if($this->allowRead("project") or $this->allowUpdate("project"))
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
                       window.open('edit_longtext.php?table=projects&col=description&id=".$project['id']."&title=".$project['name']."&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";
      echo "         <input type=\"button\" name=\"project_description\" class=\"edit_project_description\" value=\"".$this->text('project_Description')."\" onClick=\"javascript:description();\">\n";
    }
  }

  function displayHidden()
  {
    echo "         <input type=\"hidden\" name=\"edit_project\" value='true'>\n";
  }
  
  function displaySubmit()
  {
    if($this->allowUpdate("action"))
    {
      echo   "          <input type=\"submit\" name=\"edit_project_submit\" class=\"edit_project_submit\" value=\"".$this->text('project_Update')."\">\n";   
    }
  }
 
  function displayDelete($project)
  {
    if($this->allowDelete("project"))
    {
      echo "  <form method=\"post\" action=\"login.php\">\n";
      echo "    <div class=\"edit_project_delete\">\n";
      $this->displayHidden();
      echo "      <input type=\"submit\" name=\"project_delete\" class=\"edit_project_delete\" value=\"".$this->text('project_Delete')."\" onClick=\"return confirm('".$this->text("project_Delete_confirm")."')\">\n";   
      echo "    </div>\n";
      echo "  </form>\n";
    }
  }
 
  
  function displayNew($project_id)
  {
    if($this->allowCreate("project"))
    {
      echo "  <form method=\"post\" action=\"login.php\">\n";
      echo "    <div class=\"edit_project_new\">\n";
      $this->displayHidden();
      echo "      <input type=\"submit\" name=\"project_new\" class=\"edit_project_new\" value=\"".$this->text('project_New')."\">\n";   
      echo "    </div>\n";
      echo "  </form>\n";
    }
  }

  function display()
  {
    if($this->allowAny("project"))
    {        
      $project_id=$_SESSION['TDEIA_project_id'];
      
      if($project_id<1){return;}
      $project=0;
      $sql="SELECT * FROM projects WHERE id='".$project_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $project=$linea;
      }else
      {
        return;
      }

      echo "    <form method=\"post\" action=\"login.php\">\n";
      
      $this->displayTitle();
      $this->displayName($project);
      $this->displayDescription($project);
      $this->displayHidden();
      $this->displaySubmit();
      $this->displayNew($project);
      $this->displayDelete($project);
 
      echo "    </form>\n";
    }  
  }
}

?>
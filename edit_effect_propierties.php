<?php
require_once('config/config.class.php');
require_once('block.php');
require_once('project.php');
require_once('variable.php');

class edit_effect_propierties extends block
{
  function update($cuts)
  {
    if($this->allowUpdate("effect_propierty"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      
      $effect_propierty_id=$_POST['effect_propierty_id'];
      if($effect_propierty_id<1){return;}

      if(isset($_POST['effect_propierty_name']))
      {
        $col='name';
        $val=htmlspecialchars($_POST['effect_propierty_name'], ENT_QUOTES, 'UTF-8');
        $sql="UPDATE effect_propierties SET ".$col."='".$val."' WHERE id='".$effect_propierty_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        $sql="UPDATE variables SET ".$col."='".$val."' WHERE effect_propierty_id='".$effect_propierty_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }

      $nature=-1;
      if(isset($_POST['effect_propierty_nature']))
      {
        $nature=1;
      }
      $col='nature';
      $val=$nature;
      $sql="UPDATE effect_propierties SET ".$col."='".$val."' WHERE id='".$effect_propierty_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);

      if(isset($_POST['effect_propierty_theta']))
      {
        $col='theta';
        $val=0.0+$_POST['effect_propierty_theta'];
        $sql="UPDATE effect_propierties SET ".$col."='".$val."' WHERE id='".$effect_propierty_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
    }
  }

  function delete()
  {
    if($this->allowDelete("effect_propierty"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      
      $effect_propierty_id=$_POST['effect_propierty_id'];
      if($effect_propierty_id<1){return;}
      
      $sql="DELETE FROM effect_propierties WHERE id='".$effect_propierty_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
  }

  function create($cuts)
  {
    if($this->allowCreate("effect_propierty"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $equation="";
      $name=$this->text('effectpropierty_Without_name');
      $description=$this->text('effectpropierty_Without_description');

      $sql="INSERT INTO effect_propierties(project_id,name,description,nature,weight,theta)
                        VALUES('".$project_id."','".$name."','".$description."','1','0','2')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $effect_propierty_id=mysqli_insert_id($this->link);

      $V=new variable();
      $V->link=$this->link;
      $prefix=$this->text('effectpropierty_Label')." ";
      $min=0.0;$max=1.0;
      $agg_id=0;
      $eff_id=$effect_propierty_id;
      $sets=3;
    
      $V->createInDB($name,$description,$prefix,$min,$max,$agg_id,$eff_id,$sets,$cuts);

      $P=new project();  
      $P->link=$this->link;
      $P->fillPropierties($project_id,$cuts);
      $P->updateAggregations($project_id,$cuts);     
    }
  }

  function loadScheme($cuts)
  {
    if($this->allowDelete("effect_propierty") and $this->allowUpdate("effect_propierty") and $this->allowCreate("effect_propierty"))
    {    
      $project_id=$_SESSION['TDEIA_project_id'];
      $fn=$_POST['effect_propierty_schema_name'];
      $flagDelete=true;
      
      $P=new project();  
      $P->link=$this->link;
      $P->importEffectPropiertiesCSV($project_id,$fn,$flagDelete);
      $P->fillPropierties($project_id,$cuts);
      $P->updateAggregations($project_id,$cuts);
    }
  }
  
  function descriptionScript()
  {
    $conf = new configuration();
    $settings=$conf->readconfig("phpTDEIAconfig.txt");
    $width=$settings['longtextWidth'];
    $height=$settings['longtextHeight'];
    $edit='false';
    if($this->allowUpdate("effect_propierty"))
    {
      $edit='true';
    }
    echo "
                 <script type=\"text/javascript\">
                   function effectpropiertyDescription(effId,effName)
                   {
                     window.open('edit_longtext.php?table=effect_propierties&col=description&id='+effId+'&title='+effName+'&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                   }
                 </script>
         \n";  
  }

  function displayTitle()
  {
    echo "          <td class=\"variable_list_title\" colspan=3>\n";
    echo $this->text('effectpropierty_Effect_propierties')."\n";
    if($this->allowUpdate('propierty'))
    {
      $conf = new configuration();
      $settings=$conf->readconfig("phpTDEIAconfig.txt");
      $width=$settings['propiertyWeightsWidth'];
      $height=$settings['propiertyWeightsHeight'];
      echo "
                   <script type=\"text/javascript\">
                     function weights()
                     {
                       window.open('edit_propierty_weights.php','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";
      echo "            <input type\"button\" name=\"effect_propierty_weight_edit\" class=\"edit_effect_propierty_weight_edit\"  value=\"".$this->text('effectpropierty_Weight_edit')."\" onClick=\"javascript:weights();\">";
    }
    echo "          </td>\n";
  }
  
  function displaySubmit()
  {
    if($this->allowUpdate("effect_propierty"))
    {
      echo "           <div class=\"edit_effect_propierty_submit\">\n";
      echo   "          <input type=\"submit\" name=\"effect_propierty_submit\" class=\"edit_effect_propierty_submit\" value=\"".$this->text('effectpropierty_Update')."\">\n";   
      echo "           </div>\n";
    }
  }
  
  function displayData($effect_propierty)
  {
    echo "          <form method=\"post\" action=\"login.php\">\n";
    echo "           <div class=\"edit_effect_propierty_name\">\n";
    $text=htmlspecialchars($effect_propierty['name'], ENT_QUOTES, 'UTF-8');
    echo "             <div class=\"edit_effect_propierty_name_label\">".$this->text('effectpropierty_Name')."</div>\n";
    echo "             <input class=\"edit_effect_propierty_name\" type=\"text\" name=\"effect_propierty_name\" value=\"".$text."\" />\n";
    echo "           </div>\n";
    
    echo "           <div class=\"edit_effect_propierty_nature\">\n";
    $check="";if($effect_propierty['nature']>0){$check="checked";}
    echo "             <input class=\"edit_effect_propierty_nature\" type=\"checkbox\" name=\"effect_propierty_nature\" ".$check."/>\n";
    echo "             <div class=\"edit_effect_propierty_nature_label\">".$this->text('effectpropierty_Increasing')."</div>\n";
    echo "           </div>\n";
    
    echo "           <div class=\"edit_effect_propierty_description\">\n";
    echo "            <input type=\"button\" name=\"effect_propierty_description\" class=\"edit_effect_propierty_description\" value=\"".$this->text('effectpropierty_Description')."\" onClick=\"javascript:effectpropiertyDescription('".$effect_propierty['id']."','".$effect_propierty['name']."');\">\n";     
    echo "           </div>\n";
    
    echo "           <div class=\"edit_effect_propierty_theta\">\n";
    $text=htmlspecialchars($effect_propierty['theta'], ENT_QUOTES, 'UTF-8');
    echo "             <div class=\"edit_effect_propierty_theta_label\">".$this->text('effectpropierty_Exponent')."</div>\n";
    echo "             <input class=\"edit_effect_propierty_theta\" type=\"text\" name=\"effect_propierty_theta\" value=\"".$text."\" />\n";
    echo "           </div>\n";
    
   $this->displayHidden($effect_propierty);
    $this->displaySubmit();
    echo "          </form>\n";
  }
  
  function displayVariable($variable_id)
  {
    if($variable_id>0)
    {
      $type='variable';
      $subtype='effect_propierty';
      echo "           <img class=\"edit_variable\" src=\"paintvariable.php?varId=".$variable_id."&typeId=".$type."&subType=".$subtype."&filesettings=editPaintSettings.txt\"/>";
//      echo "           <iframe src=\"paintvariable.php?varId=".$variable_id."&typeId=".$type."\"></iframe>\n";
    }
  }


  function displayOptions($effect_propierty,$variable_id)
  {
    echo "           <div class=\"edit_effect_propierty_weight\">\n";
    $weight=number_format($effect_propierty['weight']*100.0,2);
    echo "            ".$this->text('effectpropierty_Weight').": ".$weight."% \n";     
    echo "           </div>\n";

    echo "          <form method=\"post\" action=\"login.php\">\n";
    echo "           <div class=\"edit_effect_propierty_variable_edit\">\n";
    echo "            <input type=\"hidden\" name=\"variable_id\" value=\"".$variable_id."\" >\n";     
    echo "            <input type=\"submit\" name=\"variable_edit\" class=\"edit_effect_propierty_variable_edit\" value=\"".$this->text('effectpropierty_Variable_edit')."\" >\n";     
    echo "           </div>\n";
    echo "          </form>\n";
    
    if($this->allowDelete("effect_propierty"))
    {
      echo "          <form method=\"post\" action=\"login.php\">\n";
      echo "           <div class=\"edit_effect_propierty_delete\">\n";
      echo "            <input type=\"submit\" name=\"effect_propierty_delete\" class=\"edit_effect_propierty_delete\" value=\"".$this->text('effectpropierty_Delete')."\" onClick=\"return confirm('".$this->text("aggregator_Delete_confirm")."')\">\n";     
      echo "           </div>\n";
      $this->displayHidden($effect_propierty);
      echo "          </form>\n";
    }
  }

  function displayEffectPropierty($effect_propierty)
  {
    $variable_id=0;
    $sql="SELECT id FROM variables WHERE effect_propierty_id='".$effect_propierty['id']."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $variable_id=$linea['id'];
    }
    echo "          <td class=\"variable_list_data\">\n";
    $this->displayData($effect_propierty);
    echo "          </td>\n";
    echo "          <td class=\"variable_list_var\">\n";
    $this->displayVariable($variable_id);
    echo "          </td>\n";
    echo "          <td class=\"variable_list_edit_var\">\n";
    $this->displayOptions($effect_propierty,$variable_id);
    echo "          </td>\n";
  }

  function displayHidden($effect_propierty)
  {
    echo "         <input type=\"hidden\" name=\"edit_project\" value='".$_POST['edit_project']."'>\n";
    echo "         <input type=\"hidden\" name=\"effect_propierty_id\" value='".$effect_propierty['id']."'>\n";
  }


  function getSchemes()
  {
    $schemes=array();

    $conf=new configuration();
    $settings=$conf->readconfig('phpTDEIAconfig.txt');
    $dir=$settings['tdeiaDir'];
    
    $schDir=$dir."/schemes/propierties/";
    $len=strlen($schDir);
    $sch=glob($schDir."*.csv");
    foreach($sch as $longname)
    {
      $fn=substr($longname,$len);
      $name=substr($fn,0,strlen($fn)-4);
      $schemes[$longname]=$name;
    }
    return $schemes;
  }
  
  function displayNewEffectPropierty($effect_propierty,$project_id)
  {
    if($this->allowCreate("effect_propierty"))
    {
      echo "          <td class=\"variable_list_new_effect_propierty\">\n";
      echo "          <form method=\"post\" action=\"login.php\">\n";
      echo "           <div class=\"edit_effect_propierty_new\">\n";
      echo "            <input type=\"submit\" name=\"effect_propierty_new_effect_propierty\" class=\"edit_effect_propierty_new\" value=\"".$this->text('effectpropierty_New')."\" >\n";     
      echo "           </div>\n";
      $this->displayHidden($effect_propierty);
      echo "          </form>\n";
      echo "          </td>\n";

      echo "          <td colspan=2>\n";
      echo "          <form method=\"post\" action=\"login.php\">\n";
      echo "           <div class=\"edit_effect_propierty_available_schemes\">".$this->text('effectpropierty_Available_schemes').": </div>\n";
      echo "            <select class=\"edit_effect_propierty_available_schemes\" name=\"effect_propierty_schema_name\">\n";
      $schemes=$this->getSchemes();
      foreach($schemes as $K=>$V)
      {
         echo "                <option value=\"".$K."\" >".$V."</option>\n";       
      }      
      echo "            </select>\n";
      echo "            <input type=\"submit\" name=\"effect_propierty_scheme\" class=\"edit_effect_propierty_scheme\" value=\"".$this->text('effectpropierty_Scheme_load')."\" >\n";     
      $this->displayHidden($effect_propierty);
      echo "          </form>\n";
      echo "          </td>\n";
    }
  }
  
  function display()
  {
    if($this->allowAny("effect_propierty"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      if($project_id > 0)
      {
        $this->descriptionScript();

        echo "    <table class=\"variable_list\">\n";
        echo "      <tr class=\"variable_list\">\n";
        $this->displayTitle();
        echo "      </tr>\n";
        $sql="SELECT * FROM effect_propierties WHERE project_id='".$project_id."'";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
          {
            echo "      <tr class=\"variable_list\">\n";
            $this->displayEffectPropierty($linea);
            echo "      </tr>\n";
          }
        }

        echo "      <tr class=\"variable_list\">\n";
        $this->displayNewEffectPropierty($effect_propierty,$project_id);
        echo "      </tr>\n"; 

        echo "    </table>\n";
      }
    }
  }
}
?>

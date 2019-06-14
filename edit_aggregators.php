<?php
require_once('config/config.class.php');
require_once('block.php');
require_once('project.php');

class edit_aggregators extends block
{
  function update($cuts)
  {
    if($this->allowUpdate("aggregator"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      
      $aggregator_id=$_POST['aggregator_id'];
      if($aggregator_id<1){return;}

      if(isset($_POST['aggregator_name']))
      {
        $col='name';
        $val=htmlspecialchars($_POST['aggregator_name'], ENT_QUOTES, 'UTF-8');
        $sql="UPDATE aggregators SET ".$col."='".$val."' WHERE id='".$aggregator_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        $sql="UPDATE variables SET ".$col."='".$val."' WHERE aggregator_id='".$aggregator_id."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
    
      if(isset($_POST['aggregator_equation']) and $project_id>0)
      {
        $newEq=$_POST['aggregator_equation'];
        $equations=$this->getEquations();
        if(!array_key_exists($newEq,$equations)){return;}

        $oldEq="";
        $sql="SELECT equation AS EQ FROM aggregators WHERE id='".$aggregator_id."'";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
          $oldEq=$linea['EQ'];
        }
        
        if(!($newEq==$oldEq))
        {
          $sql="UPDATE aggregators SET equation='".$newEq."' WHERE id='".$aggregator_id."'";
          mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
          
          $P=new project();  
          $P->link=$this->link;
          $P->updateAggregations($project_id,$cuts);
        }

      }
    }
  }

  function delete()
  {
    if($this->allowDelete("aggregator"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      
      $aggregator_id=$_POST['aggregator_id'];
      if($aggregator_id<1){return;}
      
      $sql="DELETE FROM aggregators WHERE id='".$aggregator_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
  }

  function create($importance,$cuts)
  {
    if($this->allowCreate("aggregator"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      $equation="";
      $name=$this->text('aggregator_Without_name');
      $description=$this->text('aggregator_Without_description');
      $equations=$this->getEquations();
      if(count($equations)<1){return;}
      $cnt=0;
      foreach($equations as $K=>$V)
      {
        if($cnt>0){continue;}
        $equation=$K;
        $name=$V;
        $cnt++;
      }

      $sql="INSERT INTO aggregators(project_id,name,description,importance,equation)
                        VALUES('".$project_id."','".$name."','".$description."','".$importance."','".$equation."')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $aggregator_id=mysqli_insert_id($this->link);

      $V=new variable();
      $V->link=$this->link;
      $prefix=$this->text('aggregator_Label')." ";
      $min=0.0;$max=1.0;
      $agg_id=$aggregator_id;
      $eff_id=0;
      $sets=3;
    
      $V->createInDB($name,$description,$prefix,$min,$max,$agg_id,$eff_id,$sets,$cuts);
      $P=new project();  
      $P->link=$this->link;
      $P->updateAggregations($project_id,$cuts);
    }
  }
  
  function loadScheme($cuts)
  {
    if($this->allowDelete("aggregator") and $this->allowUpdate("aggregator") and $this->allowCreate("aggregator"))
    {    
      $project_id=$_SESSION['TDEIA_project_id'];
      $fn=$_POST['aggregator_schema_name'];
      $flagDelete=true;

      $P=new project();  
      $P->link=$this->link;
      $P->importAggregatorsCSV($project_id,$fn,$flagDelete);   
      $P->updateAggregations($project_id,$cuts);
    }
  }
  
  function getEquations()
  {
    $eq=array();
    $eq['simple_average']  =$this->text('aggregator_Simple_average');
    $eq['weighted_average']=$this->text('aggregator_Weighted_average');
    $eq['maximum']         =$this->text('aggregator_Maximum');
    $eq['minimum']         =$this->text('aggregator_Minimum');
    return $eq;
  }

  function descriptionScript()
  {
    $conf = new configuration();
    $settings=$conf->readconfig("phpTDEIAconfig.txt");
    $width=$settings['longtextWidth'];
    $height=$settings['longtextHeight'];
    $edit='false';
    if($this->allowUpdate("aggregator"))
    {
      $edit='true';
    }
    echo "
                 <script type=\"text/javascript\">
                   function aggregatorDescription(aggId,aggName)
                   {
                     window.open('edit_longtext.php?table=aggregators&col=description&id='+aggId+'&title='+aggName+'&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                   }
                 </script>
         \n";  
  }

  function displayTitle($title)
  {
    echo "          <td class=\"variable_list_title\" colspan=3>".$title."</td>\n";
  }
  
  function displayEquation($aggregator)
  {
    if($aggregator['importance']==1)
    {
      echo "             <div class=\"edit_aggregator_equation_name\">".$this->text('aggregator_Linear_combination')."</div>\n";
    }else
    {
      echo "             <select class=\"edit_aggregator_equation\" name=\"aggregator_equation\" >\n";
      $equations=$this->getEquations();
      foreach($equations as $K=>$V)
      {
        $sel="";if($K==$aggregator['equation']){$sel="selected";}
        echo "                <option value=\"".$K."\" ".$sel." >".$V."</option>\n";
      }
      echo "             </select>\n";
    }
  }

  function displayHidden($aggregator)
  {
    echo "         <input type=\"hidden\" name=\"edit_project\" value='".$_POST['edit_project']."'>\n";
    echo "         <input type=\"hidden\" name=\"aggregator_id\" value='".$aggregator['id']."'>\n";
  }

  function displaySubmit()
  {
    if($this->allowUpdate("aggregator"))
    {
      echo "           <div class=\"edit_aggregator_submit\">\n";
      echo   "          <input type=\"submit\" name=\"aggregator_submit\" class=\"edit_aggregator_submit\" value=\"".$this->text('aggregator_Update')."\">\n";   
      echo "           </div>\n";
    }
  }
  
  function displayData($aggregator)
  {
    echo "          <form method=\"post\" action=\"login.php\">\n";
    echo "           <div class=\"edit_aggregator_name\">\n";
    $text=htmlspecialchars($aggregator['name'], ENT_QUOTES, 'UTF-8');
    echo "             <div class=\"edit_aggregator_name_label\">".$this->text('aggregator_Name')."</div>\n";
    echo "             <input class=\"edit_aggregator_name\" type=\"text\" name=\"aggregator_name\" value=\"".$text."\" />\n";
    echo "           </div>\n";
    
    echo "           <div class=\"edit_aggregator_equation\">\n";
    echo "             <div class=\"edit_aggregator_equation_label\">".$this->text('aggregator_Equation')."</div>\n";
    $this->displayEquation($aggregator);
    echo "           </div>\n";
    
    echo "           <div class=\"edit_aggregator_description\">\n";
    echo "            <input type=\"button\" name=\"aggregator_description\" class=\"edit_aggregator_description\" value=\"".$this->text('aggregator_Description')."\" onClick=\"javascript:aggregatorDescription('".$aggregator['id']."','".$aggregator['name']."');\">\n";     
    echo "           </div>\n";
    
    $this->displayHidden($aggregator);
    $this->displaySubmit();
    echo "          </form>\n";
  }
  
  function displayVariable($variable_id,$importance)
  {
    if($variable_id>0)
    {
      $type='variable';
      $subtype='aggregator';

      if($importance>0)
      {
        $subtype='importance';
      }
      echo "           <img class=\"edit_variable\" src=\"paintvariable.php?varId=".$variable_id."&typeId=".$type."&subType=".$subtype."&filesettings=editPaintSettings.txt\"/>";
//      echo "           <iframe src=\"paintvariable.php?varId=".$variable_id."&typeId=".$type."\"></iframe>\n";
    }
  }
  
  function displayOptions($aggregator,$variable_id)
  {
    echo "          <form method=\"post\" action=\"login.php\">\n";
    echo "           <div class=\"edit_aggregator_variable_edit\">\n";
    echo "            <input type=\"hidden\" name=\"variable_id\" value=\"".$variable_id."\" >\n";     
    echo "            <input type=\"submit\" name=\"variable_edit\" class=\"edit_aggregator_variable_edit\" value=\"".$this->text('aggregator_Variable_edit')."\" >\n";     
    echo "           </div>\n";
    echo "          </form>\n";
    
    if($aggregator['importance']==0 and $this->allowDelete("aggregator"))
    {
      echo "          <form method=\"post\" action=\"login.php\">\n";
      echo "           <div class=\"edit_aggregator_delete\">\n";
      echo "            <input type=\"submit\" name=\"aggregator_delete\" class=\"edit_aggregator_delete\" value=\"".$this->text('aggregator_Delete')."\" onClick=\"return confirm('".$this->text("aggregator_Delete_confirm")."')\">\n";     
      echo "           </div>\n";
      $this->displayHidden($aggregator);
      echo "          </form>\n";
    }
  }
  
  function displayAggregator($aggregator)
  {
    $variable_id=0;
    $sql="SELECT id FROM variables WHERE aggregator_id='".$aggregator['id']."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $variable_id=$linea['id'];
    }
    echo "          <td class=\"variable_list_data\">\n";
    $this->displayData($aggregator);
    echo "          </td>\n";
    echo "          <td class=\"variable_list_var\">\n";
    $this->displayVariable($variable_id,$aggregator['importance']);
    echo "          </td>\n";
    echo "          <td class=\"variable_list_edit_var\">\n";
    $this->displayOptions($aggregator,$variable_id);
    echo "          </td>\n";
  }
  
  function displayNewImportance($project_id)
  {
    if($this->allowCreate("aggregator"))
    {
      echo "          <td class=\"variable_list_new_importance\">\n";
      echo "          <form method=\"post\" action=\"login.php\">\n";
      echo "           <div class=\"edit_aggregator_new\">\n";
      echo "            <input type=\"submit\" name=\"aggregator_new_importance\" class=\"edit_aggregator_new\" value=\"".$this->text('aggregator_New')."\" >\n";     
      echo "           </div>\n";
      $this->displayHidden($aggregator);
      echo "          </form>\n";
      echo "          </td>\n";
      echo "          <td></td><td></td>\n";
    }
  }

  function getSchemes()
  {
    $schemes=array();

    $conf=new configuration();
    $settings=$conf->readconfig('phpTDEIAconfig.txt');
    $dir=$settings['tdeiaDir'];
    
    $schDir=$dir."/schemes/aggregators/";
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
  
  function displayNewAggregator($aggregator,$project_id)
  {
    if($this->allowCreate("aggregator"))
    {
      echo "          <td class=\"variable_list_new_importance\">\n";
      echo "          <form method=\"post\" action=\"login.php\">\n";
      echo "           <div class=\"edit_aggregator_new\">\n";
      echo "            <input type=\"submit\" name=\"aggregator_new_aggregator\" class=\"edit_aggregator_new\" value=\"".$this->text('aggregator_New')."\" >\n";     
      echo "           </div>\n";
      $this->displayHidden($aggregator);
      echo "          </form>\n";
      echo "          </td>\n";

      echo "          <td colspan=2>\n";
      echo "          <form method=\"post\" action=\"login.php\">\n";
      echo "           <div class=\"edit_aggregator_available_schemes\">".$this->text('aggregator_Available_schemes').": </div>\n";
      echo "            <select class=\"edit_aggregator_available_schemes\" name=\"aggregator_schema_name\">\n";
      $schemes=$this->getSchemes();
      foreach($schemes as $K=>$V)
      {
         echo "                <option value=\"".$K."\" >".$V."</option>\n";       
      }      
      echo "            </select>\n";
      echo "            <input type=\"submit\" name=\"aggregator_scheme\" class=\"edit_aggregator_scheme\" value=\"".$this->text('aggregator_Scheme_load')."\" >\n";     
      $this->displayHidden($aggregator);
      echo "          </form>\n";
      echo "          </td>\n";
    }
  }

  function display()
  {
    if($this->allowAny("aggregator"))
    {
      $project_id=$_SESSION['TDEIA_project_id'];
      if($project_id > 0)
      {
        $this->descriptionScript();
      
        echo "    <table class=\"variable_list\">\n";

        echo "      <tr class=\"variable_list\">\n";
        $this->displayTitle($this->text('aggregator_Importance'));
        echo "      </tr>\n";

        $sql="SELECT * FROM aggregators WHERE project_id='".$project_id."' AND importance=1";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
          {
            echo "      <tr class=\"variable_list\">\n";
            $this->displayAggregator($linea);
            echo "      </tr>\n";
          }
        }else
        {
            echo "      <tr class=\"variable_list\">\n";
            $this->displayNewImportance($project_id);
            echo "      </tr>\n";        
        }

        echo "      <tr class=\"variable_list\">\n";
        $this->displayTitle($this->text('aggregator_Information_aggregators'));
        echo "      </tr>\n";

        $sql="SELECT * FROM aggregators WHERE project_id='".$project_id."' AND importance=0";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
          {
            echo "      <tr class=\"variable_list\">\n";
            $this->displayAggregator($linea);
            echo "      </tr>\n";
          }
        }
        echo "      <tr class=\"variable_list\">\n";
        $this->displayNewAggregator($aggregator,$project_id);
        echo "      </tr>\n"; 
               
        echo "    </table>\n";
      }
    }
  }
}
?>

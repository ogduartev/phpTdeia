<?php
require_once('block.php');
require_once('variable.php');
require_once('project.php');

class edit_variable extends block
{
  function updateDeleteSet($variable)
  {
    if(isset($_POST['var_edit_delete_set']))
    {
      $setid=$_POST['set_id'];
      $setcnt=0;
      foreach($variable->sets as $K=>$set)
      {
        $setcnt++;
        if($setcnt==$setid)
        {
          unset($variable->sets[$K]);
        }
      }
    }  
    return $variable;  
  }

  function updateUpdateSet($variable)
  {
    if(isset($_POST['var_edit_update_set']))
    {
      $setid=$_POST['set_id'];
      $setcnt=0;
      $sets=array();
      foreach($variable->sets as $K=>$set)
      {
        $setcnt++;
        if($setcnt==$setid)
        {
          $FN=new fuzzy_number();
          $FN->link=$this->link;
          $a=$_POST['set_a'];$b=$_POST['set_b'];$c=$_POST['set_c'];$d=$_POST['set_d'];
          $FN->trapezoid($a,$b,$c,$d);
          $label=$_POST['set_label'];
          $sets[$label]=$FN;
        }else
        {
          $sets[$K]=$set;
        }
        $variable->sets=$sets;
      }
    }  
    return $variable;  
  }

  function updateNewSet($variable)
  {
    if(isset($_POST['var_edit_new_set']))
    {
      $min=$variable->DB['minimum'];
      $max=$variable->DB['maximum'];
      $FN=new fuzzy_number();
      $FN->link=$this->link;
      $FN->trapezoid($min,$min,$max,$max);
      $label=$this->text('variable_Label')." ".(count($variable->sets)+1);
      $variable->sets[$label]=$FN;    
    }
    return $variable;  
  }

  function updateAutodefine($variable)
  {
    if(isset($_POST['edit_variable_autodefine']))
    {
      $numsets=$_POST['edit_variable_autodefine_num'];
      if($numsets<2){$numsets=2;}
      $prefix=$this->text('variable_Label')." ";
      $variable->autodefine($numsets,$prefix,2);
    }
    return $variable;
  }
  
  function updateLimits($variable)
  {
    if(isset($_POST['edit_variable_update_limits']))
    {
      $min=$_POST['edit_variable_minimum'];
      $max=$_POST['edit_variable_maximum'];
      if($max<$min){$max=2*$min;}
      $variable->changeLimits($min,$max);
    }
    return $variable;
  }
  
  function update($variable)
  {
    $variable=$this->updateDeleteSet($variable);
    $variable=$this->updateUpdateSet($variable);
    $variable=$this->updateNewSet($variable);
    $variable=$this->updateAutodefine($variable);
    $variable=$this->updateLimits($variable);
    return $variable;
  }
  
  function displayDescription($variable_id,$title)
  {
    if($this->allowRead("variable") or $this->allowUpdate("variable"))
    {
      $conf = new configuration();
      $settings=$conf->readconfig("phpTDEIAconfig.txt");
      $width=$settings['longtextWidth'];
      $height=$settings['longtextHeight'];
      $edit='false';
      if($this->allowUpdate("variable"))
      {
        $edit='true';
      }
      echo "
                   <script type=\"text/javascript\">
                     function description()
                     {
                       window.open('edit_longtext.php?table=variables&col=description&id=".$variable_id."&title=".$title."&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";
      echo "         <input type=\"button\" name=\"variable_description\" class=\"edit_variable_description\" value=\"".$this->text('variable_Description')."\" onClick=\"javascript:description();\">\n";   
    }
  }
  
  function displayData($variable)
  {
    echo "        <div class=\"edit_variable_data\">\n";
    echo "           <div class=\"edit_variable_data_title\">".$variable->DB['name']."</div>";
    echo "           <form method=\"post\" action=\"login.php\">\n";
    echo "           <div class=\"edit_variable_data_limits\">".$this->text('variable_Limits')." : ";
    echo "            <input type=\"text\" class=\"edit_variable_min\" name=\"edit_variable_minimum\" value=\"".$variable->DB['minimum']."\" />";
    echo "            <input type=\"text\" class=\"edit_variable_min\" name=\"edit_variable_maximum\" value=\"".$variable->DB['maximum']."\" />";
    echo "            <input type=\"submit\" class=\"edit_variable_update_limits\" name=\"edit_variable_update_limits\" value=\"".$this->text('variable_Update')."\"/>\n";
    echo "           </div>\n";
    $this->displayHidden($variable);
    echo "           </form>\n";
    echo "           <form method=\"post\" action=\"login.php\">\n";
    echo "           <div class=\"edit_variable_data_autodefine\">".$this->text('variable_Number_of_labels')." : ";
    echo "            <input type=\"number\"  class=\"edit_variable_numsets\" min=\"2\" max=\"15\" name=\"edit_variable_autodefine_num\" value=\"".count($variable->sets)."\"/>\n";
    echo "            <input type=\"submit\"  class=\"edit_variable_autodefine\" name=\"edit_variable_autodefine\" value=\"".$this->text('variable_Autodefine')."\"/>\n";
    $this->displayHidden($variable);
    echo "           </div>\n";
    echo "           </form>\n";
    echo "           <div class=\"edit_variable_data_description\">";
    $this->displayDescription($variable->DB['id'],$variable->DB['name']);
    echo "           </div>\n";
    echo "        </div>\n";
  }

  function displayImage($variable)
  {
    $type="serialized";
    $subtype="";
    if($variable->DB['effect_propierty_id']>0)
    {
      $subtype="effect_propierty";
    }else
    {
      $sql="SELECT * FROM aggregators WHERE id='".$variable->DB['aggregator_id']."' AND importance=1";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $subtype="importance";
      }else
      {
        $subtype="aggregator";
      }
    }
    $serie=serialize($variable);
    echo "        <div class=\"edit_variable_showvar\">\n";
    echo "           <img class=\"showvar\" src='paintvariable.php?typeId=".$type."&subType=".$subtype."&filesettings=paintSettings.txt&varSerie=".$serie."'/>";
    $this->displayReturn($variable);
    echo "        </div>\n";
  }

  function displaySet($variable,$label,$set,$set_id)
  {
    $min=$variable->DB['minimum'];
    $max=$variable->DB['maximum'];
    $step="any";
    $step=($max-$min)/1000;
    $L0=number_format($set->L(0),3,".",".");
    $L1=number_format($set->L(1),3,".",".");
    $R0=number_format($set->R(0),3,".",".");
    $R1=number_format($set->R(1),3,".",".");
    echo "           <form method=\"post\" action=\"login.php\">\n";
    echo "            <td class=\"edit_variable_label\"><input class=\"edit_variable_label\" type=\"text\" name=\"set_label\" value=\"".$label."\" ></td>\n";
    echo "            <td class=\"edit_variable_abcd\"><input class=\"edit_variable_abcd\" type=\"number\" name=\"set_a\" value=\"".$L0."\" min=\"".$min."\" max=\"".$max."\" step=\"".$step."\"></td>\n";
    echo "            <td class=\"edit_variable_abcd\"><input class=\"edit_variable_abcd\" type=\"number\" name=\"set_b\" value=\"".$L1."\" min=\"".$min."\" max=\"".$max."\" step=\"".$step."\"></td>\n";
    echo "            <td class=\"edit_variable_abcd\"><input class=\"edit_variable_abcd\" type=\"number\" name=\"set_c\" value=\"".$R1."\" min=\"".$min."\" max=\"".$max."\" step=\"".$step."\"></td>\n";
    echo "            <td class=\"edit_variable_abcd\"><input class=\"edit_variable_abcd\" type=\"number\" name=\"set_d\" value=\"".$R0."\" min=\"".$min."\" max=\"".$max."\" step=\"".$step."\"></td>\n";
    echo "            <td><input class=\"edit_variable_set\" type=\"submit\" name=\"var_edit_update_set\" value=\"".$this->text('variable_Update')."\"/></td>\n";
    echo "            <td><input class=\"edit_variable_set\" type=\"submit\" name=\"var_edit_delete_set\" value=\"".$this->text('variable_Delete')."\"/></td>\n";
    echo "            <td><input class=\"edit_variable_set\" type=\"hidden\" name=\"set_id\" value=\"".$set_id."\"/></td>\n";
    $this->displayHidden($variable);
    echo "           </form>\n";
  }

  function displaySets($variable)
  {
    echo "        <div class=\"edit_variable_sets\">\n";
    echo "          <table>\n";
    echo "            <tr>\n";
    echo "            <td class=\"edit_variable_label\">".$this->text('variable_Label')."</td>\n";
    echo "            <td class=\"edit_variable_abcd\">a</td>\n";
    echo "            <td class=\"edit_variable_abcd\">b</td>\n";
    echo "            <td class=\"edit_variable_abcd\">c</td>\n";
    echo "            <td class=\"edit_variable_abcd\">d</td>\n";
    echo "            <td class=\"edit_variable_abcd\"></td>\n";
    echo "            <td class=\"edit_variable_abcd\"></td>\n";
    echo "            </tr>\n";
    $setcnt=0;
    foreach($variable->sets as $label=>$set)
    {
      $setcnt++;
      echo "            <tr>\n";
      $this->displaySet($variable,$label,$set,$setcnt);
      echo "            </tr>\n";
    }
    echo "            <tr>\n";
    echo "           <form method=\"post\" action=\"login.php\">\n";
    echo "            <td><input class=\"edit_variable_set\" name=\"var_edit_new_set\" type=\"submit\" value=\"".$this->text('variable_New')."\"/></td><td colspan='5'></td>\n";
    $this->displayHidden($variable);
    echo "           </form>\n";
    echo "            </tr>\n";
    echo "          </table>\n";
    echo "        </div>\n";
  }

  function displayHidden($variable)
  {
    $serie=serialize($variable);
    echo "           <input type=\"hidden\" name=\"variable_edit\" value=\"true\"/>\n";
    echo "           <input type=\"hidden\" name=\"varSerie\" value='".$serie."'/>\n";
  }
  
  function displayReturn($variable)
  {
    if($this->allowUpdate('variable'))
    {
      echo "        <div class=\"edit_variable_return\">\n";
      echo "           <form method=\"post\" action=\"login.php\">\n";
      echo "            <input type=\"hidden\" name=\"variable_id\" value=\"".$variable->DB['id']."\"/>\n";
      echo "            <input type=\"submit\" class=\"edit_variable_return\" name=\"edit_variable_return\" value=\"".$this->text('variable_Save_in_database')."\"/>\n";
      $this->displayHidden($variable);
      echo "           </form>\n";
      echo "        </div>\n";
    }
  }

  function display()
  {
    if($this->allowAny('variable'))
    {
      $variable=0;
      if(isset($_POST['variable_id']))
      {
        $variable_id=$_POST['variable_id'];
        if($variable_id<1){return;}
        $variable=new variable();
        $variable->link=$this->link;
        $sets=$variable->readDB($variable_id);
      }elseif(isset($_POST['varSerie']))
      {
        $serie=$_POST['varSerie'];
        $variable=unserialize($serie);
        $variable->link=$this->link;
        $variable=$this->update($variable);
      }
      if($variable==0){return;}
      
      $this->displayData($variable);
      $this->displayImage($variable);
      $this->displaySets($variable);
//      $this->displayReturn($variable);
    }
  }
}

?>

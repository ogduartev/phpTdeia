<?php
require_once('config/config.class.php');
require_once('block.php');
require_once('variable.php');
require_once('importance.php');

class edit_input extends block
{

  function updateDB($var,$input_id)
  {
    if(isset($_POST['input_description']))
    {
      $col='description';
      $val=htmlspecialchars($_POST['input_description'], ENT_QUOTES, 'UTF-8');
      $sql="UPDATE inputs SET ".$col."='".$val."' WHERE id='".$input_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }

    if(isset($_POST['input_type']))
    {
      $col='type';
      $IN=new input();
      $IN->link=$this->link;
      $valA=array_keys($IN->cases,$_POST['input_type']);
      $val=0;if(isset($valA[0])){$val=$valA[0];}
      $sql="UPDATE inputs SET ".$col."='".$val."' WHERE id='".$input_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }

    if(isset($_POST['input_crisp']))
    {
      $col='crisp';
      $val=$_POST['input_crisp'];
      $sql="UPDATE inputs SET ".$col."='".$val."' WHERE id='".$input_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }

    if(isset($_POST['input_interval_min']) and isset($_POST['input_interval_max']))
    {
      $L=$_POST['input_interval_min'];
      $R=$_POST['input_interval_max'];
      if($R<$L){$R=$L;}

      $sql="UPDATE inputs SET L='".$L."', R='".$R."' WHERE id='".$input_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }

    if(isset($_POST['input_modifier']))
    {
      $col='modifier';
      $val=$_POST['input_modifier'];
      $sql="UPDATE inputs SET ".$col."='".$val."' WHERE id='".$input_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }

    if(isset($_POST['input_label']))
    {
      $col='set_id';
      $val=$_POST['input_label'];
      $sql="UPDATE inputs SET ".$col."='".$val."' WHERE id='".$input_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
    
    if(isset($_POST['input_fuzzy_a']) and isset($_POST['input_fuzzy_b']) and isset($_POST['input_fuzzy_c']) and isset($_POST['input_fuzzy_d']))
    {
      $FN=new fuzzy_number();
      $FN->link=$this->link;
      $a=$_POST['input_fuzzy_a'];
      $b=$_POST['input_fuzzy_b'];
      $c=$_POST['input_fuzzy_c'];
      $d=$_POST['input_fuzzy_d'];
      $FN->trapezoid($a,$b,$c,$d);
      $FN->write('input_cuts','input_id',$input_id);
    }
    
    // UPDATE importances
    $IM=new importance();
    $IM->link=$this->link;
    $project_id=$_SESSION['TDEIA_project_id'];
    $effect_id=0;
    $cuts=2;
    $propierty_id=$_POST['varId'];
    $sql="SELECT effect_id FROM effect_propierties
                           INNER JOIN propierties ON propierties.effect_propierty_id=effect_propierties.id
                           INNER JOIN effects ON propierties.effect_id=effects.id
                           INNER JOIN factors ON effects.factor_id=factors.id
                           INNER JOIN actions ON effects.action_id=actions.id
                           WHERE propierties.id='".$propierty_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $effect_id=$linea['effect_id'];
    }
    
    if($project_id>0 and $effect_id>0 and $cuts>0)
    {
      $IM->insert_single_importance($project_id,$effect_id,$cuts);
    }
    
  }

  function update()
  {
    if($this->allowUpdate("propierty"))
    {    
      $typeStr=$_POST['matrixType'];
      $pos=strpos($typeStr,"-");
      $type=substr($typeStr,0,$pos);
      $aggregation_id=substr($typeStr,$pos+1);
      if($aggregation_id==0){$type='empty';}
      $propierty_id=$_POST['varId'];
    
      if($propierty_id==0){$propierty_id=$_POST['varId'];}
      $sql="SELECT variables.id AS Vid,inputs.id AS INid,factors.name AS FN,actions.name as AN FROM effect_propierties
                             INNER JOIN variables ON variables.effect_propierty_id=effect_propierties.id
                             INNER JOIN propierties ON propierties.effect_propierty_id=effect_propierties.id
                             INNER JOIN inputs ON inputs.propierty_id=propierties.id
                             INNER JOIN effects ON propierties.effect_id=effects.id
                             INNER JOIN factors ON effects.factor_id=factors.id
                             INNER JOIN actions ON effects.action_id=actions.id
                             WHERE propierties.id='".$propierty_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $var=new variable();
        $var->link=$this->link;
        $var->readDB($linea['Vid']);
        $title=$var->DB['name']." (".$linea['FN']." - ".$linea['AN'].")";
        $this->updateDB($var,$linea['INid']);
      }
    }
  }

  function displayDescription($input_id,$title)
  {
    if($this->allowRead("propierty") or $this->allowUpdate("propierty"))
    {
      $conf = new configuration();
      $settings=$conf->readconfig("phpTDEIAconfig.txt");
      $width=$settings['longtextWidth'];
      $height=$settings['longtextHeight'];
      $edit='false';
      if($this->allowUpdate("propierty"))
      {
        $edit='true';
      }
      echo "
                   <script type=\"text/javascript\">
                     function description()
                     {
                       window.open('edit_longtext.php?table=inputs&col=description&id=".$input_id."&title=".$title."&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";
      echo "         <input type=\"button\" name=\"input_description\" class=\"edit_input_description\" value=\"".$this->text('input_Description')."\" onClick=\"javascript:description();\">\n";   
    }
  }
  
  function displayType($input_id)
  {
    echo "
                <script type=\"text/javascript\" >
                  function inputTypeChange()
                  {
                    var sel         = document.getElementById(\"sel_input_type\");
                    var divCrisp    = document.getElementById(\"div_edit_input_crisp\");
                    var divInterval = document.getElementById(\"div_edit_input_interval\");
                    var divLabel    = document.getElementById(\"div_edit_input_label\");
                    var divFuzzy    = document.getElementById(\"div_edit_input_fuzzy\");
                    divCrisp.style.display=\"none\";
                    divInterval.style.display=\"none\";
                    divLabel.style.display=\"none\";
                    divFuzzy.style.display=\"none\";
                    var type=sel.value;
                    switch(type)
                    {
                      case 'crisp' :
                                    divCrisp.style.display=\"inline\";
                                    break;
                      case 'interval' :
                                    divInterval.style.display=\"inline\";
                                    break;
                      case 'label' :
                                    divLabel.style.display=\"inline\";
                                    break;
                      case 'fuzzy_number' :
                                    divFuzzy.style.display=\"inline\";
                                    break;
                      default :
                                    break;
                    }
                  }
                </script>
    \n";
    $IN=new input();
    $IN->link=$this->link;
    $type=$IN->getTypeStr($input_id);
    $sel="";if($type=='crisp'){$sel='selected';}
    echo "          <div class=\"edit_input_type\">\n";
    echo "            <div class=\"edit_input_type_label\">".$this->text('input_Type')." : </div>\n";
    echo "            <select name=\"input_type\" id=\"sel_input_type\" class=\"edit_input_type\" onChange=\"javascript:inputTypeChange();\">\n";
    echo "              <option value=\"crisp\" ".$sel." >".$this->text('input_Crisp')."</option>\n";
    $sel="";if($type=='interval'){$sel='selected';}
    echo "              <option value=\"interval\" ".$sel." >".$this->text('input_Interval')."</option>\n";
    $sel="";if($type=='label'){$sel='selected';}
    echo "              <option value=\"label\" ".$sel." >".$this->text('input_Label')."</option>\n";
    $sel="";if($type=='fuzzy_number'){$sel='selected';}
    echo "              <option value=\"fuzzy_number\" ".$sel." >".$this->text('input_Fuzzy_number')."</option>\n";
    echo "            </select>\n";
    echo "          </div>\n";
   }
  
  function displayCrisp($var,$input_id)
  {
    $IN=new input();
    $IN->link=$this->link;
    $type=$IN->gettypeStr($input_id);
    $display="none";if($type=='crisp'){$display='inline';}
    echo "          <div class=\"edit_input_crisp\"  id=\"div_edit_input_crisp\" style=\"display:".$display."\">\n";
    echo "            <div class=\"edit_input_crisp_label\">".$this->text('input_Crisp')." : </div>\n";
    echo "            <input type=\"number\" class=\"edit_input_crisp\" name=\"input_crisp\" min=\"".$var->DB['minimum']."\" max=\"".$var->DB['maximum']."\" step=\"0.01\" value=\"".$IN->getCrisp($input_id)."\">\n";
    echo "          </div>\n";
  }
  
  function displayInterval($var,$input_id)
  {
    $IN=new input();
    $IN->link=$this->link;
    $type=$IN->getTypeStr($input_id);
    $display="none";if($type=='interval'){$display='inline';}
    echo "          <div class=\"edit_input_interval\" id=\"div_edit_input_interval\" style=\"display:".$display."\">\n";
    echo "            <div class=\"edit_input_interval_label\">".$this->text('input_Interval')." : </div>\n";
    echo "            <input type=\"number\" class=\"edit_input_interval_L\" name=\"input_interval_min\" min=\"".$var->DB['minimum']."\" max=\"".$var->DB['maximum']."\" step=\"0.01\" value=\"".$IN->getL($input_id)."\"/>\n";
    echo "            <input type=\"number\" class=\"edit_input_interval_R\" name=\"input_interval_max\" min=\"".$var->DB['minimum']."\" max=\"".$var->DB['maximum']."\" step=\"0.01\" value=\"".$IN->getR($input_id)."\"/>\n";
    echo "          </div>\n";
  }
  
  function displayLabel($var,$input_id)
  {
    $IN=new input();
    $IN->link=$this->link;
    $type=$IN->getTypeStr($input_id);
    $display="none";if($type=='label'){$display='inline';}
    
    echo "          <div class=\"edit_input_label\" id=\"div_edit_input_label\" style=\"display:".$display."\">\n";
    echo "            <div class=\"edit_input_label_label\">".$this->text('input_Label')." : </div>\n";
    echo "            <select name=\"input_label\" id=\"sel_input_label\" class=\"edit_input_label\">\n";
    $labels=$var->variable_labels($var->DB['id']);
    foreach($labels as $K=>$V)
    {
      $sel="";if($K==$IN->getset_id($input_id)){$sel="selected=\"selected\"";}
      echo "              <option value=\"".$K."\" ".$sel." >".$V."\n";
    }
    echo "            </select>\n";
    
    echo "            <div class=\"edit_input_modifier_label\">".$this->text('input_Modifier')." : </div>\n";
    
    $FN=new fuzzy_number();
    $FN->link=$this->link;
    echo "            <select name=\"input_modifier\" id=\"sel_input_modifier\" class=\"edit_input_modifier\">\n";
    foreach($FN->modifiers as $K=>$V)
    {
      $sel="";if($K==$IN->getmodifier($input_id)){$sel="selected=\"selected\"";}
      echo "              <option value=\"".$K."\" ".$sel." >".$FN->text("input_".$V)."\n";
    }
    echo "            </select>\n";
    echo "          </div>\n";
  }
  
  function displayFuzzyNumber($var,$input_id)
  {
    $IN=new input();
    $IN->link=$this->link;
    $type=$IN->getTypeStr($input_id);
    $display="none";if($type=='fuzzy_number'){$display='inline';}
    echo "          <div class=\"edit_input_fuzzy\" id=\"div_edit_input_fuzzy\" style=\"display:".$display."\">\n";
    echo "            <div class=\"edit_input_fuzzy_label\">".$this->text('input_Fuzzy_trapezoid')."</div>\n";
    $FNdef=$IN->getFNdef($input_id);
    $LR0=$FNdef->LR(0.0); 
    $LR1=$FNdef->LR(1.0);
    $a=$LR0['L']; 
    $b=$LR1['L']; 
    $c=$LR1['R']; 
    $d=$LR0['R']; 
    echo "            <input type=\"number\" class=\"edit_input_fuzzy_a\" name=\"input_fuzzy_a\" min=\"".$var->DB['minimum']."\" max=\"".$var->DB['maximum']."\" step=\"0.01\" value=\"".$a."\">\n";
    echo "            <input type=\"number\" class=\"edit_input_fuzzy_b\" name=\"input_fuzzy_b\" min=\"".$var->DB['minimum']."\" max=\"".$var->DB['maximum']."\" step=\"0.01\" value=\"".$b."\">\n";
    echo "            <input type=\"number\" class=\"edit_input_fuzzy_c\" name=\"input_fuzzy_c\" min=\"".$var->DB['minimum']."\" max=\"".$var->DB['maximum']."\" step=\"0.01\" value=\"".$c."\">\n";
    echo "            <input type=\"number\" class=\"edit_input_fuzzy_d\" name=\"input_fuzzy_d\" min=\"".$var->DB['minimum']."\" max=\"".$var->DB['maximum']."\" step=\"0.01\" value=\"".$d."\">\n";
    echo "          </div>\n";
  }
  
  function displayHidden($input_id)
  {
    echo "         <input type=\"hidden\" name=\"actionLevel\"  value='".$_POST['actionLevel']."'>\n";
    echo "         <input type=\"hidden\" name=\"factorLevel\" value='".$_POST['factorLevel']."'>\n";
    echo "         <input type=\"hidden\" name=\"matrixType\" value='".$_POST['matrixType']."'>\n";
    echo "         <input type=\"hidden\" name=\"cellType\" value='".$_POST['cellType']."'>\n";
    echo "         <input type=\"hidden\" name=\"varId\" value='".$_POST['varId']."'>\n";
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_factor_")
      {
        echo "           <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_action_")
      {
        echo "           <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
  }
  
  function displaySubmit()
  {
    if($this->allowUpdate("propierty"))
    {
      echo   "          <input type=\"submit\" name=\"input_submit\" class=\"edit_input_submit\" value=\"".$this->text('input_Update')."\">\n";   
    }
  }
  
  function displayInput($title,$var,$input_id,$propierty_id)
  {
    echo "        <div class=\"edit_input_title\">".$title."</div>\n";
    echo "        <form method=\"post\" action=\"login.php\">\n";
    $this->displayType($input_id);
    $this->displayCrisp($var,$input_id);
    $this->displayInterval($var,$input_id);
    $this->displayLabel($var,$input_id);
    $this->displayFuzzyNumber($var,$input_id);   
    $this->displayHidden($propierty_id);   
    $this->displaySubmit();   
    
    echo "        </form>\n";
    echo "        <form method=\"post\" action=\"edit_longtext.php\" target=\"_new\">\n";
    $this->displayDescription($input_id,$title);
    echo "        </form>\n";
  }
  
  function display()
  {
    if($this->allowRead("propierty") or $this->allowUpdate("propierty"))
    {        
      $typeStr=$_POST['matrixType'];
      $pos=strpos($typeStr,"-");
      $type=substr($typeStr,0,$pos);
      $aggregation_id=substr($typeStr,$pos+1);
      if($aggregation_id==0){$type='empty';}
      $propierty_id=$_POST['varId'];
    
      if($propierty_id==0){$propierty_id=$_POST['varId'];}
      $sql="SELECT variables.id AS Vid,inputs.id AS INid,factors.name AS FN,actions.name as AN FROM effect_propierties
                             INNER JOIN variables ON variables.effect_propierty_id=effect_propierties.id
                             INNER JOIN propierties ON propierties.effect_propierty_id=effect_propierties.id
                             INNER JOIN inputs ON inputs.propierty_id=propierties.id
                             INNER JOIN effects ON propierties.effect_id=effects.id
                             INNER JOIN factors ON effects.factor_id=factors.id
                             INNER JOIN actions ON effects.action_id=actions.id
                             WHERE propierties.id='".$propierty_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $var=new variable();
        $var->link=$this->link;
        $var->readDB($linea['Vid']);
        $title=$var->DB['name']." (".$linea['FN']." - ".$linea['AN'].")";
        $this->displayInput($title,$var,$linea['INid'],$propierty_id);
      }
    }
  }
}

?>
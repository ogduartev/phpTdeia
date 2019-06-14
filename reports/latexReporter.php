<?php
require_once("../input.php");
require_once("../aggregation.php");
require_once("../factor.php");
require_once("../action.php");
require_once("../config/config.class.php");
require_once("matrixReporter.php");

class latexReporter
{
  function connect()
  {
//    $link=mysql_connect("67.23.240.64","root","mysqlclo5!3ah01");
    $link=mysql_connect("localhost","root","rootpassword");
    if($link)
    {
      mysql_query("USE tdeia");
      return true;
    }else
    {
      return false;
    }
  }

  function paintSet($table,$col_id,$id,$min,$max,$case='set')
  {
    $str="";
    $sql="SELECT alpha,L,R FROM ".$table." WHERE ".$col_id."='".$id."' ORDER BY alpha";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $str1="";$str2="";
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str1.="(".($linea['L']-$min)/($max-$min).",".$linea['alpha'].")";
        $str2="(".($linea['R']-$min)/($max-$min).",".$linea['alpha'].")".$str2;
      }
    }
    switch($case)
    {
      case 'set': 
                 $str.="  \\tdeiaPaintSet{".$str1.$str2."}\n";
                 break;
      case 'importance': 
                 $str.="  \\tdeiaPaintImportanceSet{".$str1.$str2."}\n";
                 break;
      case 'aggregation': 
                 $str.="  \\tdeiaPaintAggregationSet{".$str1.$str2."}\n";
                 break;
    }
    return $str;
  }
  
  function paintVariable($variable_id)
  {
    $str="";
    $min=0;$max=0;
    $sql="SELECT name,minimum,maximum FROM variables WHERE id='".$variable_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $min=$linea['minimum'];  
      $max=$linea['maximum'];
      $label0=number_format($min,2);
      $label1=number_format($min + 1.0*($max-$min)/5.0,2);
      $label2=number_format($min + 2.0*($max-$min)/5.0,2);
      $label3=number_format($min + 3.0*($max-$min)/5.0,2);
      $label4=number_format($min + 4.0*($max-$min)/5.0,2);
      $label5=number_format($max,2);
      $str.="  \\tdeiaPaintVariable{".$linea['name']."}{".$label0."}{".$label1."}{".$label2."}{".$label3."}{".$label4."}{".$label5."}\n";  
    }
    
    $sql="SELECT id,label FROM sets WHERE variable_id='".$variable_id."'";
    $str1="";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $cnt=0;
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=$this->paintSet("cuts","set_id",$linea['id'],$min,$max,'set');
        $str1.="  \\tdeiaPaintLabel{".$cnt."}{".$linea['label']."}\n";
        $cnt++;
      }
    }
    $str.=$str1;
    return $str;
    
  }
  
  function paintVariableGrad()
  {
    $filename="matrixSettings.txt";
    $conf=new configuration();
    $config = $conf->readconfig($filename);
    $casos=array("aggregations"=>"colorAggregations",
                 "effects"=>"colorImportance",
                 "propierties"=>"colorPropierties");
    $str="\\newcommand{\\tdeiaPaintMyGradBlock}[1]\n";
    $str.="{\n";
    foreach($casos as $caso=>$colorName)
    {
      $str.=" \\ifthenelse{\\equal{#1}{".$caso."}}\n";
      $str.="{\n";
      $colors=explode(",",str_replace("#","",$config[$colorName]));
      $tam=count($colors);
      if($tam<2){return "";}
      $dx=1.0/($tam-1);
      for($i=1;$i<$tam;$i++)
      {
        $color1=$colors[$i-1];
        $color2=$colors[$i];
        $x1=$dx*($i-1);
        $x2=$dx*($i);
        $str.="  \\tdeiaPaintSubBlockGrad{".$color1."}{".$color2."}{".$x1."}{".$x2."}\n";
      }
      $str.="}{}\n";
    }
    $str.="}\n";
    
    return $str;
  }
  
  function paintInput($input_id)
  {
    $min=$max=0;
    $str="";
    $sql="SELECT variables.id,minimum,maximum,effects.name FROM variables
                              INNER JOIN propierties ON propierties.effect_propierty_id=variables.effect_propierty_id
                              INNER JOIN effects ON propierties.effect_id=effects.id
                              INNER JOIN inputs ON inputs.propierty_id=propierties.id
                              WHERE inputs.id='".$input_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.="  \\tdeiaPaintMyVariable{".$linea['id']."}\n"; 
      $min=$linea['minimum'];   
      $max=$linea['maximum'];   
      $str.="  \\tdeiaPaintVariableTitle{".$linea['name']."}\n";
    }
    $IN=new input();
    $FN=$IN->number($input_id);
    if(count($FN->alpha_cuts)>0)
    {
      $str1="";$str2="";
      foreach($FN->alpha_cuts as $cut)
      {
        $str1.="(".($cut['L']-$min)/($max-$min).",".$cut['alpha'].")";
        $str2="(".($cut['R']-$min)/($max-$min).",".$cut['alpha'].")".$str2;
      }
      $str.="  \\tdeiaPaintInputSet{".$str1.$str2."}\n";
    }
    return $str;
  }

  function paintImportance($effect_id)
  {
    $min=$max=0;
    $str="";
    $sql="SELECT variables.id,minimum,maximum FROM variables
                              INNER JOIN aggregators ON aggregators.id=variables.aggregator_id
                              WHERE importance='1'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.="  \\tdeiaPaintMyVariable{".$linea['id']."}\n";    
      $min=$linea['minimum'];   
      $max=$linea['maximum'];   
    }

    $importance_id=0;
    $sql="SELECT effects.id,effects.name FROM importances 
                                 INNER JOIN effects ON importances.effect_id=effects.id
                                 WHERE effect_id='".$effect_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $importance_id=$linea['id'];
      $str.="  \\tdeiaPaintVariableTitle{".$linea['name']."}\n";
    }
    
    $str.=$this->paintSet("importance_cuts","importance_id",$importance_id,$min,$max,'importance');
    return $str;
  }

  function paintAggregation($aggregation_id)
  {
    $min=$max=0;
    $str="";
    $sql="SELECT variables.id,minimum,maximum,CONCAT(factors.name,' - ',actions.name) AS name FROM variables
                              INNER JOIN aggregators ON aggregators.id=variables.aggregator_id
                              INNER JOIN aggregations ON aggregations.aggregator_id=aggregators.id
                              INNER JOIN factors ON factors.id=aggregations.factor_id
                              INNER JOIN actions ON actions.id=aggregations.action_id
                              WHERE aggregations.id='".$aggregation_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.="  \\tdeiaPaintMyVariable{".$linea['id']."}\n";    
      $min=$linea['minimum'];   
      $max=$linea['maximum'];   
      $str.="  \\tdeiaPaintVariableTitle{".$linea['name']."}\n";
    }
    
    $str.=$this->paintSet("aggregation_cuts","aggregation_id",$aggregation_id,$min,$max,'aggregation');

    return $str;
  }

  function paintVariableFile($project_id,$fn)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaPaintMyVariable}[1]\n";
    $str.="{\n";
    $sql="SELECT variables.id FROM variables
                              INNER JOIN effect_propierties ON effect_propierties.id=variables.effect_propierty_id
                              WHERE effect_propierties.project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->paintVariable($linea['id']);
        $str.=" }{}\n";
      }
    }
    
    $sql="SELECT variables.id FROM variables
                              INNER JOIN aggregators ON aggregators.id=variables.aggregator_id
                              WHERE aggregators.project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->paintVariable($linea['id']);
        $str.=" }{}\n";
      }
    }
    
    $str.="}\n";
    $str.="\n";
    
    $str.=$this->paintVariableGrad();
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }
  
  function paintInputFile($project_id,$fn)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaPaintMyInput}[1]\n";
    $str.="{\n";
    $sql="SELECT inputs.id FROM inputs
                              INNER JOIN propierties ON propierties.id=inputs.propierty_id
                              INNER JOIN variables ON variables.effect_propierty_id=propierties.effect_propierty_id
                              INNER JOIN effects ON effects.id=propierties.effect_id
                              WHERE effects.project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->paintInput($linea['id']);
        $str.=" }{}\n";
      }
    }
        
    $str.="}\n";
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

  function paintImportanceFile($project_id,$fn)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaPaintMyImportance}[1]\n";
    $str.="{\n";
    $sql="SELECT id FROM effects WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->paintImportance($linea['id']);
        $str.=" }{}\n";
      }
    }
        
    $str.="}\n";
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

  function paintAggregationFile($project_id,$fn)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaPaintMyAggregation}[1]\n";
    $str.="{\n";
    $sql="SELECT aggregations.id FROM aggregations
                        INNER JOIN aggregators ON aggregators.id=aggregations.aggregator_id
                        WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->paintAggregation($linea['id']);
        $str.=" }{}\n";
      }
    }
        
    $str.="}\n";
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

  function html2latex($dir,$htmlText)
  {
    $fn1=tempnam($dir,"html");
    $fn2=tempnam($dir,"latex");
    $f=fopen($fn1,"w");
    fwrite($f,$htmlText);
    fclose($f);
    
    $shell="pandoc -f html -t latex -o ".$fn2." ".$fn1;
    passthru($shell);
    
    $str="";
    $f=file($fn2);
    foreach($f as $line)
    {
      $str.=$line;
    }
    unlink($fn1);
    unlink($fn2);
    
    return $str;
  }
  
  function description($dir,$table,$id)
  {
    $str="";
    $str="\\begin{tdeiaDescriptionContent}\n";
    $sql="SELECT description FROM ".$table." WHERE id='".$id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $htmlText=$linea['description'];
      $latexText=$this->html2latex($dir,$htmlText);
    }
    $str.=$latexText;
    $str.="\\end{tdeiaDescriptionContent}\n";
    return $str;
  }

  function descriptionFile($dir,$project_id,$fn)
  {
    $tables=array();
    $tables[]=array("table"=>"projects",           "command" => "\\tdeiaDescriptionProject" ,         "sql" => "SELECT id FROM projects WHERE id='".$project_id."'");
    $tables[]=array("table"=>"aggregators",        "command" => "\\tdeiaDescriptionAggregator" ,      "sql" => "SELECT id FROM aggregators WHERE project_id='".$project_id."'");
    $tables[]=array("table"=>"factors",            "command" => "\\tdeiaDescriptionFactor" ,          "sql" => "SELECT id FROM factors WHERE project_id='".$project_id."'");
    $tables[]=array("table"=>"actions",            "command" => "\\tdeiaDescriptionAction" ,          "sql" => "SELECT id FROM actions WHERE project_id='".$project_id."'");
    $tables[]=array("table"=>"effects",            "command" => "\\tdeiaDescriptionEffect" ,          "sql" => "SELECT id FROM effects WHERE project_id='".$project_id."'");
    $tables[]=array("table"=>"effect_propierties", "command" => "\\tdeiaDescriptionEffectPropierty" , "sql" => "SELECT id FROM effect_propierties WHERE project_id='".$project_id."'");
    $tables[]=array("table"=>"inputs",             "command" => "\\tdeiaDescriptionInput" ,           "sql" => "SELECT inputs.id FROM inputs INNER JOIN propierties ON inputs.propierty_id=propierties.id INNER JOIN effects ON propierties.effect_id=effects.id WHERE project_id='".$project_id."'");
    
    $str="";
    $str.="\\newcommand{\\tdeiaDescription}[2]\n";
    $str.="{\n";
    foreach($tables as $table)
    {
        $str.=" \\ifthenelse{\\equal{#1}{".$table['table']."}}{".$table['command']."{#2}}{}\n";
    }
    $str.=" \\ifthenelse{\\equal{#1}{variables}}{\\tdeiaDescriptionVariable{#2}}{}\n";
    $str.="}\n\n";

    foreach($tables as $table)
    {
      $str.="\\newcommand{".$table['command']."}[1]\n";
      $str.="{\n";
      $sql=$table['sql'];
      $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
      if($result and mysql_num_rows($result)>0)
      {
        while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
        {
          $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
          $str.=" {\n";
          $str.=$this->description($dir,$table['table'],$linea['id']);
          $str.=" }{}\n";
        }
      }
      $str.="}\n\n";
    }

    $str.="\\newcommand{\\tdeiaDescriptionVariable}[1]\n";
    $str.="{\n";
    $sql="SELECT variables.id FROM variables INNER JOIN aggregators ON variables.aggregator_id=aggregators.id WHERE aggregators.project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->description($dir,"variables",$linea['id']);
        $str.=" }{}\n";
      }
    }
    $sql="SELECT variables.id FROM variables INNER JOIN effect_propierties ON variables.effect_propierty_id=effect_propierties.id WHERE effect_propierties.project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->description($dir,"variables",$linea['id']);
        $str.=" }{}\n";
      }
    }
    $str.="}\n\n";
    
    $str.="\\newcommand{\\tdeiaDescriptionThisProject}\n";
    $str.="{\n";
    $str.="  \\tdeiaDescriptionProject{".$project_id."}\n";
    $str.="}\n\n";
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }
  
  function writeEffectPropierty($effect_propierty_id)
  {
    $str="";
    $sql="SELECT effect_propierties.*,variables.id AS VID FROM effect_propierties
                                         INNER JOIN variables ON variables.effect_propierty_id=effect_propierties.id
                                         WHERE effect_propierties.id='".$effect_propierty_id."'";  
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.="  \\tdeiaWriteEffectPropierty";
        $str.="{".$linea['id']."}";
        $str.="{".$linea['name']."}";
        $str.="{".$linea['nature']."}";
        $str.="{".$linea['weight']."}";
        $str.="{".$linea['theta']."}";
        $str.="{".$linea['VID']."}\n";
      }
    }
    return $str;
  }
  
  function writeVariable($variable_id)
  {
    $str="";
    $sql="SELECT * FROM variables WHERE id='".$variable_id."'";  
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.="  \\tdeiaWriteVariable";
        $str.="{".$linea['id']."}";
        $str.="{".$linea['name']."}";
        $str.="{".$linea['minimum']."}";
        $str.="{".$linea['maximum']."}\n";
      }
    }
    return $str;
  }
  
  function writeImportanceEquation($project_id)
  {
    $str="";
    $sql="SELECT id,equation FROM aggregators WHERE importance=1 AND project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.=" \\tdeiaWriteAggregator{".$linea['equation']."}{".$linea['id']."}\n";
    }
    return $str;
  }
  
  function paintFactorNode($factor_id)
  {
    $str="";
    $sql="SELECT * FROM factors WHERE id='".$factor_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.=" \\tdeiaPaintFactorNode{".$linea['id']."}{".$linea['name']."}{".$linea['level']."}{".number_format($linea['weight'],3)."}{".number_format($linea['family_weight'],3)."}\n";
    }
    
    return $str;
  }

  function paintFactorConnections($factor_id)
  {
    $str="";
    $sql="SELECT id FROM factors WHERE factor_id='".$factor_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaPaintFactorConnection{".$factor_id."}{".$linea['id']."}\n";
      }
    }
    
    return $str;
  }

  function writeFactor($factor_id)
  {
    $str="";
    $sql="SELECT * FROM factors WHERE id='".$factor_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.=" \\tdeiaWriteFactor{".$linea['id']."}{".$linea['name']."}{".$linea['level']."}{".$linea['weight']."}{".$linea['family_weight']."}\n";
    }
    return $str;
  
  }
  
  function writeFactorDown($factor_id)
  {
    $str="";
    $sql="SELECT * FROM factors WHERE factor_id='".$factor_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaWriteFactorDown{".$linea['id']."}{".$linea['name']."}\n";
      }
    }else
    {
      $str.=" \\tdeiaWriteFactorNoChildren{}\n";    
    }
    return $str;
  
  }
  
  function writeFactorUp($factor_id)
  {
    $str="";
    $F=new factor();
    $parents=array_reverse($F->findUp($factor_id));
    foreach($parents as $parent_id)
    {
      $sql="SELECT * FROM factors WHERE id='".$parent_id."'";
      $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
      if($result and mysql_num_rows($result)>0)
      {
        $linea=mysql_fetch_array($result,MYSQL_ASSOC);
        $str.=" \\tdeiaWriteFactorUp{".$linea['id']."}{".$linea['name']."}\n";
      }
    }
    if(count($parents)==0)
    {
      $str.=" \\tdeiaWriteFactorNoParent{}\n";    
    }
    return $str;
  }

  
  function paintActionNode($action_id)
  {
    $str="";
    $sql="SELECT * FROM actions WHERE id='".$action_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.=" \\tdeiaPaintActionNode{".$linea['id']."}{".$linea['name']."}{".$linea['level']."}\n";
    }
    
    return $str;
  }

  function paintActionConnections($action_id)
  {
    $str="";
    $sql="SELECT id FROM actions WHERE action_id='".$action_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaPaintActionConnection{".$action_id."}{".$linea['id']."}\n";
      }
    }
    
    return $str;
  }
  
  function writeProjectData($project_id,$fn)
  {
    $str="";
    $sql="SELECT name FROM projects WHERE id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.="\\newcommand{\\projectTitle}{".$linea['name']."}\n";
    }
    
    $str.="\\newcommand{\\projectAuthor}{";
    $sql="SELECT firstname,lastname,groups.name FROM projects
                             INNER JOIN projects_users ON projects.id=projects_users.project_id
                             INNER JOIN users ON projects_users.user_id=users.id
                             INNER JOIN groups ON projects_users.group_id=groups.id
                             WHERE projects.id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=$linea['firstname']." ".$linea['lastname']." (".$linea['name']."),\\\\\n";
      }
      $str=substr($str,0,strlen($str)-4);
    }
    $str.="}\n";
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

  function writeAction($action_id)
  {
    $str="";
    $sql="SELECT * FROM actions WHERE id='".$action_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.=" \\tdeiaWriteAction{".$linea['id']."}{".$linea['name']."}{".$linea['level']."}\n";
    }
    return $str;
  
  }
  
  function writeActionDown($action_id)
  {
    $str="";
    $sql="SELECT * FROM actions WHERE action_id='".$action_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaWriteActionDown{".$linea['id']."}{".$linea['name']."}\n";
      }
    }else
    {
      $str.=" \\tdeiaWriteActionNoChildren{}\n";    
    }
    return $str;
  
  }
  
  function writeActionUp($action_id)
  {
    $str="";
    $F=new action();
    $parents=array_reverse($F->findUp($action_id));
    foreach($parents as $parent_id)
    {
      $sql="SELECT * FROM actions WHERE id='".$parent_id."'";
      $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
      if($result and mysql_num_rows($result)>0)
      {
        $linea=mysql_fetch_array($result,MYSQL_ASSOC);
        $str.=" \\tdeiaWriteActionUp{".$linea['id']."}{".$linea['name']."}\n";
      }
    }
    if(count($parents)==0)
    {
      $str.=" \\tdeiaWriteActionNoParent{}\n";    
    }
    return $str;
  }
  
  function writeImportance($effect_id,$optimism,$r)
  {
    $str="";

    $varID=0;
    $sql="SELECT variables.id FROM aggregators 
                     INNER JOIN variables ON aggregators.id=variables.aggregator_id
                     WHERE importance=1";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $varID=$linea['id'];
    }
    if($varID<1){return "";}
    
    $sql="SELECT importances.* FROM importances
                              INNER JOIN effects ON importances.effect_id=effects.id
                              WHERE effect_id='".$effect_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $FN=new fuzzy_number();
      $FN->read('importance_cuts','importance_id',$linea['id']);
      $var=new variable();
      $short=$var->short($varID,$FN);
      $long=$var->long($varID,$FN);
      $value=number_format($FN->representative_value($optimism,$r),3);
      $ambiguity=number_format($FN->ambiguity($r),3);
      $str.="  \\tdeiaWriteImportance{".$varID."}{".$linea['id']."}{".$linea['description']."}{".$short."}{".$long."}{".$value."}{".$ambiguity."}\n";
    }
    return $str;
  }
  
  function writeEffectInputs($effect_id,$optimism,$r)
  {
    $str="";
    $sql="SELECT effects.id AS EID, effects.name AS EN,
                 factors.id AS FID,factors.name AS FNM,
                 actions.id AS AID,actions.name AS ANM,
                 effect_propierties.id AS EPID, effect_propierties.name AS EPNM,
                 inputs.id AS INID
                   FROM effects
                   INNER JOIN factors ON factors.id=effects.factor_id
                   INNER JOIN actions ON actions.id=effects.action_id
                   INNER JOIN propierties ON propierties.effect_id=effects.id
                   INNER JOIN effect_propierties ON effect_propierties.id=propierties.effect_propierty_id
                   INNER JOIN inputs ON propierties.id=inputs.propierty_id
                   WHERE effects.id='".$effect_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $cnt=0;
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        if($cnt==0)
        {
          $str.="\\begin{tdeiaWriteEffectInputs}{".$linea['EID']."}{".$linea['EN']."}\n";
          $str.="  \\tdeiaWriteInputsHead{".$linea['EID']."}{".$linea['EN']."}{".$linea['FID']."}{".$linea['FNM']."}{".$linea['AID']."}{".$linea['ANM']."}\n";;
        }
        $cnt++;
        $IN=new input();
        $str.="  \\tdeiaWriteInput{".$linea['EPID']."}{".$linea['EPNM']."}{".$linea['INID']."}{".$IN->gettypeStr($linea['INID'])."}{".$IN->getmodifier($linea['INID'])."}{".$IN->asText($linea['INID'],$optimism,$r)."}\n";
      }
      $str.=$this->writeImportance($effect_id,$optimism,$r);
    }
    $str.="\\end{tdeiaWriteEffectInputs}\n";
    return $str;
  }
  
  function writeAllEffectInputs($project_id,$optimism,$r)
  {
    $str="";

    $str.="\\newcommand{\\tdeiaWriteMyEffectInput}[1]\n";
    $str.="{\n";
    $sql="SELECT effects.id FROM effects WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->writeEffectInputs($linea['id'],$optimism,$r);
        $str.=" }{}\n";
      }
    }
    $str.="}\n\n";

    $str.="\\newcommand{\\tdeiaWriteAllEffectInputs}\n";
    $str.="{\n";
    $sql="SELECT effects.id FROM effects WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaWriteMyEffectInput{".$linea['id']."}\n";
        $str.="\\newpage\n";
      }
    }
    $str.="}\n\n";


    return $str;
  }

  function writeAllEffectPropierties($project_id)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaWriteAllEffectPropierties}\n";
    $str.="{\n";
    $sql="SELECT effect_propierties.id FROM effect_propierties WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaWriteMyEffectPropierty{".$linea['id']."}\n";
        //$str.="\\newpage\n";
      }
    }
    $str.="}\n\n";

    $str.="\\newcommand{\\tdeiaWriteAllEffectPropiertiesSummary}\n";
    $str.="{\n";
    $str.="  \\begin{tdeiaEffectPropiertiesSummary}\n";
    $sql="SELECT * FROM effect_propierties WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaWriteMyEffectPropiertyInSummary{".$linea['id']."}{".$linea['name']."}{".$linea['nature']."}{".$linea['weight']."}{".$linea['theta']."}\n";
      }
    }
    $str.="  \\end{tdeiaEffectPropiertiesSummary}\n";
    $str.="}\n";

    return $str;
  }

  function writeAllImportances($project_id,$optimism,$r)
  {
    $str="";

    $varID=0;
    $sql="SELECT variables.id FROM aggregators 
                     INNER JOIN variables ON aggregators.id=variables.aggregator_id
                     WHERE importance=1";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $varID=$linea['id'];
    }
    if($varID<1){return "";}

    $str.="\\newcommand{\\tdeiaWriteMyImportance}[1]\n";
    $str.="{\n";
    $sql="SELECT importances.* FROM importances
                              INNER JOIN effects ON importances.effect_id=effects.id
                              WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $FN=new fuzzy_number();
        $FN->read('importance_cuts','importance_id',$linea['id']);
        $var=new variable();
        $short=$var->short($varID,$FN);
        $long=$var->long($varID,$FN);
        $value=number_format($FN->representative_value($optimism,$r),3);
        $ambiguity=number_format($FN->ambiguity($r),3);
        $str.="  \\tdeiaWriteImportance{".$varID."}{".$linea['id']."}{".$linea['description']."}{".$short."}{".$long."}{".$value."}{".$ambiguity."}\n";
        $str.=" }{}\n";
      }
    }
    $str.="}\n\n";
    
//    $str2=str_replace("tdeiaWriteMyImportance","tdeiaWriteMyImportanceInSummary",$str);
//    $str2=str_replace("tdeiaWriteImportance","tdeiaWriteImportanceInSummary",$str2);
//    $str.=$str2;
///////////////////////////////////


    $str.="\\newcommand{\\tdeiaWriteImportanceInSummary}[1]\n";
    $str.="{\n";
    $sql="SELECT factors.id AS FID,
                 factors.name AS FNM,
                 actions.id AS AID,
                 actions.name AS ANM,
                 effects.id AS EID,
                 effects.name AS ENM,
                 importances.id AS id,
                 importances.description AS IDES FROM importances
                              INNER JOIN effects ON importances.effect_id=effects.id
                              INNER JOIN factors ON effects.factor_id=factors.id
                              INNER JOIN actions ON effects.action_id=actions.id
                              WHERE effects.project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $FN=new fuzzy_number();
        $FN->read('importance_cuts','importance_id',$linea['id']);
        $var=new variable();
        $short=$var->short($varID,$FN);
//        $long=$var->long($varID,$FN);
        $value=number_format($FN->representative_value($optimism,$r),3);
//        $ambiguity=number_format($FN->ambiguity($r),3);
        $str.="  \\tdeiaWriteMyImportanceInSummary{".$linea['FID']."}{".$linea['FNM']."}{".$linea['AID']."}{".$linea['ANM']."}{".$linea['EID']."}{".$short."}{".$linea['IDES']."}{".$value."}{".$linea['ENM']."}\n";
        $str.=" }{}\n";
      }
    }
    $str.="}\n\n";

///////////////////////////////////    
    $str.="\\newcommand{\\tdeiaWriteImportanceSummary}\n";
    $str.="{\n";
    $str.="  \\begin{tdeiaWriteMyImportanceSummary}\n";
    $sql="SELECT importances.* FROM importances
                              INNER JOIN effects ON importances.effect_id=effects.id
                              WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $FN=new fuzzy_number();
        $FN->read('importance_cuts','importance_id',$linea['id']);
        $var=new variable();
        $short=$var->short($varID,$FN);
        $long=$var->long($varID,$FN);
        $value=number_format($FN->representative_value($optimism,$r),3);
        $ambiguity=number_format($FN->ambiguity($r),3);
//        $str.="    \\tdeiaWriteImportanceInSummary{".$varID."}{".$linea['id']."}{".$linea['description']."}{".$short."}{".$long."}{".$value."}{".$ambiguity."}\n";
        $str.="    \\tdeiaWriteImportanceInSummary{".$linea['id']."}\n";
      }
    }
    $str.="  \\end{tdeiaWriteMyImportanceSummary}\n";
    $str.="}\n";
    return $str;
  }

  function writeVariableTextFile($project_id,$fn)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaWriteMyVariable}[1]\n";
    $str.="{\n";
    $sql="SELECT variables.id FROM variables
                        INNER JOIN effect_propierties ON effect_propierties.id=variables.effect_propierty_id
                        WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->writeVariable($linea['id']);
        $str.=" }{}\n";
      }
    }
    $sql="SELECT variables.id FROM variables
                        INNER JOIN aggregators ON aggregators.id=variables.aggregator_id
                        WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->writeVariable($linea['id']);
        $str.=" }{}\n";
      }
    }
        
    $str.="}\n";
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

  function writeAllAggregators($project_id)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaWriteAllAggregatorsSummary}\n";
    $str.="{\n";
    $str.="  \\begin{tdeiaAggregatorsSummary}\n";
    $sql="SELECT * FROM aggregators WHERE project_id='".$project_id."' AND importance=0";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaWriteMyAggregatorInSummary{".$linea['name']."}{".$linea['equation']."}{".$linea['id']."}\n";
      }
    }
    $str.="  \\end{tdeiaAggregatorsSummary}\n";
    $str.="}\n";
    $str.="\n";
    $str.=$this->writeAggregatorsTable($project_id,"Imp",1);
    $str.=$this->writeAggregatorsTable($project_id,"Agg",0);
    return $str;
  }

  function writeAggregatorsTable($project_id,$case,$importance=0)
  {    
    $str="";
    $str.="\\newcommand{\\tdeiaWriteAllAggregatorsTable".$case."}\n";
    $str.="{\n";
    $sql="SELECT aggregators.id AS AGID,
                 aggregators.name AS AGNM,
                 aggregators.equation AS AGEQ,
                 aggregators.description AS AGDS,
                 variables.id AS VID,
                 variables.name AS VNM,
                 variables.description AS VDS,
                 variables.minimum AS VMI,
                 variables.maximum AS VMA
                        FROM aggregators 
                        INNER JOIN variables ON variables.aggregator_id=aggregators.id
                        WHERE project_id='".$project_id."' AND importance=".$importance."";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\tdeiaWriteMyAggregatorInTable".$case."{".$linea['AGID']."}{".$linea['AGNM']."}{".$linea['AGEQ']."}{".$linea['AGDS']."}{".$linea['VID']."}{".$linea['VNM']."}{".$linea['VDS']."}{".$linea['VMI']."}{".$linea['VMA']."}\n";
      }
    }
    $str.="}\n";
    $str.="\n";

    return $str;
  }

  function writeEffectPropiertyFile($project_id,$fn)
  {
    $str="";
    
    $str.="\\newcommand{\\tdeiaWriteMyEffectPropierty}[1]\n";
    $str.="{\n";
    $sql="SELECT effect_propierties.id FROM effect_propierties WHERE effect_propierties.project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.=" \\ifthenelse{\\equal{#1}{".$linea['id']."}}\n";
        $str.=" {\n";
        $str.=$this->writeEffectPropierty($linea['id']);
        $str.=" }{}\n";
      }
    }
    $str.="}\n\n";
    
    $str.=$this->writeAllEffectPropierties($project_id);
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

  function writeAggregatorFile($project_id,$fn)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaWriteMyImportanceEquation}\n";
    $str.="{\n";
    $str.=$this->writeImportanceEquation($project_id);
    $str.="}\n\n";
    
    
    $str.=$this->writeAllAggregators($project_id);
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

  function writeAllFactors($factors)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaWriteAllFactors}\n";
    $str.="{\n";
    foreach($factors as $factor_id)
    {
      $str.=" \\tdeiaWriteMyFactor{".$factor_id."}\n";
    }
    $str.="}\n\n";

    return $str;
  }

  function writeFactorFile($project_id,$fn)
  {
    $str="";
    
    $F=new factor();
    $factor_root=$F->getRoot($project_id);
    $factors=$F->findDownIds($factor_root);

    $str.=$this->writeAllFactors($factors);

    $maxlevel=0;
    $sql="SELECT max(level) as M FROM factors WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $maxlevel=$linea['M'];
    }

    $str.="\\newcommand{\\tdeiaPaintMyFactorTree}\n";
    $str.="{\n";
    $str.="  \\tdeiaPaintFactorTree{".$maxlevel."}{".count($factors)."}\n";
    $str.="}\n\n";
    $str.="\\newcommand{\\tdeiaPaintMyFactorAllNodes}\n";
    $str.="{\n";
    foreach($factors as $factor_id)
    {
      $str.=$this->paintFactorNode($factor_id);
    }
    foreach($factors as $factor_id)
    {
      $str.=$this->paintFactorConnections($factor_id);
    }
    $str.="}\n\n";
    $str.="\\newcommand{\\tdeiaWriteMyFactor}[1]\n";
    $str.="{\n";
    foreach($factors as $factor_id)
    {
      $str.=" \\ifthenelse{\\equal{#1}{".$factor_id."}}\n";
      $str.=" {\n";
      $str.=$this->writeFactor($factor_id);
      $str.=" }{}\n";
    }
    $str.="}\n\n";
    $str.="\\newcommand{\\tdeiaWriteMyFactorDown}[1]\n";
    $str.="{\n";
    foreach($factors as $factor_id)
    {
      $str.=" \\ifthenelse{\\equal{#1}{".$factor_id."}}\n";
      $str.=" {\n";
      $str.=$this->writeFactorDown($factor_id);
      $str.=" }{}\n";
    }
    $str.="}\n\n";
    $str.="\\newcommand{\\tdeiaWriteMyFactorUp}[1]\n";
    $str.="{\n";
    foreach($factors as $factor_id)
    {
      $str.=" \\ifthenelse{\\equal{#1}{".$factor_id."}}\n";
      $str.=" {\n";
      $str.=$this->writeFactorUp($factor_id);
      $str.=" }{}\n";
    }
    $str.="}\n\n";

    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  
  }

  function writeAllActions($actions)
  {
    $str="";
    $str.="\\newcommand{\\tdeiaWriteAllActions}\n";
    $str.="{\n";
    foreach($actions as $action_id)
    {
      $str.=" \\tdeiaWriteMyAction{".$action_id."}\n";
    }
    $str.="}\n\n";

    return $str;
  }

  function writeActionFile($project_id,$fn)
  {
    $str="";
    
    $F=new action();
    $action_root=$F->getRoot($project_id);
    $actions=$F->findDownIds($action_root);

    $str.=$this->writeAllActions($actions);
    
    $maxlevel=0;
    $sql="SELECT max(level) as M FROM actions WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $maxlevel=$linea['M'];
    }

    $str.="\\newcommand{\\tdeiaPaintMyActionTree}\n";
    $str.="{\n";
    $str.="  \\tdeiaPaintActionTree{".$maxlevel."}{".count($actions)."}\n";
    $str.="}\n\n";
    $str.="\\newcommand{\\tdeiaPaintMyActionAllNodes}\n";
    $str.="{\n";
    foreach($actions as $action_id)
    {
      $str.=$this->paintActionNode($action_id);
    }
    foreach($actions as $action_id)
    {
      $str.=$this->paintActionConnections($action_id);
    }
    $str.="}\n\n";
    $str.="\\newcommand{\\tdeiaWriteMyAction}[1]\n";
    $str.="{\n";
    foreach($actions as $action_id)
    {
      $str.=" \\ifthenelse{\\equal{#1}{".$action_id."}}\n";
      $str.=" {\n";
      $str.=$this->writeAction($action_id);
      $str.=" }{}\n";
    }
    $str.="}\n\n";
    $str.="\\newcommand{\\tdeiaWriteMyActionDown}[1]\n";
    $str.="{\n";
    foreach($actions as $action_id)
    {
      $str.=" \\ifthenelse{\\equal{#1}{".$action_id."}}\n";
      $str.=" {\n";
      $str.=$this->writeActionDown($action_id);
      $str.=" }{}\n";
    }
    $str.="}\n\n";
    $str.="\\newcommand{\\tdeiaWriteMyActionUp}[1]\n";
    $str.="{\n";
    foreach($actions as $action_id)
    {
      $str.=" \\ifthenelse{\\equal{#1}{".$action_id."}}\n";
      $str.=" {\n";
      $str.=$this->writeActionUp($action_id);
      $str.=" }{}\n";
    }
    $str.="}\n\n";

    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  
  }

  function writeEffectFile($project_id,$fn,$optimism,$r)
  {
    $str="";

    $str.=$this->writeAllEffectInputs($project_id,$optimism,$r);
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  
  }

  function writeImportanceFile($project_id,$fn,$optimism,$r)
  {
    $str="";

    $str.=$this->writeAllImportances($project_id,$optimism,$r);
    
    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  
  }

  function writeTable($project_id,$fn,$optimism,$r)
  {
    $M=new matrixReporter();
    $M->actionLevel=3;
    $M->factorLevel=3;
    $M->project_id=$project_id;
    $M->type="propierties"; // 'aggregations','effects','propierties'
    $M->cellType="Color"; // 'Short', 'Number', 'Number/Ambiguity', 'Color'
    $M->aggregation_id=3;
       
    $str=$M->write($optimism,$r);

    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

  function writeAllTables($project_id,$dirTables,$prefix,$fn,$optimism,$r)
  {
    $M=new matrixReporter();
       
    $M->writeAll($project_id,$dirTables,$prefix,$fn,$optimism,$r);
    $M->writeTableChaps($project_id,$dirTables,$prefix,$fn);
    $M->writeSummary($project_id,$dirTables."summary.tex");
  }

  function generateAll($project_id,$dir,$optimism=0.5,$r=1)
  {
    $paso=0;
    $paso++;echo "Paso ".$paso."\n";
    $this->paintVariableFile($project_id,$dir."variablesFig.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->paintInputFile($project_id,$dir."inputsFig.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->paintImportanceFile($project_id,$dir."importancesFig.tex"); //input by effect_id
    $paso++;echo "Paso ".$paso."\n";
    $this->paintAggregationFile($project_id,$dir."aggregationsFig.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->descriptionFile($dir,$project_id,$dir."descriptions.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->writeProjectData($project_id,$dir."project.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->writeEffectPropiertyFile($project_id,$dir."effectpropierties.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->writeVariableTextFile($project_id,$dir."variables.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->writeAggregatorFile($project_id,$dir."aggregators.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->writeFactorFile($project_id,$dir."factors.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->writeActionFile($project_id,$dir."actions.tex");
    $paso++;echo "Paso ".$paso."\n";
    $this->writeEffectFile($project_id,$dir."effects.tex",$optimism,$r);
    $paso++;echo "Paso ".$paso."\n";
    $this->writeImportanceFile($project_id,$dir."importances.tex",$optimism,$r);
    $paso++;echo "Paso ".$paso."\n";
    $this->writeTable($project_id,$dir."tableTest.tex",$optimism,$r);
    $paso++;echo "Paso ".$paso."\n";
    $this->writeAllTables($project_id,$dir."mainTables/","mainTables/",$dir."tableSet.tex",$optimism,$r);
  }

}

//$optimism=0.5;
//$r=1;
$project_id=30;
$dirBase="/home/ogduartev/public_html/phpTdeia/reports/example";

$R=new latexReporter();
if($R->connect())
{
  for($pr=1;$pr<=30;$pr++)
  {
    $project_id=$pr;
    $str="";if($pr<10){$str.="0";}$str.=$pr;
    echo "Projecto ".$str."\n";
    $dir=$dirBase.$str."/";
    $R->generateAll($project_id,$dir);
  }
}else{echo "\nConexión fallida\n";}

?>

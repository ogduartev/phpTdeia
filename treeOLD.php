<?php
require_once('config/config.class.php');
require_once("block.php");

class tree extends block
{
  var $nodes;
  
  function findDownList($id,$table)
  {
    $this->nodes=array();
    $sql="SELECT * FROM ".$table."s WHERE id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $qt=array("id"=>$linea['id'],"name"=>$linea['name'],"description"=>$linea['description'],"level"=>$linea['level']);
        if($table=="factor"){$qt["weight"]=$linea['weight'];}
        $this->nodes[]=$qt;
        $this->findDownListLoop($linea['id'],$table);
      }
    }
  }
  
  function findDownListLoop($id,$table)
  {
    $sql="SELECT * FROM ".$table."s WHERE ".$table."_id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $qt=array("id"=>$linea['id'],"name"=>$linea['name'],"description"=>$linea['description'],"level"=>$linea['level']);
        if($table=="factor"){$qt["weight"]=$linea['weight'];}
        $this->nodes[]=$qt;
        $this->findDownListLoop($linea['id'],$table);
      }
    }
  }
  
  function findDown($id,$table)
  {
    $q=array();
    $sql="SELECT * FROM ".$table."s WHERE id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $qt=array("id"=>$linea['id'],"name"=>$linea['name'],"description"=>$linea['description'],"level"=>$linea['level']);
        if($table=="factor"){$qt["weight"]=$linea['weight'];}
        $qt["tree"]=$this->findDownLoop($linea['id'],$table);
        $q[]=$qt;
      }
    }
    return $q; 
  }
  
  function findDownLoop($id,$table)
  {
    $q=array();
    $sql="SELECT * FROM ".$table."s WHERE ".$table."_id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $qt=array("id"=>$linea['id'],"name"=>$linea['name'],"description"=>$linea['description'],"level"=>$linea['level']);
        if($table=="factor"){$qt["weight"]=$linea['weight'];}
        $qt["tree"]=$this->findDownLoop($linea['id'],$table);
        $q[]=$qt;
      }
    }
    return $q;
  }
  
  function findRoot($table,$project_id)
  {
    $sql="SELECT id FROM ".$table."s WHERE ".$table."_id IS NULL";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      return $linea['id'];
    }
    return 0;
  }

  function display($table,$project_id)
  {
/*
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    $menuTreeMarginLeft=$settings['menuTreeMarginLeft'];
    $menuTreeMarginTop=$settings['menuTreeMarginTop'];
    $menuTreeTab=$settings['menuTreeTab'];
    $menuTreeNodeHeight=$settings['menuTreeNodeHeight'];
*/    
    $root_id=$this->findRoot($table,$project_id);
    if($root_id < 1){return;}
    $this->findDownList($root_id,$table);
    print_r($this->nodes);
/*    $str="";
    $cnt=0;
    foreach($this->nodes as $node)
    {
      $top = $menuTreeMarginTop + $cnt*$menuTreeNodeHeight;
      $left= $menuTreeMarginLeft;// + $node['level']*$menuTreeTab;
      $str.="    <div class=\"node_tree\" style=\"margin-left:".$left."px;margin-top:".$top."px;\">\n";
      for($i=0;$i<=$node['level'];$i++)
      {
        $left= $menuTreeMarginLeft + $i*$menuTreeTab;
        $str.="      <div class=\"node_tree_icon\" style=\"margin-left:".$left."px;\">&nbsp;</div>\n";
      }
      $left= $menuTreeMarginLeft + ($node['level']+1)*$menuTreeTab;
      $str.="      <div class=\"node_tree_content\" style=\"margin-left:".$left."px;\"><nobr>".$node['name']."</nobr></div>\n";
      $str.="    </div>\n";
      $cnt++;
    }
    echo $str;*/
  }

}
?>

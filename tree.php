<?php

abstract class tree extends block
{
  var $formId="";
  
  function getChildren($table,$col,$id,$parent_id)
  {
    $children=array();
    $sql="SELECT * FROM ".$table." WHERE ".$col."='".$parent_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $ch=$linea;
        $CH=$this->getChildren($table,$col,$id,$linea[$id]);
        if(count($CH)>0)
        {
          $ch['children']=$CH;
        }
        $children[]=$ch;
      }
    }
    return $children;
  }
  
  function getChildrenOwn($table,$col,$id,$parent_id)
  {
    $children=array();
    $sql="SELECT * FROM ".$table." WHERE ".$id."='".$parent_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $ch=$linea;
      $CH=$this->getChildren($table,$col,$id,$linea[$id]);
      if(count($CH)>0)
      {
        $ch['children']=$CH;
      }
      $children[]=$ch;
    }
    return $children;
  }
  
  function getRootNode($table,$col,$id,$condition="")
  {
    $sql="SELECT ".$id." AS ID FROM ".$table." WHERE ".$col." IS NULL";
    if(strlen($condition)>0)
    {
      $sql.=" AND ".$condition;
    }
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      return $linea['ID'];
    }
  }
  
  abstract function idStr($node);
  abstract function nameStr($node);
  abstract function return_id($node);
    
  function displayNode($node)
  {
    $id=$this->idStr($node);
    $name=$this->nameStr($node);
    $return_id=$this->return_id($node);
    $collapsed="open";
    if(isset($_POST["node_collapsed_".$id]) and ($_POST["node_collapsed_".$id]=="closed"))
    {
      $collapsed="closed";
    }
    echo "<input type=\"hidden\" name=\"node_collapsed_".$id."\" id=\"node_collapsed_".$id."\" value=\"".$collapsed."\"/>\n";
    echo " <li class=\"".$collapsed."_node\">";
    echo "<nobr><div class=\"".$collapsed."_click\" id=\"click_".$id."\" onclick=\"javascript:collapse('".$id."','".$this->formId."');\"></div>";
    echo "<div class=\"node_text\" onClick=\"javascript:clickText('".$this->formId."','".$this->returnId."','".$return_id."');\">".$name."</div></nobr></li>\n";
    if(isset($node['children']))
    {
      echo "<ul class=\"".$collapsed."_list\"   id=\"list_".$id."\">\n";
      foreach($node['children'] as $child)
      {
        $this->displayNode($child);
      }
      echo "</ul>\n";
    }
  }
  
  function displayTreeFromNode($table,$col,$id,$parent_id)
  {
  
    echo "<script type=\"text/javascript\" src=\"js/tree.js\"></script>\n";

    $tree=$this->getChildrenOwn($table,$col,$id,$parent_id);
    echo "<div class=\"treeview\">\n";
    foreach($tree as $node)
    {
      echo "<ul class=\"open_list\"   id=\"list_tree\">\n";
      $this->displayNode($node);
      echo "</ul>\n";
    }
    echo "</div>\n";
    
  }
  
  function displayTreeFromRoot($table,$col,$id,$condition="")
  {
    $parent_id=$this->getRootNode($table,$col,$id,$condition);
    $this->displayTreeFromNode($table,$col,$id,$parent_id);
  }
}

class factorTree extends tree
{
  function idStr($node){return "factor_".$node['id'];}  
  function nameStr($node){return $node['name']." (".number_format($node['weight']*100,1)."%)";}
  function return_id($node){return $node['id'];}
}

class actionTree extends tree
{
  function idStr($node){return "action_".$node['id'];}
  function nameStr($node){return $node['name'];}
  function return_id($node){return $node['id'];}
}
?>

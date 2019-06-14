<?php
require_once("tree.php");

class menu_factors extends block
{
  function displayHidden()
  {
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_action_")
      {
        echo "<input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    if(isset($_POST['actionLevel']))
    {
      echo "           <input type=\"hidden\" name=\"actionLevel\" value='".$_POST['actionLevel']."'>\n";
    }
    if(isset($_POST['factorLevel']))
    {
      echo "           <input type=\"hidden\" name=\"factorLevel\" value='".$_POST['factorLevel']."'>\n";
    }
    echo "           <input type=\"hidden\" name=\"matrixType\"  id=\"matrixType\"  value='".$_POST['matrixType']."'>\n";
    echo "           <input type=\"hidden\" name=\"cellType\"    id=\"cellType\"    value='".$_POST['cellType']."'>\n";
    echo "           <input type=\"hidden\" name=\"varId\"       id=\"varId\"       value='".$variable_id."'>\n";

    $factor_id=0;
    if(isset($_POST['factor_id']))
    {
      $factor_id=$_POST['factor_id'];
    }
    echo "           <input type=\"hidden\" name=\"edit_factor_id\"  id=\"edit_factor_id\"  value='edit'>\n";
    echo "           <input type=\"hidden\" name=\"factor_id\"       id=\"factor_id\"       value='".$factor_id."'>\n";
  }
  
  function display()
  {
    if(true)
    {
      echo "<form id=\"menu_factors\" method=\"post\" action=\"login.php\">\n";
      $tree=new factorTree();
      $tree->connect();
      $tree->formId="menu_factors";
      $tree->returnId="factor_id";
      $condition="project_id='".$_SESSION['TDEIA_project_id']."'";
      $tree->displayTreeFromRoot("factors","factor_id","id",$condition);
      $this->displayHidden();
      $tree->disconnect();
      echo "</form>\n";
    }
  }
}
?>
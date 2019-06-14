<?php
require_once('phptliste/tlistemysql.php');

class menu_actions extends block
{
  function displayHidden()
  {
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_factor_")
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

    $action_id=0;
    if(isset($_POST['action_id']))
    {
      $action_id=$_POST['action_id'];
    }
    echo "           <input type=\"hidden\" name=\"edit_action_id\"  id=\"edit_action_id\"  value='edit'>\n";
    echo "           <input type=\"hidden\" name=\"action_id\"       id=\"action_id\"       value='".$action_id."'>\n";
  }
  
  function display()
  {
    if(true)
    {
      echo "<form id=\"menu_actions\" method=\"post\" action=\"login.php\">\n";
      $tree=new actionTree();
      $tree->connect();
      $tree->formId="menu_actions";
      $tree->returnId="action_id";
      $condition="project_id='".$_SESSION['TDEIA_project_id']."'";
      $tree->displayTreeFromRoot("actions","action_id","id",$condition);
      $this->displayHidden();
      $tree->disconnect();
      echo "</form>\n";
    }
  }
}
?>

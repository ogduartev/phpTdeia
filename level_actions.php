<?php
require_once("block.php");

class level_actions extends block
{
  function displayHidden()
  {
    $str="";
    $str.="         <input type=\"hidden\" name=\"actionLevel\" value='".$_POST['actionLevel']."'>\n";
    $str.="         <input type=\"hidden\" name=\"factorLevel\" value='".$_POST['factorLevel']."'>\n";
    $str.="         <input type=\"hidden\" name=\"matrixType\" value='".$_POST['matrixType']."'>\n";
    $str.="         <input type=\"hidden\" name=\"cellType\" value='".$_POST['cellType']."'>\n";
    $str.="         <input type=\"hidden\" name=\"varId\" value='".$_POST['varId']."'>\n";
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_factor_")
      {
        $str.="           <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_action_")
      {
        $str.="           <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    return $str;
  }
  
  
  function display()
  {
    if($this->allowAny("action"))
    {  
      
      $maxLevel=0;
      $sql="SELECT MAX(level) AS MAX FROM actions WHERE project_id='".$_SESSION['TDEIA_project_id']."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $maxLevel=$linea['MAX'];
      }
      
      $level=$maxLevel;
      if(isset($_POST['actionLevel'])){$level=$_POST['actionLevel'];}else{$_POST['actionLevel']=$maxLevel;}
      
      if($level<0){$level=0;}
      if($level>$maxLevel){$level=$maxLevel;}
      
      $str.="  <form method=\"post\" action=\"login.php\" id=\"formLevelFactor\">\n";
      $str.=$this->displayHidden();
      $str.="  <label class=\"level\" for=\"actionLevel\">".$this->text("level_Depth").": </label>";
      $str.="  <input class=\"level\" type=\"range\" name=\"actionLevel\" min=\"0\" max=\"".$maxLevel."\" value=\"".$level."\" onChange=\"submit();\">\n";
      $str.="  <output class=\"level\" for=\"actionLevel\" id=\"actionLevelOutput\">".$level."</output>";
      $str.="  </form>\n";
      echo $str;
    }
  }
}
?>

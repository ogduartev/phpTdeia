<?php
require_once('block.php');
require_once('factor.php');

class edit_weights extends block
{
  function update($cuts=2)
  {
    $project_id=$_SESSION['TDEIA_project_id'];
    
    if($this->allowUpdate('factor') and $project_id>0)
    {
      $weightChange=false;
      foreach($_POST as $K=>$V)
      {
        if(substr($K,0,9)=="weight_f_")
        {
          $factor_id=substr($K,9);
          $sql="SELECT family_weight AS W FROM factors WHERE id='".$factor_id."'";
          $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
          if($result and mysqli_num_rows($result)>0)
          {
            $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
            if(!($linea['W']==$V))
            {
              $weightChange=true;
              $sql2="UPDATE factors SET family_weight='".$V."' WHERE id='".$factor_id."'";
              mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
            }
          }
        }
      }
    
      if($weightChange)
      {
        $factor = new factor();
        $factor->link=$this->link;
        $factor->recalculateWeights($project_id,$cuts);
      }
    }
  }


  function displayFactor($factor,$ids,$parentWeight)
  {
    $id="f_".$factor['id'];
    $family_weight=number_format($factor['family_weight'],3);
    $weight=number_format($factor['weight'],3);
    $parentWeight=number_format($parentWeight,3);
    echo "      <td class=\"weights_label\" >".$factor['name']."</td>\n";
    echo "      <td class=\"weights_range\" >\n";
    echo "        <input type=\"hidden\" value=\"".$family_weight."\" id=\"hidden_".$id."\" name=\"weight_".$id."\" />\n";
    echo "        <input type=\"range\" min=\"0\" max=\"1\" step=\"0.01\" value=\"".$family_weight."\" id=\"weight_".$id."\" name=\"weight\" onChange=\"javascript:change(this,".$parentWeight.")\"/>\n";
    echo "      </td>\n";
//    echo "      <td class=\"weights_weight\"><span id=\"output_".$id."\" name=\"output_".$id."\">".$family_weight."</span></td>\n";
    echo "      <td class=\"weights_weight\">\n";
    echo "        <input class=\"weights_weight\" id=\"output_".$id."\" name=\"output\" value=\"".$family_weight."\" ondblclick=\"javascript:changeTxt(this,".$parentWeight.")\" />\n";
    echo "      </td>\n";
    echo "      <td class=\"weights_weight\"><span id=\"output_root_".$id."\" name=\"output_root_".$id."\">".$weight."</span></td>\n";
  } 


  function display()
  {
    $factor_id=$_GET['factor_id'];
    if(isset($_POST['weight_submit']))
    {
      $factor_id=$_POST['factor_id'];
      $this->update();
    }
    
    $factor=new factor();
    $factor->link=$this->link;
    if($factor->isRoot($factor_id))
    {
      echo htmlspecialchars($this->text('weight_Factor_is_root'), ENT_QUOTES, 'UTF-8');
      return;
    }
    echo "<script type=\"text/javascript\" src=\"js/weights.js\"></script>\n";
    
    $factors=$factor->getBrothers($factor_id);
    $parentName=$factor->getParentName($factor_id);
    $parentWeight=$factor->getParentWeight($factor_id);
    $ids="";foreach($factors as $fac){$ids.="f_".$fac['id']." ";}
    echo "<form method=\"post\" action=\"edit_weights.php\">\n";
    echo " <table class=\"weights\">\n";
    echo "  <tr class=\"weights\">\n";
    echo "    <th class=\"weights_title\" colspan=4>".$this->text('weight_Weights_in_factor')." '".$parentName."'</th>\n";
    echo "  </tr>\n";
    echo "  <tr class=\"weights\">\n";
    echo "    <th class=\"weights_label\">".$this->text('weight_Factor')."</th>\n";
    echo "    <th class=\"weights_homogeneus\" ><input type=\"button\" class=\"weights_homogeneus\" value=\"".$this->text('weight_Homogenize')."\" onClick=\"javascript:homogeneus(".$parentWeight.")\"/></th>\n";
    echo "    <th class=\"weights_weight\">".$this->text('weight_Weight')."</th>\n";
    echo "    <th class=\"weights_weight\">".$this->text('weight_Weight_to_root')."</th>\n";
    echo "  </tr>\n";
    $sum=0;
    $root_sum=0;
    foreach($factors as $fac)
    {
      $sum=$sum+$fac['family_weight'];
      $root_sum=$root_sum+$fac['weight'];
      echo "  <tr class=\"weights\">\n";
      $this->displayFactor($fac,$ids,$parentWeight);
      echo "  </tr>\n";
    }    
    $sum=number_format($sum,3);
    $root_sum=number_format($root_sum,3);
    echo "  <tr class=\"weights\">\n";
    echo "    <th class=\"weights_label\"></th><th class=\"weights_range\" >".$this->text('weight_Sum')."</th>\n";
    echo "    <th class=\"weights_weight\"><span id=\"output_sum\" name=\"output_sum\">".$sum."</span></th>\n";
    echo "    <th class=\"weights_weight\"><span id=\"output_sum\" name=\"output_root_sum\">".$root_sum."</span></th>\n";
    echo "  </tr>\n";
    echo "  <tr class=\"weights\">\n";
    echo "   <td class=\"weights_submit\" colspan=4>\n";
    echo "    <input type=\"hidden\" name=\"factor_id\" value=\"".$factor_id."\"\"/>\n";
    echo "    <input type=\"submit\" name=\"weight_submit\" class=\"weights_submit\" value=\"".$this->text('weight_Update')." \" onClick=\"submit();\">\n";   
    echo "   </td>\n";
    echo "  </tr>\n";
    echo " </table>\n";
    echo "</form>\n";
    
  }
}

$B=new block();
if($B->connect())
{
  $xmlFN="page_structure/edit_weights.xml";
  $B->html($xmlFN);
}
?>

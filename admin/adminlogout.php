<?php
require_once("block.php");

class adminlogout extends block
{
  
  function display()
  {
    echo "               <form method='POST' action='login.php'>\n";
    echo "                 <input type='submit' name='exitsubmit' class='logout_button' value='".$this->text('adminlogout_Logout')."'>\n";
    echo "               </form>\n";
  }
  
}
?>

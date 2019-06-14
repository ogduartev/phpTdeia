<?php
require_once('block.php');
require_once('input.php');

class edit_longtext extends block
{
  function display()
  {
    $title=$_GET['title'];
    $table=$_GET['table'];
    $col=$_GET['col'];
    $id=$_GET['id'];
    $edit=$_GET['edit'];
    if(isset($_POST['longtext_submit']))
    {
      $title=$_POST['title'];
      $table=$_POST['table'];
      $col=$_POST['col'];
      $id=$_POST['id'];
      $text=$_POST['edit_longtext_content'];
      $text=str_replace('"','\"',$text);
      $sql="UPDATE ".$table." SET ".$col."=\"".$text."\" WHERE id=".$id;
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
//      echo  "<script type='text/javascript'>window.close();</script>"; return; // con esta línea se cierra la ventana
    }
    
    echo "  <script src=\"js/tinymce/js/tinymce/tinymce.min.js\" type=\"text/javascript\"></script>\n";
    echo "  <script>tinymce.init({selector:'textarea'});</script>\n";
    echo "<div class=\"longtext_title\">".$title."</div>\n";
    $text="";
    $sql="SELECT ".$col." AS T FROM ".$table." WHERE id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $text=$linea['T'];
    }
    
    if($edit=='true')
    {
      $text=htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
      echo "  <form method=\"post\" action\"edit_longtext.php\">\n";
      echo "    <textarea name=\"edit_longtext_content\" >\n";
      echo $text."\n";
      echo "    </textarea>\n";
      foreach($_GET as $K=>$V)
      {
        echo "  <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\">\n";   
      }
      echo "  <input type=\"submit\" name=\"longtext_submit\" class=\"longtext_submit\" value=\"".$this->text('input_Update')."\">\n";   
      echo "  </form>\n";
    }else
    {
      echo $text;
    }
  }
}

$B=new block();
if($B->connect())
{
  $xmlFN="page_structure/edit_longtext.xml";
  $B->html($xmlFN);
}
?>

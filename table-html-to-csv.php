

<?php
/*
 *  Save the html from this URL
 *  Same day
 *  http://sentencias.tfjfa.gob.mx:8082/SICSEJL/faces/content/public/BoletinJurisdiccional.xhtml?fbclid=IwAR1-DULB5RE23oFs-up02AtwVzPUS9mFHbo8x39y9iYKZiZXnUAkBn4FalQ
 *  Day before
 *  http://sentencias.tfjfa.gob.mx:8082/SICSEJL/faces/content/public/BoletinJurisdiccional.xhtml?fbclid=IwAR1-DULB5RE23oFs-up02AtwVzPUS9mFHbo8x39y9iYKZiZXnUAkBn4FalQ
 *
 */

  if (!isset($_FILES["file"])) {
?>
<html>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
  <body>
    <form action="" method="POST" enctype="multipart/form-data">
      <input type="file" name="file" />
      <input type="submit"/>
    </form>
   </body>
</html>

<?php
  exit;
}

if ($_FILES["file"]["error"] > 0){
  echo "Error: " . $_FILES["file"]["error"];
  exit;
}

if(isset($_FILES['file'])){
  $errors= array();
  $file_name = $_FILES['file']['name'];
  $file_size =$_FILES['file']['size'];
  $file_tmp =$_FILES['file']['tmp_name'];
  $file_type=$_FILES['file']['type'];

  $fileNameCmps = explode(".", $file_name);
  $fileExtension = strtolower(end($fileNameCmps));
      
  $extensions= array("jpeg","jpg","png", "html", "htm");
      
  if(in_array($fileExtension,$extensions)=== false){
    $errors[]="Extension not allowed, please choose a JPEG or PNG file.";
  }
      
  if($file_size > 100000000){
    $errors[]='File size must be excately 100 MB';
  }

  $path = "uploads/";
  $path = $path . basename($file_name);

  if(!empty(basename($file_name)) && basename($file_name) == "uploads/"){
    unlink($path);
  }
      
  if(empty($errors)==true){
    if(move_uploaded_file($file_tmp, $path)) {
      //echo "<br>El archivo '". $file_name ."' ha sido cargado.<br>".PHP_EOL;
      if(substr($file_name, -4) == 'html'){
        //echo "<br>Comienza a ejecutar php...".PHP_EOL;
        run_request($path); 
      }else{
        echo "<br>La extencion del archivo debe de ser html.<br>".PHP_EOL;
      }
    } else{
      echo "<br>Hubo un error cargando el archivo, por favor vuelva a intentar.<br>\n".PHP_EOL;
    }
  }else{
    print_r($errors);
  }
}

try{ 


} catch(Exception $e) {
  echo $e->getMessage();
}


function run_request($path){

  set_include_path( get_include_path().PATH_SEPARATOR."..");
  //require_once(dirname(__FILE__)."/xlsxwriter.class.php");
  //echo "<br>\n".PHP_EOL;
  //echo 'Abriendo archivo ' . $path .".<br>\n".PHP_EOL;
  if (($gestor = fopen($path, "r")) === FALSE) {
    exit;
  }

  if (($gestor = fopen($path, "r")) !== FALSE) {
    //echo "Abro archivo...".PHP_EOL;
    $page = file_get_contents($path, FILE_USE_INCLUDE_PATH);

    fclose($gestor);
    $csv = array();

    //Remove all befor this string
    $string_before='<input type="hidden" name="frmTablas" value="frmTablas">';
    $page = strstr($page, $string_before);
    $string_after='<button id="frmTablas:j_idt43" name="frmTablas:j_idt43"';
    $str = explode($string_after, $page);
    $page = htmlspecialchars_decode($str[0]);
    $page = utf8_decode($str[0]);

    //$page = preg_replace('/<span[^>]+\>/i', '', $page);
    //$page = preg_replace('/<span class="ui-column-title">.*<\/span>/','',$page);
    //$page = preg_replace('/<div class="\ui-column-title\">(.+?)<\/div>/s','',$page); 
    $page = str_replace('class="ui-state-default" role="columnheader" style="width:4%"><span class="ui-column-title">', '<th><span>', $page);
    $page = str_replace('class="ui-state-default" role="columnheader" style="width:12%"><span class="ui-column-title">', '<th><span>', $page);
    $page = str_replace('class="ui-state-default" role="columnheader" style="width:20%"><span class="ui-column-title">', '<th><span>', $page);

    $page = preg_replace('#<span class="ui-column-title">(.*?)</span>#', '', $page);   
    $page = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $page);
    $page = preg_replace( "/\r|\n/", "", $page);
    $page = preg_replace('#</?a[^>]*>#is', '',$page);
      
    $page = str_replace('</td><td>', '////////', $page);
    $page = str_replace('</th><th>', '////////', $page);
      
    $page = str_replace('<table><thead><tr><th>', '', $page);
    $page = str_replace('<div><label>', '', $page);
    $page = str_replace('<div>','', $page);
    $page = str_replace('</div>','', $page);
    $page = str_replace('<span>','', $page);
    $page = str_replace('</span>','', $page);
    $page = str_replace('<input><img>','', $page);
    $page = str_replace('<!-- Aquí comienza el botón de exportar datos de la búsqueda general-->','', $page);
    $page = str_replace('<!-- Aqu&iacute; comienza el bot&oacute;n de exportar datos de la b&uacute;squeda general-->','', $page);

    $page = str_replace('  ','', $page);
    $page = str_replace('</th></tr></thead><tbody><tr><td>', '||||', $page);
    $page = str_replace('</label></div>','||||', $page);
    $page = str_replace('</td></tr><tr><td>', '||||', $page);
    $page = str_replace('</th></tr><tr><th>', '||||', $page);
    $page = str_replace('</td></tr></tbody></table>', '||||||||', $page);
    $page = str_replace('</label>','||||||||', $page);

    $data = array();
    $data_excel = array();
    $data = explode('||||', $page);
    /*
    $writer = new XLSXWriter();
    $writer->setAuthor('Doc Author');
    $styles1 = array( 'font'=>'Arial','font-size'=>10,'font-style'=>'bold', 'fill'=>'#eee', 'halign'=>'center', 'border'=>'left,right,top,bottom');
    $styles2 = array( ['font-size'=>6],['font-size'=>8],['font-size'=>10],['font-size'=>16] );
    $styles3 = array( ['font'=>'Arial'],['font'=>'Courier New'],['font'=>'Times New Roman'],['font'=>'Comic Sans MS']);
    $styles4 = array( ['font-style'=>'bold'],['font-style'=>'italic'],['font-style'=>'underline'],['font-style'=>'strikethrough']);
    $styles5 = array( ['color'=>'#f00'],['color'=>'#0f0'],['color'=>'#00f'],['color'=>'#666']);
    $styles6 = array( ['fill'=>'#ffc'],['fill'=>'#fcf'],['fill'=>'#ccf'],['fill'=>'#cff']);
    $styles7 = array( 'border'=>'left,right,top,bottom');
    $styles8 = array( ['halign'=>'left'],['halign'=>'right'],['halign'=>'center'],['halign'=>'none']);
    $styles9 = array( array(),['border'=>'left,top,bottom'],['border'=>'top,bottom'],['border'=>'top,bottom,right']);
    */
    $filename = str_replace('uploads/','', $path);
    $filename = str_replace('.html','', $filename);

    create_csv($data, $filename);

    foreach ( $data as $line ) {
      $val = explode('////////', $line);
      array_push($data_excel, $val);
      //$writer->writeSheetRow('Sheet1', $rowdata = $val, $styles1 );
    }

    //Trying to move to type excel file
    //csv_to_excel($data_excel, $filename);
    //$writer->writeToFile('server-'.$filename.'.xlsx');
    //echo "Actualizacion terminada.<br>\n".PHP_EOL;
  }
}

function csv_to_excel($data_excel, $filename){
  //require_once "excel.php";
  $export_file = "xlsfile://".$filename.".xls";
  //$export_file = "xlsfile://example.xls";
  $fp = fopen($export_file, "wb");
  if (!is_resource($fp)){
    die("No abrio el archivo $filename.xls");
  }

  fwrite($fp, serialize($data_excel));
  fclose($fp);
  
  header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
  header ("Cache-Control: no-cache, must-revalidate");
  header ("Pragma: no-cache");
  header ("Content-type: application/x-msexcel");
  header ("Content-Disposition: attachment; filename=\"" . basename($export_file) . "\"" );
  header ("Content-Description: PHP/INTERBASE Generated Data" );
  
  readfile($export_file);
  exit;
}

function create_csv($data, $filename){

  header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8' );
  header ("Content-Disposition: attachment; filename=\"" . basename($filename).".csv" . "\"" );

  $fp = fopen('php://output', 'wb');
  if (!is_resource($fp)){
    die("No abrio el archivo $filename.csv");
  }
  foreach ( $data as $line ) {
    $val = explode('////////', $line);
    fputcsv($fp, $val);
  }

  fclose($fp);
  exit;
}

?>
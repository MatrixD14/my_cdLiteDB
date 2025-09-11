<?php
ob_start();
session_start();
function r(){header("Location:index.php");exit();};
function table($ro,$n=''){
  if(!$ro)return;
  echo "<br><br>$n<hr><div style='max-height:400px;overflow:auto;'><table border=2><tr><th>".implode("</th><th>",array_keys($ro[0]))."</th></tr>";
  foreach($ro as $v) echo"<tr><td>".implode("</td><td>",$v)."</td></tr>";
   echo"</table></div>";
}
if(!($f=$_POST['file']??($_GET['file']??''))) r();
if(!($_SESSION['logo']??0))if(isset($_POST['us'], $_POST['pass'])&&$_POST['us']=="admin"&&$_POST['pass']=="admin"){
       $_SESSION['logo']=1;
       session_regenerate_id(true);
    }else r();
try{
    ($db=new PDO("sqlite:$f"))->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(Exception $e){r();}
$tb=$db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
if(isset($_GET['export'])&&($t=$_GET['export'])){
  if(in_array($t,$tb)){
  $data=$db->query("SELECT * FROM $t")->fetchAll(PDO::FETCH_ASSOC);
  if(($_GET['type']??'')=='json'){
  header('Content-Type: application/json; charset=utf-8');
  header('Content-Disposition: attachment; filename="'.$t.'.json"');
  echo json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
  exit;
  }else{
    header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$t.'.csv"');
        $fout=fopen('php://output','w');
        if(!empty($data)){
            fputcsv($fout,array_keys($data[0]),",",'"',"\\");
            foreach($data as $row) fputcsv($fout,$row,",",'"',"\\");
        }
        fclose($fout);
    exit;
  }
  }
}
foreach($tb as $t)if($t!='sqlite_sequence')echo"<br><a href=?file=$f&t=$t>$t</a><hr>";
if(($t=$_GET['t']??$_SESSION['lab']??'') && in_array($t,$tb)){
  try{
  echo($ro=$db->query("SELECT * FROM $t")->fetchAll(PDO::FETCH_ASSOC))?table($ro,$t):table($db->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC),$t);
    echo"<hr><a href=\"?file=$f&export=$t&type=json\">ExportJSON</a><br><br><a href=\"?file=$f&export=$t&type=csc\">ExportCSV</a>";
  }catch(Exception $e){echo'erro: '.$e->getMessage();}
}
if(!empty($_POST['CMD'])){
  $_SESSION['cmd']=($sql=$_POST['CMD']);
  if(!empty($_GET['run'])&&!empty($_SESSION['cmd'])) $sql= $_SESSION['cmd'];
  $erro ='';
  try{
  if(($ros=$db->query($sql)) instanceof PDOStatement&&preg_match('/^(INSERT|UPDATE|DELETE)\s+(?:INTO|FROM)?\s*["`]?(?<tbl>[a-zA-Z0-9_]+)["`]?/i',$sql,$m)) $_SESSION['lab']=$m['tbl'];
  if(!isset($_SESSION['history'])) $_SESSION['history']=[];$_SESSION['history'][]=$sql;
  }catch(Exception $e){ $erro=$e->getMessage();}
  if($erro)echo"<br>$erro";
  else{header("Location: ?file=$f&run=1");
  exit;}
}
if(isset($_GET['LIMP']))$_SESSION['cmd']='';
?>
<br><br><form method="post" onsubmit="return check()">
        <textarea name="CMD"id="CMD"rows="10" cols="50" placeholder="Digite seu command SQL..." required><?=htmlspecialchars($_SESSION['cmd']??'')?></textarea><br>
        <input type="hidden"name="file"value="<?=htmlspecialchars($f)?>">
        <input type="submit"> <a href="?file=<?=$f?>&LIMP=1"?> LIMPA</a>
    </form>
    <script>
      function check(){
      let cmd=document.querySelector('#CMD').value.toUpperCase(),list=['DROP','DELETE','VACUUM','TRUNCATE','UPDATE','REPLACE','ALTER','ATTACH','DETACH'];
      return list.some(p=>cmd.includes(p))? confirm('esse cmd \n'+cmd+'\n e perigoso, tem certesa'):true;
      }
    </script>
<textarea readonly rows="10" cols="50">
<?php
if(!empty($_SESSION['history']))
  foreach(array_slice(array_reverse($_SESSION['history']),-1000) as $hi)echo"$hi\n\n";
  ob_end_flush();
?></textarea>

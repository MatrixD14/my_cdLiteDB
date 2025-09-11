<?php
ob_start();
session_start();
function r(){header("Location:index.php");exit();};
if(!($f=$_POST['file']??($_GET['file']??''))) r();
if(!($_SESSION['logo']??0))if(isset($_POST['us'], $_POST['pass'])&&$_POST['us']=="admin"&&$_POST['pass']=="admin"){
       $_SESSION['logo']=1;
       session_regenerate_id(true);
    }else r();
try{
    ($db=new PDO("sqlite:$f"))->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(Exception $e){r();}
$tb=$db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
foreach($tb as $t)if($t!='sqlite_sequence')echo"<br><a href=?file=$f&t=$t>$t</a><hr>";
if(!empty($_POST['CMD'])){
  $_SESSION['cmd']=($sql=$_POST['CMD']);
  if(!empty($_GET['run'])&&!empty($_SESSION['cmd'])) $sql= $_SESSION['cmd'];
  try{
  if(($ros=$db->query($sql)) instanceof PDOStatement)$_SESSION['lab']=$t??'';
else if(preg_match('/^(INSERT|UPDATE|DELETE)\s+(?:INTO|FROM)?\s*["`]?(?<tbl>[a-zA-Z0-9_]+)["`]?/i',$sql,$m)) $_SESSION['lab']=$m['tbl'];
  }catch(Exception $e){echo'erro: '.$e->getMessage();}
  header("Location: ?file=$f&run=1");
  exit;
}
if(($t=$_GET['t']??$_SESSION['lab']??'') && in_array($t,$tb)){
  try{
  echo($ro=$db->query("SELECT * FROM $t")->fetchAll(PDO::FETCH_ASSOC))?table($ro,$t):table($db->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC),$t);}catch(Exception $e){echo'erro: '.$e->getMessage();}
}
function table($ro,$n=''){
  if(!$ro)return;
  echo "<br><table border=2>$n<tr><th>".implode("</th><th>",($h=array_keys($ro[0])))."</th></tr>";
  foreach($ro as $v) echo"<tr><td>".implode("</td><td>",$v)."</td></tr>";
   echo"</table>";
}
if(isset($_GET['LIMP']))$_SESSION['cmd']='';
ob_end_flush();
?>
<br><br><form method="post" onsubmit="return check()">
        <textarea name="CMD"id="CMD"rows="10" cols="50" placeholder="Digite seu command SQL..." required><?=htmlspecialchars($_SESSION['cmd']??'')?></textarea><br>
        <input type="hidden"name="file"value="<?=htmlspecialchars($f)?>">
        <input type="submit"> <a href="?file=<?=$f?>&LIMP=1"?>">DELETE</a>
    </form>
    <script>
      function check(){
      let cmd=document.querySelector('#CMD').value.toUpperCase(),list=['DROP','DELETE','VACUUM','TRUNCATE','UPDATE','REPLACE','ALTER','ATTACH','DETACH'];
      return list.some(p=>cmd.includes(p))? confirm('esse cmd \n'+cmd+'\n e perigoso, tem certesa'):true;
      }
    </script>

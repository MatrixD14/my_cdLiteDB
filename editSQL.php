<?php
session_start();
$r=function(){header("Location:index.php");exit();};
$f=$_POST['file']??($_GET['file']??''); 
if(!$f) $r();
if(!($_SESSION['logo']??0)){
    if(isset($_POST['us'], $_POST['pass'])&&$_POST['us']=="admin"&&$_POST['pass']=="admin"){
       $_SESSION['logo']=1;
       session_regenerate_id(true);
    }else $r();
}
try{
    $db=new PDO("sqlite:$f");
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(Exception $e){ $e->getMessage(); $r();}

$tb=$db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
foreach($tb as $t) if($t!='sqlite_sequence') echo "<br><a href=?file=$f&t=$t>$t</a><hr>";
if(!empty($_POST['add'])){
  $sql =$_POST['add'];
  try{
  $ros=$db->query($sql);
if($ros instanceof PDOStatement){
  $ro =$ros->fetchAll(PDO::FETCH_ASSOC);
    echo($ro)?table($ro):'<br>command executado, mas sem retorno';
}else echo'<br>command executado com sucesso';
if(preg_match('/^(INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+([a-zA-Z0-9_]+)/i', $sql, $m)){
            $tbl = $m[2];
            if(in_array($tbl,$tb)){
              $ro=$db->query("SELECT * FROM $tbl")->fetchAll(PDO::FETCH_ASSOC);
              if($ro)table($ro);
              else echo'sem registros';
            }
        }
  }catch(Exception $e){echo'erro: '.$e->getMessage();}
}

if(empty($_POST['add'])&&isset($_GET['t']) && in_array($_GET['t'],$tb)){
  try{
  $ro=$db->query("SELECT * FROM ".$_GET['t'])->fetchAll(PDO::FETCH_ASSOC);
    if($ro)table($ro);
    else{
      echo "<br>sem dados, banco de dados:";
      $ro=$db->query("PRAGMA table_info(".$_GET['t'].")")->fetchAll(PDO::FETCH_ASSOC);
    if($ro)table($ro);
} }catch(Exception $e){echo'erro: '.$e->getMessage();}
}
function table($ro){
  echo "<table border=1><tr>";
        foreach(array_keys($ro[0]) as $th) echo "<th>$th</th>";
        echo "</tr>";
        foreach($ro as $row){
            echo "<tr>";
            foreach($row as $v) echo "<td>$v</td>";
            echo "</tr>";
        }
   echo "</table>";
}?>
<br><br><form method="post" onsubmit="return check()">
        <textarea name="add" id="Command" rows="10" cols="50" placeholder="Digite seu comando SQL..." required></textarea><br>
        <input type="hidden" name="file" value="<?=htmlspecialchars($f)?>">
        <input type="submit" value="Executar SQL">
    </form>
    <script>
      function check(){
      const cmd = document.querySelector('#Command').value.toUpperCase();
      const list = ['DROP', 'DELETE','VACUUM','TRUNCATE','UPDATE', 'REPLACE', 'ALTER', 'ATTACH', 'DETACH'];
      for(let p of list)if(cmd.includes(p))return confirm("esse cmd \""+p+"\" e perigoso, tem certesa, que quer executar!!!");
      return true;
      }
    </script>

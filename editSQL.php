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
}catch(Exception $e){ $e->getMenssage(); $r();}

$tb=$db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
foreach($tb as $t) if($t!='sqlite_sequence') echo "<br><a href=?file=$f&t=$t>$t</a><hr>";
if(isset($_GET['t']) && in_array($_GET['t'],$tb) && ($ro=$db->query("SELECT * FROM ".$_GET['t'])->fetchAll(PDO::FETCH_ASSOC))){
    if($ro){
        echo "<table border=1><tr>";
        foreach(array_keys($ro[0]) as $th) echo "<th>$th</th>";
        echo "</tr>";
        foreach($ro as $row){
            echo "<tr>";
            foreach($row as $v) echo "<td>$v</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else echo "<br>sem dados";
}
function sh(){
echo '<form method="post">
        <textarea name="t" rows="4" cols="50" placeholder="Digite seu comando SQL..."></textarea><br>
        <input type="hidden" name="file" value=$f>
        <input type="submit" value="Executar SQL">
    </form>';
}
sh();
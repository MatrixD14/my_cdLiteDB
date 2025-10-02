<?php ob_start();session_start(); 
strpos(($_SERVER['HTTP_REFERER']??''),basename(__FILE__))?:session_unset()&&session_destroy();
if (isset($_GET['q']) && $_GET['q']==1){
    if (is_dir(($dir = __DIR__."/tmpDB"))) {
        foreach (glob("$dir/*.db") as $db) {
            $wal = $db . '-wal';
            $shm = $db . '-shm';
            if (!file_exists($wal)) {
                @unlink($db);
                @unlink($shm);
            }
        }
    }
    exit;
}
function r(){if(ob_get_level())ob_end_clean();header("Location:/");}
$a=$_GET['f']??''?:r();
$b=(substr($a,-7)=='.db.cry')?$a:$a.'.cry';
$c=__DIR__."/tmpDB/tmp_".md5($a.session_id()).".db";
if(!is_dir(dirname($c)))mkdir(dirname($c),0700,true);
$d=fn($p,$s,$it=100000,$ln=32)=>hash_pbkdf2('sha256',$p,$s,$it,$ln,true);
function e($x,$y,$p){
    global $d;
    $z=@file_get_contents($x);
    if($z===false)die("erro ler $x");
    if(strlen($z)===0)return false;
    $k=random_bytes(32);
    $i=random_bytes(16);
    $v=openssl_encrypt($z,'aes-256-cbc',$k,OPENSSL_RAW_DATA,$i)?:die("openssl falhou");
    $s=random_bytes(16);
    $j=random_bytes(16);
    $f=openssl_encrypt($k,'aes-256-cbc',$d($p,$s),OPENSSL_RAW_DATA,$j);
    if($f===false)die("openssl key falhou");
    $g="DBX1".$s.$j.pack('N',strlen($f)).$f.$i.$v;$h=$y.'.tmp'.mt_rand();
    file_put_contents($h,$g) or die("falha gravar tmp");
    chmod($h,0600);
    rename($h,$y);
    return true;
}
function q($x,$y,$p){
    global $d;
    $z=@file_get_contents($x);
    if($z===false)die("erro ler $x");
    if(strlen($z)<4||substr($z,0,4)!=="DBX1")die("arquivo curto ou magic invalido");
    $o=4;if(strlen($z)<$o+16+16+4)die("header incompleto");
    $s=substr($z,$o,16);$o+=16;
    $j=substr($z,$o,16);$o+=16;
    $l=substr($z,$o,4);$o+=4;
    $m=unpack('N',$l)[1];if(strlen($z)<$o+$m+16)die("Arquivo incompleto");
    $f=substr($z,$o,$m);$o+=$m;
    $i=substr($z,$o,16);$o+=16;
    $v=substr($z,$o);
    $k=openssl_decrypt($f,'aes-256-cbc',$d($p,$s),OPENSSL_RAW_DATA,$j)?:r();
    $p=openssl_decrypt($v,'aes-256-cbc',$k,OPENSSL_RAW_DATA,$i)?:die("Falha decifrar DB");
    file_put_contents($y,$p) or die("Falha gravar tmp $y");
    chmod($y,0600);
    return true;
}
if(!isset($_SESSION['p'])){
    if(!isset($_POST['p'])){
        echo"<form method=post>".(file_exists($b)?'':"Crie ")."senha<input type=password name=p required></form>";
        exit;
    }
    $_SESSION['p']=$_POST['p'];
}
$p=$_SESSION['p'];
if(file_exists($b)){
    if(!file_exists($c))q($b,$c,$p);
}else{
    if(file_exists($a))copy($a,$c) or die("Falha copiar tmp");
    else try{
        $x=new PDO("sqlite:$c");
        $x->setAttribute(3,2);
        $x->exec("PRAGMA journal_mode=WAL;");
        // $x->exec("CREATE TABLE IF NOT EXISTS _I_(id INTEGER PRIMARY KEY); DROP TABLE _I_;");
        $x=null;
    }catch(Exception $e){
            if(file_exists($c))unlink($c);
            die("Erro criando DB: ".$e->getMessage());
        }
        e($c,$b,$p);
        @unlink($a);
        session_unset();
        session_destroy();
        r();
    }
register_shutdown_function(function() use ($c,$b,$p) {
    if(file_exists($c)){
        try { 
            $x = new PDO("sqlite:$c"); 
            $x->setAttribute(3,2); 
            @$x->exec("PRAGMA wal_checkpoint(FULL);"); 
            $x = null; 
        }catch (Throwable $e) {} 
        try { e($c,$b,$p); } catch(Throwable $e) {}
    }
});

try{$y=new PDO("sqlite:$c",null,null,[12=>true]);$y->setAttribute(3,2);$y->exec("PRAGMA journal_mode=WAL;");}catch(Exception $e){r();}$T=$y->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(7);function tb($r,$n){if($r){echo"<b>$n</b><hr><div style=max-height:400px;overflow:auto><table border=1><tr><th>".join("</th><th>",array_keys($r[0]))."</th></tr>";foreach($r as$v)echo"<tr><td>".join("</td><td>",$v)."</td></tr>";echo"</table></div>";}}if(($t=$_GET['x']??'')&&in_array($t,$T)){$d=$y->query("SELECT*FROM $t")->fetchAll(2);header('Content-Type:'.(($j=($_GET['y']??'')=='j')?'application/json':'text/csv'));header("Content-Disposition:attachment;filename=$t.".($j?"json":"csv"));$j?print(json_encode($d,448|256)):($o=fopen('php://output','w'))&&fputcsv($o,array_keys($d[0]??[]))||array_map(fn($r)=>fputcsv($o,$r),$d);exit;}foreach($T as$t)if($t!='sqlite_sequence')echo"<a href=?f=$a&t=$t>$t</a><hr>";$s=[];$e='';if(!empty($_POST['c'])){$_SESSION['c']=$q=$_POST['c'];$r=array_filter(array_map('trim',explode(';',$q)));try{if(count($r)>1)$y->beginTransaction();foreach($r as$sq){if($sq&&!preg_match('/(#|--|\/\*|\*\/)/',$sq)){if(preg_match('/^\s*(SELECT|PRAGMA)/i',$sq)&&($v=$y->query($sq)->fetchAll(2)))$s=['q'=>$sq,'r'=>$v];else{$y->exec($sq);if(preg_match('/^(INSERT|UPDATE|DELETE)\s+(?:INTO|FROM)?\s*["`]?(?<t>[a-z0-9_]+)["`]?/i',$sq,$m))$_SESSION['lb']=$m['t'];}}$_SESSION['h'][]=$sq;}if($y->inTransaction())$y->commit();}catch(Exception $x){if($y->inTransaction())$y->rollBack();$e=$x->getMessage();}$e?print("<b style=color:red>$e</b><hr>"):((!$s)?header("Location:?f=$a&run=1"):'');}if($s)tb($s['r'],$s['q']);if(!$s&&($t=$_GET['t']??($_SESSION['lb']??''))&&in_array($t,$T)){try{$r=$y->query("SELECT*FROM $t")->fetchAll(2);tb($r?:$y->query("PRAGMA table_info($t)")->fetchAll(2),$t);echo"<hr><a href=?f=$a&x=$t&y=j>JSON</a>|<a href=?f=$a&x=$t&y=c>CSV</a>";}catch(Exception $x){echo $x->getMessage();}}isset($_GET['z'])&&$_SESSION['c']='';?><form method=post onsubmit="return s()"><textarea name=c id=c rows=10 cols=50 spellcheck=false required><?=htmlspecialchars($_SESSION['c']??'')?></textarea><br><input type=hidden name=f value="<?=$a?>"><input type=submit> <a href="?f=<?=$a?>&z=1">Clear</a></form><textarea readonly rows=10 cols=50><?php echo !empty($_SESSION['h'])?join("\n\n",array_slice(array_reverse($_SESSION['h']),-500)):'';ob_end_flush();?></textarea><script>function s(){let c=document.querySelector('#c').value.toUpperCase();return['DROP','DELETE','VACUUM','TRUNCATE','UPDATE','REPLACE','ALTER','ATTACH','DETACH'].some(x=>c.includes(x))?confirm(c):1;}window.addEventListener("beforeunload", ()=> {navigator.sendBeacon("?q=1");});</script>
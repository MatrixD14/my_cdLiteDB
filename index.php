<?php header("Cache-Control: no-cache, must-revalidate"); header("Pragma: no-cache"); header("Expires: 0");
$base=realpath(__DIR__);
$dir=isset($_GET['dir'])?realpath($base.'/'.$_GET['dir']):$base;
if($dir===false||strpos($dir,$base)!==0)die('acesso negado');
$file=scandir($dir);
$relati=str_replace($base.'/','',$dir);
$relati=ltrim($relati,'/');?>
<h2>Diretório:</h2>
<ul><?php if($dir!=$base){$paret=trim(dirname($relati),'/'); if($paret!=='')echo '<a href="?dir='.urlencode($paret).'">Voltar</a><br><br>';}
foreach ($file as $files) {
    if($files=="."||$files=="..")continue;
    $Filepast=$dir.'/'.$files;
    if($Filepast===$base.'/index.php')continue;
    $RelPastFile=ltrim(str_replace($base.'/','', $Filepast),'/');
    if(is_dir($Filepast))echo '<li><a href="?dir='.urlencode($RelPastFile).'">'.htmlspecialchars($files).'</a></li><br>';
    else echo '<li><a href="'.htmlspecialchars($RelPastFile).'">'.htmlspecialchars($files).'</a></li><br>';}?></ul>

<?php foreach(scandir($d=@realpath(($b=__DIR__).'/'.$_GET['d']))as$x)if($x[0]!='.'&&($f="$d/$x")!="$b/index.php"){$r=substr($f,strlen($b)+1);echo'<br><a href='.(is_dir($f)?"?d=$r":$r).'>'.htmlspecialchars($x);}

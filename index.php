<?php require 'config.php';
function head($t){?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($t)?> | <?=e(setting('name'))?></title><link rel="stylesheet" href="assets/style.css"><script src="assets/app.js"></script></head><body>
<header class="nav"><a class="logo" href="index.php"><?=e(setting('name'))?></a><nav><?php foreach(TYPES as $k=>$T)if(empty($T['hide']))echo '<a href="index.php?p=list&t='.$k.'">'.e($T['label']).'</a>';?><a href="index.php?p=skills">Skills</a><a href="index.php?p=education">Education</a><a href="index.php?p=certs">Certifications</a><a href="index.php?p=contact">Contact</a><a href="auth.php">Client login</a><a href="admin.php">Admin</a></nav></header><main><?php }
function foot(){?></main><footer><?=e(setting('name'))?>, <?=date('Y')?>. Built with PHP and MySQL.</footer></body></html><?php }
function excerpt($s,$n=140){$s=trim(preg_replace('/\s+/',' ',(string)$s));return strlen($s)>$n?cut($s,$n).'...':$s;}
function get_cat(){return isset($_GET['cat'])&&is_string($_GET['cat'])?trim($_GET['cat']):'';}
function base_url($t){$b=['education'=>'index.php?p=education','cert'=>'index.php?p=certs'];return $b[$t]??'index.php?p=list&t='.rawurlencode($t);}
function entries_of($t,$cat=''){return q("SELECT * FROM entries WHERE type=? AND published=1".($cat!==''?' AND category=?':'').' ORDER BY id DESC',$cat!==''?[$t,$cat]:[$t])->fetchAll();}
function chips($t,$cur){$cs=q("SELECT category c,COUNT(*) n FROM entries WHERE type=? AND published=1 AND category<>'' GROUP BY category ORDER BY category",[$t])->fetchAll();if(!$cs)return;$b=base_url($t);
 echo'<div class="chips"><a class="chip'.($cur===''?' on':'').'" href="'.e($b).'">All</a>';
 foreach($cs as $c)echo'<a class="chip'.(strcasecmp($c['c'],$cur)===0?' on':'').'" href="'.e($b.'&cat='.rawurlencode($c['c'])).'">'.e($c['c']).' <small>'.(int)$c['n'].'</small></a>';
 echo'</div>';}
function ordered($imgs,$cover){$a=[];$b=[];foreach($imgs as $f){if($f['filename']===$cover)$a[]=$f;else $b[]=$f;}return array_merge($a,$b);}
function thumbs($id,$imgs,$cls=''){if(!$imgs)return;echo'<div class="gal'.$cls.'">';foreach($imgs as $f){$u='uploads/'.e($f['filename']);echo'<a href="'.$u.'" data-lb="'.(int)$id.'"><img src="'.$u.'" alt="" loading="lazy"></a>';}echo'</div>';}
function videos($items, $heading = true)
{
    if (!$items) return;
    echo '<section class="video-gallery">';
    if ($heading) echo '<h2>Short videos</h2>';
    echo '<div class="video-grid">';
    foreach ($items as $f) {
        $filename = basename((string)($f['filename'] ?? ''));
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $types = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogv' => 'video/ogg', 'ogg' => 'video/ogg'];
        if (!isset($types[$ext])) continue;
        $u = 'uploads/' . rawurlencode($filename);
        echo '<figure class="video-card">';
        echo '<video controls playsinline preload="metadata" width="100%" aria-label="' . e($f['orig_name'] ?: 'Short video') . '">';
        echo '<source src="' . e($u) . '" type="' . e($types[$ext]) . '">';
        echo 'Your browser cannot play this video. <a href="' . e($u) . '" target="_blank" rel="noopener">Open video file</a>';
        echo '</video>';
        echo '<figcaption>' . e($f['orig_name'] ?: 'Video') . '</figcaption>';
        echo '<p class="video-help">If playback does not start, try the direct video link. MP4 files should use H.264 video and AAC audio.</p>';
        echo '<p><a class="video-direct" href="' . e($u) . '" target="_blank" rel="noopener">Open video in a new tab</a></p>';
        echo '</figure>';
    }
    echo '</div></section>';
}
function docs($pdfs){if(!$pdfs)return;echo'<div class="docs">';foreach($pdfs as $f)echo'<a class="doc" href="download.php?id='.(int)$f['id'].'" download><span class="ic">PDF</span><span class="dn">'.e($f['orig_name']?:'document.pdf').'<span class="sz">'.fsize($f['size']).'</span></span><span class="dl">Download</span></a>';echo'</div>';}
function card($r,$m=null){$n=$m?count($m['image']):0;?><article class="card"><a class="cl" href="index.php?p=view&s=<?=e($r['slug'])?>"><?php if($r['image']):?><img src="uploads/<?=e($r['image'])?>" alt="" loading="lazy"><?php endif?>
<div><?php if($r['level']):?><span class="pill"><?=e($r['level'])?></span><?php endif?><?php if($r['category']):?> <span class="pill alt"><?=e($r['category'])?></span><?php endif?><h3><?=e($r['title'])?></h3><p><?=e($r['summary']?:excerpt($r['body']))?></p>
<?php if($n>1):?><span class="bd"><?=$n?> images</span><?php endif?>
<?php foreach(array_filter(explode(',',$r['tags'])) as $t):?><small>#<?=e(trim($t))?></small> <?php endforeach?></div></a>
<?php if($m&&$m['pdf']):?><div class="cf"><?php docs($m['pdf'])?></div><?php endif?><?php if($m&&!empty($m['video'])): videos($m['video'], false); endif?></article><?php }
$p=$_GET['p']??'home';

function ph($t,$s){echo'<header class="ph rv"><h1>'.e($t).'</h1><p class="lead">'.e($s).'</p></header>';}

if($p==='skills'){head('Skills');ph('Skills','What I use to design, build and secure things.');
 $g=[];foreach(q('SELECT * FROM skills ORDER BY category,pct DESC') as $s)$g[$s['category']][]=$s;
 foreach($g as $c=>$list):?><section class="rv"><h2><?=e($c)?></h2><div class="skgrid"><?php foreach($list as $s):?>
<div class="sk"><div class="skh"><b><?=e($s['name'])?></b><span><span class="cnt" data-to="<?=(int)$s['pct']?>">0</span>%</span></div><div class="trk"><i class="fill" data-w="<?=(int)$s['pct']?>"></i></div></div><?php endforeach?></div></section><?php endforeach;
 if(!$g)echo'<p>No skills yet. Add them in Admin.</p>';foot();exit;}

if($p==='education'){head('Education');ph('Education','Where I studied and what I learned.');
 $cat=get_cat();chips('education',$cat);$r=entries_of('education',$cat);$M=files_for(array_column($r,'id'));?>
<ol class="tl big"><?php foreach($r as $x):$m=$M[(int)$x['id']];?><li class="rv"><span class="pill"><?=e($x['level'])?></span><?php if($x['category']):?> <span class="pill alt"><?=e($x['category'])?></span><?php endif?><h3><?=e($x['title'])?></h3><p class="mut"><?=e($x['summary'])?></p><p><?=nl2br(e($x['body']))?></p><?php thumbs($x['id'],ordered($m['image'],$x['image']),' sm');docs($m['pdf']);?></li><?php endforeach?></ol>
<?php if(!$r)echo'<p>No education yet. Add it in Admin.</p>';foot();exit;}

if($p==='certs'){head('Certifications');ph('Certifications','Credentials you can verify.');
 $cat=get_cat();chips('cert',$cat);$r=entries_of('cert',$cat);$M=files_for(array_column($r,'id'));?>
<div class="grid"><?php foreach($r as $x):$m=$M[(int)$x['id']];$im=ordered($m['image'],$x['image']);?><article class="card cert rv"><?php if($im):$c0=array_shift($im);?><a class="cv" href="uploads/<?=e($c0['filename'])?>" data-lb="<?=(int)$x['id']?>"><img src="uploads/<?=e($c0['filename'])?>" alt="" loading="lazy"></a><?php endif?><div><span class="pill"><?=e($x['level'])?></span><?php if($x['category']):?> <span class="pill alt"><?=e($x['category'])?></span><?php endif?><h3><?=e($x['title'])?></h3><p><?=e($x['summary'])?></p><p><?=nl2br(e($x['body']))?></p>
<?php thumbs($x['id'],$im,' sm');docs($m['pdf']);?>
<?php if(preg_match('#^https?://#',$x['link'])):?><a class="btn" href="<?=e($x['link'])?>" rel="noopener" target="_blank">Verify credential</a><?php endif?></div></article><?php endforeach?></div>
<?php if(!$r)echo'<p>No certifications yet. Add them in Admin.</p>';foot();exit;}

if($p==='about'){head('About');?>
<article class="post"><h1>About me</h1><div class="body"><?=nl2br(e(setting('about')))?></div>
<h2>Skills</h2>
<?php $g=[];foreach(q('SELECT * FROM skills ORDER BY category,pct DESC') as $s)$g[$s['category']][]=$s;
foreach($g as $c=>$list):?><h3><?=e($c)?></h3><div class="skills"><?php foreach($list as $s):?><label><?=e($s['name'])?> <span><?=(int)$s['pct']?>%</span><progress value="<?=(int)$s['pct']?>" max="100"></progress></label><?php endforeach?></div><?php endforeach;if(!$g)echo'<p>No skills added yet.</p>';?>
<h2>Education</h2>
<?php $ed=q("SELECT * FROM entries WHERE type='education' AND published=1 ORDER BY id DESC")->fetchAll();?>
<ol class="tl"><?php foreach($ed as $r):?><li><span class="pill"><?=e($r['level'])?></span><h3><?=e($r['title'])?></h3><p class="mut"><?=e($r['summary'])?></p><p><?=nl2br(e($r['body']))?></p></li><?php endforeach?></ol><?php if(!$ed)echo'<p>No education added yet.</p>'?>
<p><a class="btn" href="index.php?p=contact">Contact me</a></p></article>
<?php foot();exit;}

if($p==='contact'){
 $m='';$sent=false;
 if($_SERVER['REQUEST_METHOD']==='POST'){
  check();if(!empty($_POST['site']))exit;
  $n=trim($_POST['n']??'');$em=trim($_POST['em']??'');$b=trim($_POST['b']??'');
  if(time()-($_SESSION['last']??0)<30)$m='Please wait 30 seconds before sending another message.';
  elseif(!$n||!filter_var($em,FILTER_VALIDATE_EMAIL)||strlen($b)<10||strlen($b)>3000)$m='Enter your name, a valid email, and a message of 10 to 3000 characters.';
  else{q('INSERT INTO messages(name,email,body) VALUES(?,?,?)',[substr($n,0,80),substr($em,0,120),$b]);$_SESSION['last']=time();$sent=true;}
 }
 head('Contact');ph('Contact me','Have a project, a question or a job? Send me a message.');?>
<div class="cgrid"><div class="rv">
<?php foreach(['email'=>'Email','location'=>'Location','github'=>'GitHub','linkedin'=>'LinkedIn'] as $k=>$l){$v=setting($k);if(!$v)continue;echo'<div class="ci"><small>'.$l.'</small>';
 if($k==='email')echo'<a href="mailto:'.e($v).'">'.e($v).'</a>';elseif(preg_match('#^https?://#',$v))echo'<a href="'.e($v).'" rel="noopener" target="_blank">'.e($v).'</a>';else echo e($v);echo'</div>';}?></div>
<div class="rv"><?php if($sent):?><div class="done"><svg class="tick" viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="24"/><path d="M14 27l8 8 16-17"/></svg><h2>Message sent</h2><p>Thank you. I will reply by email.</p></div>
<?php else:if($m)echo'<p class="note">'.e($m).'</p>'?>
<form method="post"><?=field()?>
<div class="fl"><input name="n" placeholder=" " required maxlength="80" value="<?=e($_POST['n']??'')?>"><label>Your name</label></div>
<div class="fl"><input name="em" type="email" placeholder=" " required maxlength="120" value="<?=e($_POST['em']??'')?>"><label>Your email</label></div>
<div class="fl"><textarea name="b" rows="6" placeholder=" " required maxlength="3000"><?=e($_POST['b']??'')?></textarea><label>Your message</label></div>
<input name="site" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true"><button class="btn">Send message</button></form><?php endif?></div></div>
<?php foot();exit;}

if($p==='view'){
 $r=q('SELECT * FROM entries WHERE slug=? AND published=1',[$_GET['s']??''])->fetch();
 if(!$r){http_response_code(404);head('Not found');echo'<h1>Page not found</h1><a href="index.php">Back to home</a>';foot();exit;}
 head($r['title']);$m=files_for([$r['id']])[(int)$r['id']];?>
 <article class="post"><span class="pill"><?=e(TYPES[$r['type']]['label']??$r['type'])?></span><?php if($r['category']):?> <a class="pill alt" href="<?=e(base_url($r['type']).'&cat='.rawurlencode($r['category']))?>"><?=e($r['category'])?></a><?php endif?><h1><?=e($r['title'])?></h1>
 <?php docs($m['pdf']);thumbs($r['id'],ordered($m['image'],$r['image']));videos($m['video']??[]);?>
 <div class="body"><?=nl2br(e($r['body']))?></div>
 <?php if(preg_match('#^https?://#',$r['link'])):?><a class="btn" href="<?=e($r['link'])?>" rel="noopener" target="_blank">Open project</a><?php endif?></article>
 <?php foot();exit;}

if($p==='list'){
 $t=array_key_exists($_GET['t']??'',TYPES)?$_GET['t']:first_type();$s='%'.(is_string($_GET['q']??'')?($_GET['q']??''):'').'%';$cat=get_cat();
 $sql='SELECT * FROM entries WHERE type=? AND published=1 AND (title LIKE ? OR tags LIKE ? OR summary LIKE ? OR category LIKE ?)';$args=[$t,$s,$s,$s,$s];
 if($cat!==''){$sql.=' AND category=?';$args[]=$cat;}
 $rows=q($sql.' ORDER BY id DESC',$args)->fetchAll();$M=files_for(array_column($rows,'id'));
 head(TYPES[$t]['label']);?>
 <h1><?=e(TYPES[$t]['label'])?></h1>
 <form class="search"><input type="hidden" name="p" value="list"><input type="hidden" name="t" value="<?=e($t)?>"><?php if($cat!==''):?><input type="hidden" name="cat" value="<?=e($cat)?>"><?php endif?><input name="q" placeholder="Search by title, tag or category" value="<?=e(is_string($_GET['q']??'')?($_GET['q']??''):'')?>"></form>
 <?php chips($t,$cat);?>
 <div class="grid"><?php foreach($rows as $r)card($r,$M[(int)$r['id']]);if(!$rows)echo'<p>No results. Try another search.</p>';?></div>
 <?php foot();exit;}

head('Home');?>
<section class="hero"><h1><?=e(setting('tagline'))?></h1><p class="lead"><?=e(setting('about'))?></p>
<a class="btn" href="index.php?p=list&t=project">See my projects</a> <a class="btn ghost" href="index.php?p=list&t=lesson">Learn cybersecurity</a></section>
<div class="stats rv"><?php foreach(['Projects'=>"SELECT COUNT(*) FROM entries WHERE type='project' AND published=1",'Lessons'=>"SELECT COUNT(*) FROM entries WHERE type='lesson' AND published=1",'Certifications'=>"SELECT COUNT(*) FROM entries WHERE type='cert' AND published=1",'Skills'=>'SELECT COUNT(*) FROM skills'] as $l=>$sql):?><div><b class="cnt" data-to="<?=(int)q($sql)->fetchColumn()?>">0</b><span><?=$l?></span></div><?php endforeach?></div>
<?php foreach(TYPES as $k=>$T):if(!empty($T['hide']))continue;$rows=q('SELECT * FROM entries WHERE type=? AND published=1 ORDER BY id DESC LIMIT 3',[$k])->fetchAll();if(!$rows)continue;$M=files_for(array_column($rows,'id'));?>
<h2><?=e($T['label'])?></h2><div class="grid"><?php foreach($rows as $r)card($r,$M[(int)$r['id']])?></div>
<?php endforeach;foot();

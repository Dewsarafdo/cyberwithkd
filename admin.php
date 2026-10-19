<?php require 'config.php';
function top(){?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin</title><link rel="stylesheet" href="assets/style.css"><script src="assets/app.js" defer></script></head><body><main class="admin"><?php }
$msg='';$hasUser=q('SELECT COUNT(*) c FROM users')->fetch()['c']>0;
if($_SERVER['REQUEST_METHOD']==='POST'&&empty($_POST)&&!empty($_SERVER['CONTENT_LENGTH'])){
 $msg='That upload is bigger than the server allows (post_max_size in php.ini). Upload fewer or smaller files at a time.';
}elseif($_SERVER['REQUEST_METHOD']==='POST'){
 $a=$_POST['a']??'';
 if($a==='setup'||$a==='login'){
  check();$u=trim($_POST['u']??'');$pw=$_POST['pw']??'';
  if($a==='setup'){
   if(!$hasUser&&$u&&strlen($pw)>=10){q('INSERT INTO users(username,pass) VALUES(?,?)',[$u,password_hash($pw,PASSWORD_DEFAULT)]);$hasUser=true;$msg='Admin created. Log in now.';}
   else $msg='Use a username and a password of 10+ characters.';
  }else{
   $row=q('SELECT * FROM users WHERE username=?',[$u])->fetch();$_SESSION['f']=$_SESSION['f']??0;
   if($_SESSION['f']<5&&$row&&password_verify($pw,$row['pass'])){session_regenerate_id(true);$_SESSION['uid']=$row['id'];$_SESSION['f']=0;}
   else{$_SESSION['f']++;sleep(1);$msg='Wrong login, or too many tries. Close the browser and retry.';}
  }
 }elseif(!empty($_SESSION['uid'])){
  check();
  if($a==='logout'){session_destroy();header('Location: admin.php');exit;}
  if($a==='settings'){foreach(['name','tagline','about','email','location','github','linkedin'] as $k)q('REPLACE INTO settings VALUES(?,?)',[$k,trim($_POST[$k]??'')]);$msg='Settings saved.';}
  if($a==='delete'){$id=(int)($_POST['id']??0);foreach(q('SELECT * FROM files WHERE entry_id=?',[$id])->fetchAll() as $f)remove_file($f);$r=q('SELECT image FROM entries WHERE id=?',[$id])->fetch();if($r&&$r['image'])@unlink(__DIR__.'/uploads/'.basename($r['image']));q('DELETE FROM entries WHERE id=?',[$id]);$msg='Deleted.';}
  if($a==='filedel'){$f=q('SELECT * FROM files WHERE id=?',[(int)($_POST['id']??0)])->fetch();if($f){remove_file($f);if($f['kind']==='image')fix_cover($f['entry_id']);$msg='File removed.';}}
  if($a==='cover'){$f=q("SELECT * FROM files WHERE id=? AND kind='image'",[(int)($_POST['id']??0)])->fetch();if($f){q('UPDATE entries SET image=? WHERE id=?',[$f['filename'],$f['entry_id']]);$msg='Cover image changed.';}}
  if($a==='skill'&&trim($_POST['name']??'')){q('REPLACE INTO skills(name,category,pct) VALUES(?,?,?)',[trim($_POST['name']),trim($_POST['category']??'')?:'General',max(0,min(100,(int)$_POST['pct']))]);$msg='Skill saved.';}
  if($a==='skilldel'){q('DELETE FROM skills WHERE id=?',[(int)$_POST['id']]);$msg='Skill deleted.';}
  if($a==='msgdel'){q('DELETE FROM messages WHERE id=?',[(int)$_POST['id']]);$msg='Message deleted.';}
  if($a==='save'&&isset(TYPES[$_POST['type']??''])&&trim($_POST['title']??'')){
   $ty=$_POST['type'];$id=(int)($_POST['id']??0);$F=TYPES[$ty]['fields'];$d=[];
   if($id&&!q('SELECT 1 FROM entries WHERE id=? AND type=?',[$id,$ty])->fetchColumn())$id=0;
   foreach(['title','summary','body','category','tags','link','level'] as $k)$d[$k]=in_array($k,$F)?trim($_POST[$k]??''):'';
   $d['category']=cut(preg_replace('/\s+/',' ',$d['category']),60);
   if(in_array('period',$F))$d['level']=trim($_POST['period']??'');
   $d['published']=isset($_POST['published'])?1:0;
   if($id)q('UPDATE entries SET title=?,summary=?,body=?,category=?,tags=?,link=?,level=?,published=? WHERE id=? AND type=?',array_merge(array_values($d),[$id,$ty]));
   else{q('INSERT INTO entries(type,slug,title,summary,body,category,tags,link,level,published) VALUES(?,?,?,?,?,?,?,?,?,?)',array_merge([$ty,slugify($d['title'])],array_values($d)));$id=(int)$pdo->lastInsertId();}
   $ni=$np=0;$why=[];
   if(in_array('images',$F)){list($ni,$b,$w)=save_files($id,'images','image');$why=array_merge($why,$w);if($ni)fix_cover($id);}
   if(in_array('pdfs',$F)){list($np,$b,$w)=save_files($id,'pdfs','pdf');$why=array_merge($why,$w);}
   if(in_array('videos',$F)){list($nv,$b,$w)=save_files($id,'videos','video');$why=array_merge($why,$w);}
   $msg='Saved.'.($ni?" $ni image".($ni>1?'s':'').' added.':'').($np?" $np PDF".($np>1?'s':'').' added.':'').($why?' NOT saved: '.implode(' ',$why):'');
  }
 }
}
if(empty($_SESSION['uid'])){top();?>
<form method="post" class="panel"><h1><?=$hasUser?'Log in':'Create your admin account'?></h1><?php if($msg)echo'<p class="note">'.e($msg).'</p>'?><?=field()?>
<input type="hidden" name="a" value="<?=$hasUser?'login':'setup'?>"><input name="u" placeholder="Username" autocomplete="username" required>
<input name="pw" type="password" placeholder="Password<?=$hasUser?'':' (10+ characters)'?>" autocomplete="current-password" required><button class="btn"><?=$hasUser?'Log in':'Create account'?></button></form></main></body></html><?php exit;}

top();$tab=$_GET['t']??first_type();?>
<div class="bar"><h1>Dashboard</h1><nav><?php foreach(TYPES as $k=>$T):?><a href="?t=<?=e($k)?>" class="<?=$tab===$k?'on':''?>"><?=e($T['label'])?></a><?php endforeach?>
<a href="?t=skills" class="<?=$tab==='skills'?'on':''?>">Skills</a><a href="?t=messages" class="<?=$tab==='messages'?'on':''?>">Messages</a><a href="?t=settings" class="<?=$tab==='settings'?'on':''?>">Settings</a><a href="index.php">View site</a>
<form method="post" class="inline"><?=field()?><input type="hidden" name="a" value="logout"><button class="link">Log out</button></form></nav></div>
<div class="admin-metrics">
<?php foreach(['Published content'=>"SELECT COUNT(*) FROM entries WHERE published=1",'Drafts'=>"SELECT COUNT(*) FROM entries WHERE published=0",'Contact messages'=>'SELECT COUNT(*) FROM messages','Client accounts'=>'SELECT COUNT(*) FROM client_users'] as $label=>$sql):?><div class="metric"><span><?=e($label)?></span><strong><?=(int)q($sql)->fetchColumn()?></strong></div><?php endforeach?>
</div>
<?php if($msg)echo'<p class="note">'.e($msg).'</p>';
if($tab==='skills'):?>
<div class="split"><form method="post" class="panel"><h2>Add or update a skill</h2><?=field()?><input type="hidden" name="a" value="skill">
<input name="name" placeholder="Skill name (same name updates it)" required><input name="category" placeholder="Category, for example Cybersecurity">
<label>Level from 0 to 100<input name="pct" type="number" min="0" max="100" value="70"></label><button class="btn">Save skill</button></form>
<div class="panel"><h2>Your skills</h2><?php foreach(q('SELECT * FROM skills ORDER BY category,name') as $r):?><div class="row"><span><?=e($r['name'])?> (<?=e($r['category'])?>, <?=(int)$r['pct']?>%)</span>
<form method="post" class="inline" data-confirm="Delete this skill?"><?=field()?><input type="hidden" name="a" value="skilldel"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="link">Delete</button></form></div><?php endforeach?></div></div>
<?php elseif($tab==='messages'):?>
<div class="panel"><h2>Messages from your contact page</h2><?php $ms=q('SELECT * FROM messages ORDER BY id DESC')->fetchAll();foreach($ms as $r):?>
<div class="msg"><b><?=e($r['name'])?></b> <a href="mailto:<?=e($r['email'])?>"><?=e($r['email'])?></a> <small><?=e($r['created_at'])?></small><p><?=nl2br(e($r['body']))?></p>
<form method="post" class="inline" data-confirm="Delete this message?"><?=field()?><input type="hidden" name="a" value="msgdel"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="link">Delete</button></form></div>
<?php endforeach;if(!$ms)echo'<p>No messages yet.</p>'?></div>
<?php elseif($tab==='settings'):?>
<form method="post" class="panel"><?=field()?><input type="hidden" name="a" value="settings">
<?php foreach(['name'=>'Your name','tagline'=>'Headline on home page','about'=>'About you','email'=>'Public email','location'=>'City and country','github'=>'GitHub link (https://...)','linkedin'=>'LinkedIn link (https://...)'] as $k=>$l):?><label><?=$l?><textarea name="<?=$k?>" rows="<?=$k==='about'?5:1?>"><?=e(setting($k))?></textarea></label><?php endforeach?>
<button class="btn">Save settings</button></form>
<?php else:
 if(!isset(TYPES[$tab]))$tab=first_type();
 $F=TYPES[$tab]['fields'];$ed=isset($_GET['edit'])?q('SELECT * FROM entries WHERE id=? AND type=?',[(int)$_GET['edit'],$tab])->fetch():[];$v=function($k)use($ed){return e($ed[$k]??'');};?>
<div class="split"><div class="col">
<form method="post" enctype="multipart/form-data" class="panel"><h2><?=$ed?'Edit':'Add'?> item</h2><?=field()?><input type="hidden" name="a" value="save"><input type="hidden" name="type" value="<?=e($tab)?>"><input type="hidden" name="id" value="<?=(int)($ed['id']??0)?>">
<input name="title" placeholder="<?=['education'=>'Degree or course','cert'=>'Certification name'][$tab]??'Title'?>" value="<?=$v('title')?>" required>
<input name="summary" placeholder="<?=['education'=>'School or university','cert'=>'Issuing organization'][$tab]??'One-line summary'?>" value="<?=$v('summary')?>">
<?php if(in_array('category',$F)):?><input name="category" list="cats" maxlength="60" autocomplete="off" placeholder="<?=['project'=>'Category, for example Web Dev','lesson'=>'Category, for example Web security','education'=>'Category, for example University','cert'=>'Category, for example Cybersecurity'][$tab]??'Category'?>" value="<?=$v('category')?>"><datalist id="cats"><?php foreach(q("SELECT DISTINCT category FROM entries WHERE type=? AND category<>'' ORDER BY category",[$tab])->fetchAll(PDO::FETCH_COLUMN) as $cc):?><option value="<?=e($cc)?>"><?php endforeach?></datalist><?php endif?>
<textarea name="body" rows="9" placeholder="Full description"><?=$v('body')?></textarea>
<?php if(in_array('images',$F)):?><label>Add images (JPG, PNG or WebP, 5 MB each). Hold Ctrl or Shift to pick many<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple></label><?php endif?>
<?php if(in_array('videos',$F)):?><label>Add short videos (MP4, WebM or Ogg; maximum 50 MB each)<input type="file" name="videos[]" accept="video/mp4,video/webm,video/ogg" multiple></label><small>Compress videos for faster loading. For longer videos, use a trusted video host/CDN.</small><?php endif?>
<?php if(in_array('pdfs',$F)):?><label>Add PDF files that visitors can download (25 MB each)<input type="file" name="pdfs[]" accept="application/pdf,.pdf" multiple></label><?php endif?>
<?php if(in_array('tags',$F)):?><input name="tags" placeholder="Tags, separated by commas" value="<?=$v('tags')?>"><?php endif?>
<?php if(in_array('link',$F)):?><input name="link" placeholder="https://link-to-live-demo-or-repo" value="<?=$v('link')?>"><?php endif?>
<?php if(in_array('period',$F)):?><input name="period" placeholder="Years, for example 2019 to 2023" value="<?=$v('level')?>"><?php endif?>
<?php if(in_array('level',$F)):?><select name="level"><?php foreach(['Beginner','Intermediate','Advanced'] as $l):?><option <?=($ed['level']??'')===$l?'selected':''?>><?=$l?></option><?php endforeach?></select><?php endif?>
<label class="chk"><input type="checkbox" name="published" <?=($ed['published']??1)?'checked':''?>> Published</label><button class="btn">Save item</button></form>
<?php if($ed&&(in_array('images',$F)||in_array('pdfs',$F)||in_array('videos',$F))):$fl=q('SELECT * FROM files WHERE entry_id=? ORDER BY id',[$ed['id']])->fetchAll();$act='?t='.rawurlencode($tab).'&amp;edit='.(int)$ed['id'];?>
<div class="panel"><h2>Files on this item</h2>
<?php $fi=array_filter($fl,function($f){return $f['kind']==='image';});$fp=array_filter($fl,function($f){return $f['kind']==='pdf';});$fv=array_filter($fl,function($f){return $f['kind']==='video';});
if($fi):?><div class="amg"><?php foreach($fi as $f):?><figure><img src="uploads/<?=e($f['filename'])?>" alt="" loading="lazy"><figcaption>
<?php if($f['filename']===$ed['image']):?><b>Cover</b><?php else:?><form method="post" action="<?=$act?>" class="inline"><?=field()?><input type="hidden" name="a" value="cover"><input type="hidden" name="id" value="<?=(int)$f['id']?>"><button class="link">Make cover</button></form><?php endif?>
<form method="post" action="<?=$act?>" class="inline" data-confirm="Remove this image?"><?=field()?><input type="hidden" name="a" value="filedel"><input type="hidden" name="id" value="<?=(int)$f['id']?>"><button class="link">Remove</button></form></figcaption></figure><?php endforeach?></div><?php endif?>
<?php foreach($fp as $f):?><div class="row"><span><?=e($f['orig_name']?:'document.pdf')?> <small>(<?=fsize($f['size'])?>)</small></span><a href="download.php?id=<?=(int)$f['id']?>">Download</a>
<form method="post" action="<?=$act?>" class="inline" data-confirm="Remove this PDF?"><?=field()?><input type="hidden" name="a" value="filedel"><input type="hidden" name="id" value="<?=(int)$f['id']?>"><button class="link">Remove</button></form></div><?php endforeach?>
<?php foreach($fv as $f):?><div class="row"><span><?=e($f['orig_name']?:'video')?> <small>(<?=fsize($f['size'])?>)</small></span><form method="post" action="<?=$act?>" class="inline" data-confirm="Remove this video?"><?=field()?><input type="hidden" name="a" value="filedel"><input type="hidden" name="id" value="<?=(int)$f['id']?>"><button class="link">Remove</button></form></div><?php endforeach?>
<?php if(!$fl)echo'<p>No files yet. Pick files in the form above and press Save item.</p>'?></div>
<?php endif?></div>
<div class="panel"><h2>All items</h2><?php foreach(q('SELECT id,title,published,category,(SELECT COUNT(*) FROM files f WHERE f.entry_id=entries.id AND f.kind=\'pdf\') pdfs FROM entries WHERE type=? ORDER BY id DESC',[$tab]) as $r):?>
<div class="row"><span><?=e($r['title'])?><?=$r['published']?'':' (draft)'?><?php if($r['category']):?> <small>[<?=e($r['category'])?>]</small><?php endif?><?php if($r['pdfs']):?> <small>[<?=(int)$r['pdfs']?> PDF]</small><?php endif?></span><a href="?t=<?=e($tab)?>&edit=<?=$r['id']?>">Edit</a>
<form method="post" class="inline" data-confirm="Delete this item?"><?=field()?><input type="hidden" name="a" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="link">Delete</button></form></div><?php endforeach?></div>
</div>
<?php endif?></main></body></html>

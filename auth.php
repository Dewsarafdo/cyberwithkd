<?php
require 'config.php';
// Client accounts are separate from the site administrator account.
function auth_redirect($msg=''){ header('Location: auth.php'.($msg?'?m='.rawurlencode($msg):'')); exit; }
function client_ip_hash(){ return hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown', getenv('APP_KEY') ?: 'replace-this-app-key-before-deploying'); }
function rate_limited(){ q("DELETE FROM auth_attempts WHERE attempted_at < (NOW() - INTERVAL 1 HOUR)"); return (int)q('SELECT COUNT(*) FROM auth_attempts WHERE ip_hash=? AND attempted_at > (NOW() - INTERVAL 15 MINUTE)',[client_ip_hash()])->fetchColumn() >= 8; }
function record_attempt(){ q('INSERT INTO auth_attempts(ip_hash) VALUES(?)',[client_ip_hash()]); }
function is_local_development(){
  $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
  $host = preg_replace('/:\d+$/', '', $host);
  return in_array($host, ['localhost', '127.0.0.1', '::1'], true) && !TURNSTILE_SITE_KEY && !TURNSTILE_SECRET_KEY;
}
function verify_turnstile($token){
  // Development fallback uses rate limits + a honeypot. Configure Turnstile keys for public deployment.
  if (!TURNSTILE_SECRET_KEY || !TURNSTILE_SITE_KEY) return false;
  if (!$token || !function_exists('curl_init')) return false;
  $ch=curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
  curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['secret'=>TURNSTILE_SECRET_KEY,'response'=>$token,'remoteip'=>$_SERVER['REMOTE_ADDR']??'']),CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>5]);
  $raw=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$data=json_decode((string)$raw,true);
  return $code===200 && !empty($data['success']);
}
$msg=''; $mode=$_GET['mode']??'login';
if (!in_array($mode,['login','register','verify','resend','logout','account'],true)) $mode='login';
if ($mode==='logout' && $_SERVER['REQUEST_METHOD']==='POST') { check(); unset($_SESSION['client_id']); session_regenerate_id(true); auth_redirect('You have been signed out.'); }
if ($mode==='verify') {
  $token=is_string($_GET['token']??null)?$_GET['token']:'';
  if (strlen($token)===64) { $r=q('SELECT id FROM client_users WHERE verify_token_hash=? AND verify_expires > NOW() AND verified_at IS NULL',[hash('sha256',$token)])->fetch(); if($r){q('UPDATE client_users SET verified_at=NOW(),verify_token_hash=NULL,verify_expires=NULL WHERE id=?',[$r['id']]);$msg='Email verified. You can now log in.';} else $msg='This verification link is invalid or expired. Register again or contact the site owner.'; }
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
  check();
  $mode=$_POST['mode']??'login';
  if ($mode==='logout') { unset($_SESSION['client_id']); session_regenerate_id(true); auth_redirect('You have been signed out.'); }
  if (!empty($_POST['website'])) { $msg='Unable to process request.'; }
  elseif (rate_limited()) { http_response_code(429); $msg='Too many attempts. Please wait 15 minutes and try again.'; }
  elseif ($mode==='register') {
    $name=trim((string)($_POST['name']??''));$email=strtolower(trim((string)($_POST['email']??'')));$pw=(string)($_POST['password']??'');
    record_attempt();
    if (!preg_match('/^[\pL\pN .\'-]{2,100}$/u',$name)||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>190||strlen($pw)<8||strlen($pw)>200) $msg='Enter a valid name and email, and a password of at least 8 characters.';
    elseif(q('SELECT id FROM client_users WHERE email=?',[$email])->fetch()) $msg='An account with this email already exists. Please log in.';
    else {
      q('INSERT INTO client_users(email,display_name,pass,verified_at,verify_token_hash,verify_expires) VALUES(?,?,?,NOW(),NULL,NULL)',[$email,$name,password_hash($pw,PASSWORD_DEFAULT)]);
      $msg='Account created successfully. You can now log in.';
      $mode='login';
    }
  } elseif ($mode==='login') {
    $email=strtolower(trim((string)($_POST['email']??'')));$pw=(string)($_POST['password']??'');record_attempt();
    $r=q('SELECT * FROM client_users WHERE email=?',[$email])->fetch();
    if($r && password_verify($pw,$r['pass'])){session_regenerate_id(true);$_SESSION['client_id']=(int)$r['id'];header('Location: auth.php?mode=account');exit;}
    $msg='Login failed. Check your email and password.';
  }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Client account</title><link rel="stylesheet" href="assets/style.css"></head><body><main class="post auth"><p><a href="index.php">← Back to portfolio</a></p>
<?php if($msg):?><p class="note"><?=e($msg)?></p><?php endif?>
<?php if($mode==='account' && !empty($_SESSION['client_id'])): $u=q('SELECT email,display_name,verified_at,created_at FROM client_users WHERE id=?',[(int)$_SESSION['client_id']])->fetch(); if(!$u){unset($_SESSION['client_id']);header('Location: auth.php');exit;}?>
<h1>Your account</h1><p>Welcome, <?=e($u['display_name'])?>.</p><p>Email: <?=e($u['email'])?></p><p>Account created: <?=e($u['created_at'])?></p><form method="post"><?=field()?><input type="hidden" name="mode" value="logout"><button class="btn">Sign out</button></form>
<?php elseif($mode==='register'):?><h1>Create client account</h1><p>Create an account with your name, email and password. No email verification is required.</p><form method="post" class="panel"><?=field()?><input type="hidden" name="mode" value="register"><input class="hp" name="website" tabindex="-1" autocomplete="off"><label>Name<input name="name" required maxlength="100" autocomplete="name"></label><label>Email<input type="email" name="email" required maxlength="190" autocomplete="email"></label><label>Password (8+ characters)<input type="password" name="password" required minlength="8" maxlength="200" autocomplete="new-password"></label><p class="mut">Email verification is disabled. Use a strong, unique password.</p><button class="btn">Create account</button></form><p>Already registered? <a href="auth.php">Log in</a></p>
<?php else:?><h1>Client login</h1><form method="post" class="panel"><?=field()?><input type="hidden" name="mode" value="login"><input class="hp" name="website" tabindex="-1" autocomplete="off"><label>Email<input type="email" name="email" required autocomplete="email"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="btn">Log in</button></form><p>New here? <a href="auth.php?mode=register">Create an account</a></p><?php endif?></main></body></html>

const R=document.documentElement;R.classList.add('js');
try{const t=localStorage.getItem('theme');if(t)R.dataset.theme=t}catch(e){}
document.addEventListener('submit',e=>{const m=e.target.dataset.confirm;if(m&&!confirm(m))e.preventDefault()});
document.addEventListener('DOMContentLoaded',()=>{
 const $=s=>[...document.querySelectorAll(s)],nav=document.querySelector('.nav nav'),Q=new URLSearchParams(location.search);
 // Subtle pointer glow and card tilt on fine-pointer devices only.
 const finePointer=matchMedia('(hover:hover) and (pointer:fine)').matches;
 if(finePointer){document.querySelectorAll('.card').forEach(card=>{card.addEventListener('pointermove',e=>{const r=card.getBoundingClientRect(),x=(e.clientX-r.left)/r.width,y=(e.clientY-r.top)/r.height;card.style.setProperty('--mx',(x*100)+'%');card.style.setProperty('--my',(y*100)+'%');if(!matchMedia('(prefers-reduced-motion: reduce)').matches){card.style.transform='perspective(900px) rotateX('+((.5-y)*2.8)+'deg) rotateY('+((x-.5)*3.2)+'deg) translateY(-5px)'}});card.addEventListener('pointerleave',()=>{card.style.transform='';card.style.removeProperty('--mx');card.style.removeProperty('--my')})});}
 function count(n){const to=+n.dataset.to,t0=performance.now();(function f(t){const p=Math.min((t-t0)/1300,1);n.textContent=Math.round(to*(1-Math.pow(1-p,3)));if(p<1)requestAnimationFrame(f)})(t0)}
 const io=new IntersectionObserver(es=>es.forEach(x=>{if(!x.isIntersecting)return;const el=x.target;el.classList.add('in');
  el.querySelectorAll('.fill').forEach(f=>f.style.width=f.dataset.w+'%');el.querySelectorAll('.cnt').forEach(count);io.unobserve(el)}),{threshold:.12});
 $('.rv').forEach((el,i)=>{el.style.animationDelay=(i%4)*90+'ms';io.observe(el)});
 if(nav){
  $('.nav nav a').forEach(a=>{const u=new URL(a.href);if(Q.get('p')&&u.searchParams.get('p')===Q.get('p')&&u.searchParams.get('t')===Q.get('t'))a.classList.add('on')});
  const b=document.createElement('button');b.className='tg';b.setAttribute('aria-label','Switch light or dark theme');b.textContent='\u25D0';
  b.onclick=()=>{const d=(R.dataset.theme||(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'))==='dark'?'light':'dark';R.dataset.theme=d;try{localStorage.setItem('theme',d)}catch(e){}};nav.append(b);
 }
 const sp=document.createElement('div');sp.id='sp';document.body.append(sp);
 addEventListener('scroll',()=>{sp.style.width=scrollY/((R.scrollHeight-innerHeight)||1)*100+'%'},{passive:true});
 document.addEventListener('click',e=>{const a=e.target.closest('a[href]');if(!a||a.target||a.hash||a.hasAttribute('download')||a.hasAttribute('data-lb')||e.metaKey||e.ctrlKey||e.shiftKey||a.origin!==location.origin)return;
  e.preventDefault();document.body.classList.add('out');setTimeout(()=>location.href=a.href,220)});
 // image viewer: click any gallery picture, then use the arrows, the keyboard or swipe-free buttons
 const lb=document.createElement('div');lb.id='lb';lb.hidden=true;
 lb.innerHTML='<button class="lx" aria-label="Close">\u00D7</button><button class="lp" aria-label="Previous image">\u2039</button><img alt=""><button class="ln" aria-label="Next image">\u203A</button><span class="lc"></span>';
 document.body.append(lb);
 let set=[],cur=0;const im=lb.querySelector('img'),lc=lb.querySelector('.lc'),lp=lb.querySelector('.lp'),ln=lb.querySelector('.ln');
 function show(n){cur=(n+set.length)%set.length;im.src=set[cur].href;lc.textContent=set.length>1?(cur+1)+' / '+set.length:'';lp.style.display=ln.style.display=set.length>1?'':'none'}
 function shut(){lb.hidden=true;im.removeAttribute('src');document.body.style.overflow=''}
 document.addEventListener('click',e=>{
  const a=e.target.closest('a[data-lb]');
  if(a){e.preventDefault();set=$('a[data-lb="'+a.dataset.lb+'"]');lb.hidden=false;document.body.style.overflow='hidden';show(set.indexOf(a));return}
  if(lb.hidden)return;
  if(e.target.closest('.lx')||e.target===lb)shut();else if(e.target.closest('.lp'))show(cur-1);else if(e.target.closest('.ln'))show(cur+1)});
 addEventListener('keydown',e=>{if(lb.hidden)return;if(e.key==='Escape')shut();if(e.key==='ArrowLeft')show(cur-1);if(e.key==='ArrowRight')show(cur+1)});
 addEventListener('pageshow',()=>document.body.classList.remove('out'));
});

<!--
/*MSClass (Class Of Marquee Scroll - General Uninterrupted Scrolling(JS)) Ver 2.85*\
@ 持蛍電佩茨獄強丼惚��厮将瓜yi砺虹���峺柘致醫袖槻慣─�
@ Code By : Yongxiang Cui(333) E-Mail:zhadan007@21cn.com http://www.popub.net
@ Update　: 2011-07-08 (Ver 2.85)

\***================Please keep the above copyright information================***/


/**==================The following instructions can be deleted=================**\
　Explain:
　　include <script type="text/javascript" src="MSClass.js"></script> 
　Create Examples:
　　I
　　new Marquee("Marquee") 
　　new Marquee("Marquee","top")
　　......
　　new Marquee("Marquee",0,1,760,52)
　　new Marquee("Marquee","top",1,760,52,50,5000)
　　......
　　new Marquee("Marquee",0,1,760,104,50,5000,3000,52)
　　new Marquee("Marquee",null,null,760,104,null,5000,null,-1)

　　II
　　var Marquee1 = new Marquee("Marquee")　*required 
　　Marquee1.Direction = "top";　or　Marquee1.Direction = 0; 
　　Marquee1.Step = 1; 
　　Marquee1.Width = 760; 
　　Marquee1.Height = 52; 
　　Marquee1.Timer = 50; 
　　Marquee1.DelayTime = 5000; 
　　Marquee1.WaitTime = 3000; 
　　Marquee1.ScrollStep = 52; 
　　Marquee1.Start(); 

　　III
　　new Marquee( 
　　{ 
　　　MSClassID : "Marquee", 
　　　Direction : "top", 
　　　Step　 : 1, 
　　　Width　 : 760, 
　　　Height　 : 52, 
　　　Timer　 : 50, 
　　　DelayTime : 5000, 
　　　WaitTime : 3000, 
　　　ScrollStep: 52, 
　　　AutoStart : 1 
　　}); 

　Parameters:
　　ID　　　　　 "Marquee"　Container ID　　　(required) 
　　Direction　　(0)　　　　Scroll direction　(optional,Default 0)(Values:0 Up, 1 Down, 2 Left, 3 Right, -1 Vertical alternate, 4 Transverse alternate) 
　　Step　　　　 (1)　　　　Scrolling step　　(optional,Default 2) 
　　Width　　　　(760)　　　Visual width　　　(optional,Default is container width) 
　　Height　　　 (52)　　　 Visual height　　 (optional,Default is container height) 
　　Timer　　　　(50)　　　 Running timer　　 (optional,Default 30) 
　　DelayTime　　(5000)　　 Pause delay time　(optional,Default 0 - not pause) 
　　WaitTime　　 (3000)　　 Waiting time　　　(optional,Default 0 - not wait) 
　　ScrollStep　 (52)　　　 Scroll spacing　　(optional,Default is visual width or visual height) 
　　SwitchType　 (0)　　　　Wheel show type 　(optional,Default 0 - scroll)(Values:0 Scroll, 1 Cut in, 2 Fade in)

\***all rights reserved:Yongxiang Cui(333) E-Mail:zhadan007@21cn.com Website:http://www.popub.net***/ 

eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)d[e(c)]=k[c]||e(c);k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('13 2r(){16 b=1k,a=1M;b.$=13(c){15 1h.3K(c)};b.t=(b.F=["3M 2.78.77","76 74 2r 2s","73 72 71(70)","6Z://6Y.6X.6W/6V/3M.6U","6T 6S(6R) 6Q@6P.6O"]).4x("\\n");b.a=a[0]["3M"]||a[0]||a[0][0];b.b=(b.c=1L b.a=="3l"?b.$(b.a[0])||b.$(b.a.4K):b.$(b.a))&&b.6N.1U().2S(4N>>>4M,14>>4L)==b.F[1].2S(4N>>>4M,14>>4L);11(!b.c||!b.b){15 2u("4k ["+(b.a.4K||b.a[0]||b.a)+"] 6M 6L!")||(b.c=-1)}b.2Y=1+(b.i=b.2d=-1);b.f=b.q=b.r=b.s=b.B=b.u=b.k=b.e=b.d=b.j=0;b.18=a[1]||a[0]["18"]||0;b.1a=a[2]||a[0]["1a"]||1;b.1F=a[3]||a[0]["1F"]||0;b.1N=a[4]||a[0]["1N"]||0;b.1T=a[5]||a[0]["1T"]||30;b.1n=a[6]||a[0]["1n"]||0;b.1S=a[7]||a[0]["1S"]||2G;b.1b=a[8]||a[0]["1b"]||-10;b.1m=a[9]||a[0]["1m"]||0;b.2j=a[10]||a[0]["2j"];b.38=b.$(a[0]["6K"])||0;b.37=b.$(a[0]["6J"])||0;b.1Q=a[0]["1Q"]||[];b.c.12.3e=b.c.12.4f=b.c.12.4e="2O";b.3O=(6I.6H.2v().6G("6F")==-1);b.m=(1h.3B)?1:0;11(a.1j>=7||a[0]["6E"]==1){b.4J()}}2r.3Y.4J=13(){11(1k.c==-1||1k.i>=0){11(1k.i==2){1k.2B()}15 1g}16 f=1k,G,r,P,K,u,O,e="6D",N=2r,E=0,p=[],D=0,B=0,n,b=0,L={6C:-1,6B:-1,1t:0,6A:0,2o:1,6z:1,1x:2,3G:3,6y:4},h=0,A=0,T=0,w=0,t=0,q=0,F=0,M=0,a=[],S=1A;16 g=13(m,j,i){i?0:i=0;15 f.m?(m.2a[j]!="4I"&&m.2a[j]!="4H"&&m.2a[j]!="3I"&&m.2a[j]!="6x"&&m.2a[j]!="6w"&&m.2a[j]!="3v")?m.2a[j]:i:(1i.2p(m,1A)[j]!="4I"&&1i.2p(m,1A)[j]!="4H"&&1i.2p(m,1A)[j]!="3I"&&1i.2p(m,1A)[j]!="2z"&&1i.2p(m,1A)[j]!="3v")?1i.2p(m,1A)[j]:i};16 v=13(j){16 i=j||1i.1v;3L=i.3L||i.6v;11(3L==6u){2u(f.t)}};f.c.12.1G=g(f.c,"1G","4G");16 H={b:13(){15\'<1s><1p 1D="8"><1E 12="1o:0;1o-1t:1z 1I #2X;1o-2o:1z 1I #29;1O:0;28:0;1O-2o:-4F;"></1E></1p></1s>\'},c:13(i,j){15 6t(j)?\'<1s><1p 6s="2o" 1D="17" 1R="1k.12.2n=\\\'#3E\\\';1k.12.1P=\\\'#29\\\';" 1W="1k.12.2n=\\\'\\\';1k.12.1P=\\\'#3D\\\';" 32="\'+j+\'">\'+i+"</1p></1s>":"<1s><1p 1D=\\"17\\" 1R=\\"1k.12.2n=\'#3E\';1k.12.1P=\'#29\';1h.3K(\'"+n+"3J"+R+"\').12.1w=\'1B\';\\" 1W=\\"1k.12.2n=\'\';1k.12.1P=\'#3D\';1h.3K(\'"+n+"3J"+R+\'\\\').12.1w=\\\'\\\';" ><1E 12="1D:6r;1G:4G;"><1E 2q="\'+n+"3J"+R+\'" 12="1G:2x;1x:1z;1t:6q;1P:#29;z-4D:0;2W-3F:2z;1y:2w%;1o:0;1O:0;28:0;">\'+i+\'</1E><1E 12="1G:2x;1x:3I;1t:4F;1P:#2X;-4A-4z-4y:1B;2W-3F:2z;1y:2w%;1o:0;1O:0;28:0;">\'+i+"</1E></1E></1p></1s>"},f:13(){16 i=f.$(n);11(!i){15 1g}i.12.1w="1B"},d:13(j){16 m=f.$(n);11(!m){15 1g}16 i=j||1i.1v;m.12.1t=(i.2F+(1h.6p.3z||1h.2V.3z))+"1f";m.12.1x=1h.2V.6o-i.2c<=4E?(i.2c-4E)+"1f":i.2c+"1f";m.12.1w="";15 1g},a:13(){11(!N.c){N.c=1;f.m?1h.1Y("6n",v):1h.1X("6m",v,1J)}11(!f.1Q[0]){15 1g}f.c.3C=13(){15 1g};16 j=1h.4g("6l");n=j.2q="M"+(19.3c().1U().6k(-6));j.12.6j="1w:1B;z-4D:2w;1G:2x;1x:0;1t:-4n;1y:6i;1o-1x:1z 1I #3H;1o-1t:1z 1I #3H;1o-3G:1z 1I #4C;1o-2o:1z 1I #4C;1O:0;28:0;";16 i=\'<1E 12="2W-6h:\\\'\\6g\\6f\\\',6e;2E:6d;6c-1P:#3H;1y:6b;1o-1x:1z 1I #29;1o-1t:1z 1I #29;1o-3G:1z 1I #2X;1o-2o:1z 1I #2X;1O:0;28:0;"><2l 1o="0" 3A="0" 12="2W-3F:2z;4B-6a:69;1O:1z;1y:68;4B-67:1x;-4A-4z-4y:1B; 1o-2m:2m" 66="15 1g;" 3C="15 1g">\';1C(R=1;R<f.1Q.1j;R++){i+=(!f.1Q[R]||!f.1Q[R][0]||f.1Q[R][0]=="")?H.b():H.c(f.1Q[R][0],f.1Q[R][1]||R)}i+=H.b();i+="<1s><1p 1D=\\"17\\" 1R=\\"1k.12.2n=\'#3E\';1k.12.1P=\'#29\';\\" 1W=\\"1k.12.2n=\'\';1k.12.1P=\'#3D\';\\" 32=\\"2u(\'"+f.F.4x("\\\\n")+"\')\\">65 "+f.F[0]+"</1p></1s></2l></1E>";j.1q=i;11(f.m){1i.1Y("64",13(){1h.2V.2K(j)});f.c.1Y("3C",H.d);1h.1Y("2J",H.f)}1e{1i.1X("63",13(){1h.2V.2K(j)},1J);f.c.1X("62",H.d,1J);1h.1X("61",H.f,1J)}}};H.a();11(f.1T<20){f.1T=20}11(f.1S<2G){f.1S=2G}11(f.1F==0){f.1F=1l(f.c.12.1y)}11(f.1N==0){f.1N=1l(f.c.12.1D)}f.c.12.1y=f.1F+"1f";f.c.12.1D=f.1N+"1f";11(1L f.18=="2t"){f.18=L[f.18.1U().2v()]}G=f.18>1?"<2l 4s=\'0\' 3A=\'0\' 12=\'1o-2m:2m;1w:3u;\'><1s><1p 3h=1J 12=\'4w-4v: 3g;4u-2I:4t-3B;\'>2h</1p><1p 3h=1J 12=\'4w-4v: 3g;4u-2I:4t-3B;\'>2h</1p></1s></2l>":"<2l 4s=\'0\' 3A=\'0\' 12=\'1o-2m:2m;\'><1s><1p>2h</1p></1s><1s><1p>2h</1p></1s></2l>";r=f.18>1?f.1F:f.1N;P=f.18>1?"60":"5Z";f.d=f.18>1?"5Y":"3z";f.w=f.18>1?"1x":"1t";11(f.18>4){f.18=2}11(f.18<-1){f.18=0}f.n=f.c.1q;16 c={0:13(){15 1},5X:13(i,m,j){15 m*(i/=j)*i},5W:13(i,m,j){15-m*(i/=j)*(i-2)},5V:13(i,m,j){11((i/=j/2)<1){15 m/2*i*i}15-m/2*((--i)*(i-2)-1)},5U:13(i,m,j){15 m*(i/=j)*i*i},5T:13(i,m,j){15 m*((i=i/j-1)*i*i+1)},5S:13(i,m,j){11((i/=j/2)<1){15 m/2*i*i*i}15 m/2*((i-=2)*i*i+2)},5R:13(i,m,j){15 m*(i/=j)*i*i*i},5Q:13(i,m,j){15-m*((i=i/j-1)*i*i*i-1)},5P:13(i,m,j){11((i/=j/2)<1){15 m/2*i*i*i*i}15-m/2*((i-=2)*i*i*i-2)},5O:13(i,m,j){15 m*(i/=j)*i*i*i*i},5N:13(i,m,j){15 m*((i=i/j-1)*i*i*i*i+1)},5M:13(i,m,j){11((i/=j/2)<1){15 m/2*i*i*i*i*i}15 m/2*((i-=2)*i*i*i*i+2)},5L:13(i,m,j){15-m*19.4r(i/j*(19.1H/2))+m},5K:13(i,m,j){15 m*19.2A(i/j*(19.1H/2))},5J:13(i,m,j){15-m/2*(19.4r(19.1H*i/j)-1)},5I:13(i,m,j){15(i==0)?0:m*19.1V(2,10*(i/j-1))},5H:13(i,m,j){15(i==j)?m:m*(-19.1V(2,-10*i/j)+1)},5G:13(i,m,j){11(i==0){15 0}11(i==j){15 m}11((i/=j/2)<1){15 m/2*19.1V(2,10*(i-1))}15 m/2*(-19.1V(2,-10*--i)+2)},5F:13(i,m,j){15-m*(19.2U(1-(i/=j)*i)-1)},5E:13(i,m,j){15 m*19.2U(1-(i=i/j-1)*i)},5D:13(i,m,j){11((i/=j/2)<1){15-m/2*(19.2U(1-i*i)-1)}15 m/2*(19.2U(1-(i-=2)*i)+1)},2N:13(j,W,V){16 m=1.2k;16 U=0;16 i=W;11(j==0){15 0}11((j/=V)==1){15 W}11(!U){U=V*0.3}11(i<19.2D(W)){i=W;16 m=U/4}1e{16 m=U/(2*19.1H)*19.3y(W/i)}15-(i*19.1V(2,10*(j-=1))*19.2A((j*V-m)*(2*19.1H)/U))},4b:13(j,W,V){16 m=1.2k;16 U=0;16 i=W;11(j==0){15 0}11((j/=V)==1){15 W}11(!U){U=V*0.3}11(i<19.2D(W)){i=W;16 m=U/4}1e{16 m=U/(2*19.1H)*19.3y(W/i)}15 i*19.1V(2,-10*j)*19.2A((j*V-m)*(2*19.1H)/U)+W},5C:13(j,W,V){16 m=1.2k;16 U=0;16 i=W;11(j==0){15 0}11((j/=V/2)==2){15 W}11(!U){U=V*(0.3*1.5)}11(i<19.2D(W)){i=W;16 m=U/4}1e{16 m=U/(2*19.1H)*19.3y(W/i)}11(j<1){15-0.5*(i*19.1V(2,10*(j-=1))*19.2A((j*V-m)*(2*19.1H)/U))}15 i*19.1V(2,-10*(j-=1))*19.2A((j*V-m)*(2*19.1H)/U)*0.5+W},3b:13(i,U,m,j){11(j==3x){j=1.2k}15 U*(i/=m)*i*((j+1)*i-j)},4a:13(i,U,m,j){11(j==3x){j=1.2k}15 U*((i=i/m-1)*i*((j+1)*i+j)+1)},5B:13(i,U,m,j){11(j==3x){j=1.2k}11((i/=m/2)<1){15 U/2*(i*i*(((j*=(1.4q))+1)*i-j))}15 U/2*((i-=2)*i*(((j*=(1.4q))+1)*i+j)+2)},4p:13(i,m,j){15 m-c.3w(j-i,m,j)},3w:13(i,m,j){11((i/=j)<(1/2.75)){15 m*(7.2T*i*i)}1e{11(i<(2/2.75)){15 m*(7.2T*(i-=(1.5/2.75))*i+0.75)}1e{11(i<(2.5/2.75)){15 m*(7.2T*(i-=(2.25/2.75))*i+0.5A)}1e{15 m*(7.2T*(i-=(2.5z/2.75))*i+0.5y)}}}},5x:13(i,m,j){11(i<j/2){15 c.4p(i*2,m,j)*0.5}15 c.3w(i*2-j,m,j)*0.5+m*0.5}};11(f.1a.1j){f.k=(13(i){f.1a=f.1a[0]<1?4o(f.1a[0].1U().3f("0.","")):4o(f.1a[0]);1C(R 5w c){16 j=++j||0;11((R.2S(4)==i||i.2S(4)==R||R==i||i==j)&&j>0){E=j;15 R}}})(f.1a[1].1U().2v()).1U()}11(f.1a<1){f.k=f.1a}11(f.1m>0||(f.1n>0&&f.1b==-10)){f.1b=r}11(f.1b==-10){f.1b=1A}f.2Y=f.1a;11(f.1m>1){11(f.1m==3){f.1m=0;f.e=M=1}1e{f.1m==2?D=1:f.q=f.m?f.1m:1-(D=1);11(f.m&&f.q>0){f.c.12.39="21(5v="+(f.1n/5u*0.2)+",3d="+(f.q-10)+")";11(f.q>=33||f.q<10){f.c.2g.21.3d=19.2b(19.3c()*22)}f.c.2g.21.4d();f.c.2g.21.4c()}f.1a=r;f.k=1-(f.1m=1)}}11(f.2j){16 J=(1L f.2j)=="2t"?[f.2j]:f.2j;1C(R=0;R<J.1j;R++){16 y=f.$(J[R]);11(y&&y.12.1w=="1B"){f.f=1;p[R]=[y,g(y,"1G","3v"),g(y,"1t")];y.12.1G="2x";y.12.41="2O";y.12.1t="-4n";y.12.1w="4m"}}}f.G=13(){11(1L f.a=="3l"&&(!f.a.1j||f.a.1j>0)){f.D=f.$(f.a[1])||f.$(f.a.5t);11(f.D){16 2i=0;16 m=0;11(f.D.3j=="4l"){f.D.12.2Q="0"}f.D.12.1w="3u-4m";f.D.12.1O=f.D.12.28=0;f.D.12.3k="1B";1C(16 W=0,V=f.D.1u.1j;W<V;W++){16 1d=f.D.1u[W];11(1d.3i==1){m++;1d.12.3k="1B";1d.12.4i=1d.12.4h="1x";1d.12.1w="3u";11(f.18>1){1d.12.3t=g(1d,"3t");1d.12.3s=g(1d,"3s");1d.12.3r=g(1d,"3r");1d.12.3q=g(1d,"3q");2i+=1d[P]+1l(1d.12.3t)+1l(1d.12.3s)+1l(1d.12.3r)+1l(1d.12.3q)}1e{1d.12.3p=g(1d,"3p");1d.12.3o=g(1d,"3o");1d.12.3n=g(1d,"3n");1d.12.3m=g(1d,"3m");2i+=1d[P]+1l(1d.12.3p)+1l(1d.12.3o)+1l(1d.12.3n)+1l(1d.12.3m)}11(M){a[a.1j]=1d}}}f.18>1?(f.D.12.1y=2i+"1f")||(f.D.12.1D=f.1N+"1f"):(f.D.12.1D=2i+"1f")||(f.D.12.1y=f.1F+"1f");f.C=1l(f.c[P]/f.1b);f.B=f.1c=1;f.v=[];11(1L f.a=="3l"&&(!f.a.1j||f.a.1j>1)){16 Z=f.$(f.a[2])||f.a[2]||f.$(f.a.5s);11(Z&&(Z.1j||Z.3j)){16 Y=(f.a[3]||f.a.5r||"1R").1U().2v();(Y=="1R"||Y=="2J")?0:Y="1R";16 U=(f.a[4]||f.a.5q||0);f.1b=r;11(f.1a>=f.1b){f.1a=r-1}16 2y=1l(f.c[P]/f.1b);l=Z.1j||1;16 X=0,1r=Z;1C(16 2R=0;2R<l;2R++){11(l>1){1r=f.$(Z[2R])}1r.12.1O=1r.12.28=0;1r.12.3k="1B";11(1r.3j=="4l"){1r.12.2Q="0"}f.C=1r.1u.1j;1C(16 W=0;W<f.C;W++){11(1r.1u[W].3i==1){11(2y==X){1r.1u[W].12.1w="1B";5p}1r.1u[W].12.2Q=g(1r.1u[W],"2Q","2z");1r.1u[W].12.3e="2O";f.v[X]=1r.1u[W];f.v[X][Y]=(13(i){15 13(){F=1K(13(){f.u=1;1C(16 2P=0;2P<f.v.1j;2P++){f.v[2P].2f=""}f.v[i].2f=e;s(i)},U)}})(X);f.v[X].1W=(13(i){15 13(){1Z(F);11(f.u==1){f.u=0;11(f.i==0){2e(t);t=2M(w,f.1T)}1e{1Z(q);q=1K(k,f.1n)}C(i)}}})(X);X+=1}}}f.C=X;f.r=f.C>1?1:0;11(f.v.1j>0){f.v[0].2f=e}11(2y>f.C){f.s=2y-f.C;1C(16 V=0;V<f.v.1j;V++){f.v[V].1R=f.v[V].1W=13(){}}f.C=2y;2u("4k ["+f.c.2q+"] 4j 5o 5n 5m 5l ["+f.s+"] 5k 5j 4j!")||0}}}11(M==0&&f.1m==0&&D==0&&f.q==0&&f.18>=0&&f.18<=3){11(f.18>1){f.D.12.1y=2i*2+"1f";f.D.12.1D=f.1N+"1f";f.D.1q+=f.D.1q}1e{f.D.12.4i=f.D.12.4h="1x";f.D.12.1y=f.1F+"1f";f.c.1q+=f.c.1q}}}}};f.G();11(f.B==0&&M){1C(16 R=0,Q=f.c.1u.1j;R<Q;R++){11(f.c.1u[R].3i==1){a[a.1j]=f.c.1u[R]}}}11(f.B==0&&M==0&&(f.18==0||f.18==1)){f.c.1q=G.3f(/2h/g,f.c.1q)}1e{11(f.B==0&&M==0&&(f.18==2||f.18==3)){f.c.3h=1J;f.c.12.5i="3g";(f.1b==0&&f.1n==0)?f.c.1q+=f.c.1q:f.c.1q=G.3f(/2h/g,f.c.1q)}1e{11(f.18==4||f.18==-1){f.18=6%f.18;f.e=1}}}K=f.1b;u=f.18;O=f.k;11(f.1m>0){f.E=1h.4g("1E");f.E.12.1y=f.1F+"1f";f.E.12.1D=f.1N+"1f";f.E.12.1G="2x";f.E.12[f.w]=f.18%2?-r+"1f":r+"1f";f.18>1?f.E.12.1t="0":f.E.12.1x="0";f.E.1q=f.c.1q;f.E.12.3e=f.E.12.4f=f.E.12.4e="2O";f.c.2K(f.E);f.E[f.d]=r}16 w=13(){f.k>0?f.1a=19.5h((f.1b-b)*f.k):0;f.2s()};16 C=13(j){11(f.i==2){15 1g}11(f.1m>0&&(D==1||f.q>0)){j+1>=f.C?f.E[f.d]=0:f.E[f.d]=r*(j+1)}};16 s=13(j){11(f.i==2){15 1g}1Z(q);2e(t);11(f.1m==0){f.1c=1l(f.c[f.d]/K)+1;11(f.1c>f.C){f.1c-=f.C}j+=1;f.u=1;11(j<=f.1c){f.1b=K*(f.1c-j)+f.c[f.d]%K;f.18=f.18>1?3:1}1e{11(j-f.1c==f.C-1){11(E==24){f.k="2N"}11(E==27||E==26){f.k="3b"}}11(f.1c<=2&&j==f.C&&E==23){f.k="2N"}f.1b=K*(j-f.1c)-f.c[f.d]%K;f.18=f.18>1?2:0}}1e{11(f.i==0&&f.e==1){f.18=f.18>1?5-f.18:1-f.18;u=f.18}f.18%2?f.E.12[f.w]=-r+f.c[f.d]+"1f":f.E.12[f.w]=r+f.c[f.d]+"1f";j==f.C?f.E[f.d]=1-(f.1c=1):f.E[f.d]=r*(j);f.1c=j+1}b=0;11(f.1b>0){k()}};16 I=13(m){1Z(B);16 j=m||f.c;16 i=5;(13(){f.m?j.12.39="46(2L="+i+")":j.12.2L=(i/2w);i+=5;11(i<=2w){B=1K(1M.5g,20)}})()};16 k=13(){16 m=f.18%2;11(D==1){I()}11(f.q>0){11(f.q>=33||f.q<10){f.c.2g.21.3d=19.2b(19.3c()*22)}f.c.2g.21.4d();f.c.2g.21.4c()}11(f.j==1){q=1K(k,f.1n)}1e{2e(t);h=A=b=f.i=0;11(f.e==1&&f.k>0){11((f.18==2||f.18==0)&&f.c[P]-r-f.c[f.d]<f.1b){f.1b=f.c[P]-r-f.c[f.d]}11(m&&f.c[f.d]<f.1b){f.1b=f.c[f.d]}}11(f.B==1&&f.u==0){f.1c=1l(f.c[f.d]/K)+2;11(f.e==1){11(f.1m==1){11(f.1c>f.C){f.1c=1}}1e{11(m){f.1c-=2;11(f.1c<=0){f.1c=1}}1e{11(f.1c>=f.C){f.1c=f.C}}}}1e{11(f.1c>f.C){f.1c-=f.C}}11(f.r==1&&f.s==0){1C(16 j=0;j<f.C;j++){f.v[j].2f=""}f.e==0&&D==0&&f.q==0&&f.1m==0&&m?f.v[f.1c+f.C-2>f.C?f.1c-3:f.1c+f.C-3].2f=e:f.v[f.1c-1].2f=e}}11(f.e==1&&f.C>1){11(((E==22||E==24)&&!m&&f.1c==2)||((E==22||E==24)&&m&&f.1c==f.C-1)){f.k="4b"}11(((E==23||E==24)&&!m&&f.1c==f.C)||((E==23||E==24)&&m&&f.1c==1)){f.k="2N"}11(((E==25||E==27)&&!m&&f.1c==2)||((E==25||E==27)&&m&&f.1c==f.C-1)){f.k="4a"}11(((E==26||E==27)&&!m&&f.1c==f.C)||((E==26||E==27)&&m&&f.1c==1)){f.k="3b"}}11(f.e==1&&f.2d>=0){f.2d=-1;f.49()}t=2M(w,f.1T);f.48()}};f.36=13(j){11(f.i==1){16 i=-1;11(1L 1M[0]=="5f"){i=1M[0]}11(1L 1M[0]=="2t"){u=i=L[1M[0].1U().2v()]}11(i<0||i>3){2u("5e 5d 5c!");15 1g}11(f.2d==i){15 1g}f.18=i;11(1L 1M[0]=="2t"||f.e==1){u=f.18}f.3a()}};f.45=f.49=f.48=f.44=13(){};f.3a=13(){1Z(q);q=1K(k,1M[0])};f.5b=13(){f.47();f.c[f.d]=f.i=h=A=0;f.i=-1};f.2B=13(){11(f.i==2&&!1M[0]){f.i=b>1?0:1;t=2M(w,f.1T);15 1g}11(f.i==0){2e(t);t=2M(w,f.1T)}11(f.i==1){f.3a(f.1n)}};f.47=13(){11(f.i!=2){f.2C();f.i=2}};f.2C=f.5a=f.59=13(){1Z(F);2e(t);1Z(q)};16 x=13(){11(S){I(S)}16 j=f.B==0?f.c:f.D,U=f.18%2;16 m=U?a.1j-1:0;S=a[m].58(1J);f.1b=a[m][P];f.m?S.12.39="46(2L = 0)":S.12.2L=0;U?j.57(S,j.1u[0]):j.2K(S);j.56(a[m]);U?f.c[f.d]=f.1b:f.c[f.d]=f.c[P]-r-f.1b;11(U){a.55(S);a.54()}1e{a.53(S);a.52()}};16 o=13(){2e(t);h=A=b=1-(f.i=1);f.1b=K;f.18=u;f.k=O;11(f.u==1){15 1g}11(f.e==1){11(f.1m==1){u=f.18=f.18>1?5-f.18:1-f.18}1e{11(f.c[f.d]==0||f.c[f.d]>=f.c[P]-r){11(M){x()}1e{f.2d=f.18;f.45();u=f.18=f.18>1?5-f.18:1-f.18}}}}f.44();q=1K(k,f.1n)};16 z=13(){f.G();1K(d,0)};16 d=13(){f.y=f.c[P]/2;11(f.1m==0&&f.y<=r+f.1a&&f.e==0){11(f.B==1){16 j=f.c.51("43")/1+1||1;f.c.50("43",j);11(j<5){z()}}1e{f.c.1q=f.n;42(f.n)}15 1g}42(f.n);11(f.e==1){f.2d=f.18>1?5-f.18:1-f.18}11(f.f==1){1C(R=0;R<p.1j;R++){16 i=p[R][0];i.12.1G=p[R][1];i.12.1w="1B";i.12.41="4Z";i.12.1t=p[R][2]}}11(f.1n>0&&f.38){f.38.2J=13(){f.36(f.18>1?5-f.18:1-f.18)}}11(f.1n>0&&f.37){f.37.2J=13(){f.36(f.18)}}f.c[f.d]=f.i=h=A=0;11(M){x()}T=19.2b(f.1b/f.1a);11(f.1S>=4Y){f.i=1;11(f.1S==4X){f.1n=40}f.1S=40;15}1K(13(){11((f.1b>=0&&(f.B==0||(f.B==1&&(f.r==0||f.r==1)))&&f.l)||(f.1n==0&&f.1b==-2&&f.l)){f.l()}k()},f.1S)};11(f.1m==1){f.2s=13(){16 i=f.18%2;b+=f.1a;11(b>=f.1b&&f.1n>0){i?f.E.12[f.w]=(1l(f.E.12[f.w])-(f.1b-f.1a-b))+"1f":f.E.12[f.w]=(1l(f.E.12[f.w])+(f.1b-f.1a-b))+"1f";o();f.c[f.d]=f.E[f.d];i?f.E.12[f.w]=-r+f.c[f.d]+"1f":f.E.12[f.w]=r+f.c[f.d]+"1f";f.1c==f.C?f.1c=1-(f.E[f.d]=0):f.E[f.d]=r*(f.1c);15}3Z(i){2H 0:f.E.12[f.w]=(1l(f.E.12[f.w])-f.1a)+"1f";2I;2H 1:f.E.12[f.w]=(1l(f.E.12[f.w])+f.1a)+"1f"}}}1e{11(1L f.k=="2t"){f.2s=13(){b+=h;11(h<=T){A+=(f.1a=19.2b(c[f.k](h++,f.1b,T))-A);11(f.c[f.d]<=f.1a&&f.e==0){f.c[f.d]+=f.y}f.18%2?f.c[f.d]-=f.1a:f.c[f.d]+=f.1a}1e{11(f.c[f.d]>f.y&&f.e==0){f.c[f.d]-=f.y}o()}}}1e{f.2s=13(){16 i=f.18%2;b+=f.1a;11((b>=f.1b&&f.1n>0)||(f.e==1&&b>f.1a&&(f.c[f.d]<=0||f.c[f.d]>=f.c[P]-r))){i?f.c[f.d]-=f.1b+f.1a-b:f.c[f.d]+=f.1b+f.1a-b;o();15}3Z(i){2H 0:11(f.c[f.d]>=f.y&&f.e==0){f.c[f.d]-=f.y}f.c[f.d]+=f.1a;2I;2H 1:11(f.c[f.d]<=f.1a&&f.e==0){f.c[f.d]+=f.y}f.c[f.d]-=f.1a}}}}1K(d,2G)};2r.3Y.l=13(){16 g=1k,f=1A,j=1A,k=0,m=0,b=0,d=0,i=0,h=19.2b(g.1F/2);16 a=13(n){16 n=n||1i.1v;11(g.1n!=0||(n.3X&&n.3X!=1)||(n.3W&&n.3W!=1)||g.i==2||g.i==-1){15 1g}11(k==0){g.i=k=1;g.2C();n.3V?n.3V():n.4W=1J;n.3U?n.3U():n.4V=1g;b=g.c[g.d];m=g.18>1?1i.1v?n.2c:n.35:1i.1v?n.2F:n.34;f=n.2Z||n.4U;j=f.12.2E;f.12.2E="4T";11(g.m){f.4S();1h.1Y("31",e);1h.1Y("3S",c)}1e{1h.1X("3R",e,1g);1h.1X("3P",c,1g)}}};16 e=13(o){16 o=o||1i.1v;d=g.18>1?1i.1v?o.2c:o.35:1i.1v?o.2F:o.34;16 n=m-d+b;11(n<=0||n>=g.y){n<=0?g.c[g.d]+=g.y:g.c[g.d]=g.y-n;m=g.18>1?1i.1v?o.2c:o.35:1i.1v?o.2F:o.34;b=g.c[g.d];n=m-d+b}g.c[g.d]=n};16 c=13(n){11(g.m){f.4R();1h.3T("31",e);1h.3T("3S",c)}1e{1h.3Q("3R",e,1g);1h.3Q("3P",c,1g)}k=g.j=0;f.12.2E=j;g.2B()};11(g.e==0){g.m?g.c.1Y("32",a):g.c.1X("4Q",a,1g)}11(g.1b==-2){g.c.31=13(n){11(g.18>1){16 n=n||1i.1v;11(1i.1v){11(g.3O){i=n.2Z.2q==g.c.2q?n.3N-g.c[g.d]:n.2Z.4P-g.c[g.d]+n.3N}1e{g.1b=1A;15}}1e{i=n.4O-g.c[g.d]}g.18=i>h?3:2;g.1a=19.2b(19.2D(h-i)*(g.2Y*2)/h)}};g.c.1W=13(){11(g.1a==0){g.1a=1}}}1e{g.c.1R=13(){11((g.i==0&&g.1n>0)||g.i==2||g.i==-1){15 1g}g.j=1;g.2C()};g.c.1W=13(){11(g.j==1){g.j=0;g.2B()}}}};',62,443,'|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||if|style|function||return|var||Direction|Math|Step|ScrollStep|Counter|ae|else|px|false|document|window|length|this|parseInt|SwitchType|DelayTime|border|td|innerHTML|ad|tr|top|childNodes|event|display|left|width|1px|null|none|for|height|div|Width|position|PI|solid|true|setTimeout|typeof|arguments|Height|margin|color|ContextMenu|onmouseover|WaitTime|Timer|toString|pow|onmouseout|addEventListener|attachEvent|clearTimeout||revealTrans|||||||padding|FFF|currentStyle|round|clientX|Bound|clearInterval|className|filters|MSCLASS_TEMP_HTML|ab|HiddenID|70158|table|collapse|backgroundColor|bottom|getComputedStyle|id|Marquee|Scroll|string|alert|toLowerCase|100|absolute|ac|12px|sin|Continue|Pause|abs|cursor|clientY|800|case|break|onclick|appendChild|opacity|setInterval|inelastic|hidden|af|fontSize|aa|substr|5625|sqrt|body|font|808080|BakStep|srcElement||onmousemove|onmousedown||pageY|pageX|Run|NextBtn|PrevBtn|filter|Play|inback|random|Transition|overflow|replace|nowrap|noWrap|nodeType|tagName|listStyle|object|borderBottomWidth|borderTopWidth|marginBottom|marginTop|borderRightWidth|borderLeftWidth|marginRight|marginLeft|inline|static|outbounce|undefined|asin|scrollTop|cellpadding|all|oncontextmenu|000|0A246A|size|right|D4D0C8|0px|_|getElementById|keyCode|MSClass|offsetX|IsNotOpera|mouseup|removeEventListener|mousemove|onmouseup|detachEvent|preventDefault|stopPropagation|button|which|prototype|switch|3600000|visibility|delete|fixnum|OnPause|OnBound|alpha|Stop|OnScroll|UnBound|outback|outelastic|play|apply|overflowY|overflowX|createElement|cssFloat|styleFloat|pages|The|UL|block|10000px|parseFloat|inbounce|525|cos|cellspacing|keep|word|space|white|join|select|user|moz|text|404040|index|170|2px|relative|medium|auto|Start|MSClassID|97|99|79|layerX|offsetLeft|mousedown|releaseCapture|setCapture|move|target|returnValue|cancelBuble|100000|60000|visible|setAttribute|getAttribute|shift|push|pop|unshift|removeChild|insertBefore|cloneNode|Terminate|Destroy|Reset|wrong|set|Parameters|number|callee|ceil|whiteSpace|actual|than|less|numbers|tab|or|continue|TabTimeout|TabEvent|TabID|ContentID|1000|Duration|in|inoutbounce|984375|625|9375|inoutback|inoutelastic|inoutcirc|outcirc|incirc|inoutexpo|outexpo|inexpo|inoutsine|outsine|insine|inoutquint|outquint|inquint|inoutquart|outquart|inquart|inoutcubic|outcubic|incubic|inoutquad|outquad|inquad|scrollLeft|scrollHeight|scrollWidth|click|contextmenu|load|onload|About|onselectstart|align|166px|10px|indent|168px|background|default|arial|u4F53|u5B8B|family|170px|cssText|slice|DIV|keydown|onkeydown|clientWidth|documentElement|3px|17px|valign|isNaN|113|witch|1em|30pt|alterleft|down|up|alterup|altertop|active|AutoStart|opera|indexOf|userAgent|navigator|NextBtnID|PrevBtnID|error|initialization|constructor|com|21cn|zhadan007|333|Cui|Yongxiang|html|script|net|popub|www|http|JS|Scrolling|Uninterrupted|General|Of||Class|110708|85'.split('|'),0,{}))
//-->
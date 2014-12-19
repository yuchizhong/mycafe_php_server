<?php

echo enc("0");

function uhash($str) {
	return md5(md5(md5($str)));
}

function enc($str) {
	return encode_pass($str, "8FH43N0F02INF01NFFE9");
}

function dec($str) {
        return encode_pass($str, "8FH43N0F02INF01NFFE9", 'decode');;
}

function encode_pass($tex,$key,$type="encode"){
    $chrArr=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
                  'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                  '0','1','2','3','4','5','6','7','8','9');
    if($type=="decode"){
        if(strlen($tex)<14)return false;
        $verity_str=substr($tex, 0,8);
        $tex=substr($tex, 8);
        if($verity_str!=substr(md5($tex),0,8)){
            //完整性验证失败
            return false;
        }    
    }
    $key_b=$type=="decode"?substr($tex,0,6):$chrArr[rand()%62].$chrArr[rand()%62].$chrArr[rand()%62].$chrArr[rand()%62].$chrArr[rand()%62].$chrArr[rand()%62];
    $rand_key=$key_b.$key;
    $rand_key=md5($rand_key);
    $tex=$type=="decode"?base64_decode(substr($tex, 6)):$tex;
    $texlen=strlen($tex);
    $reslutstr="";
    for($i=0;$i<$texlen;$i++){
        $reslutstr.=$tex{$i}^$rand_key{$i%32};
    }
    if($type!="decode"){
        $reslutstr=trim($key_b.base64_encode($reslutstr),"==");
        $reslutstr=substr(md5($reslutstr), 0,8).$reslutstr;
    }
    return $reslutstr;
}

?>

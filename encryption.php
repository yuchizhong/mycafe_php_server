<?php

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
    return $tex;
}

?>

<?php

//must be 16 bytes
$strEncryptionPassword = 'Unicoffee2014168';

//** PHP's mcrypt does not have built in PKCS5 Padding, so we use this
function addPKCS5Padding($text) {
	$blocksize = 16;
        $pad = $blocksize - (strlen($text) % $blocksize);
       	return $text . str_repeat(chr($pad), $pad);
}

function removePKCS5Padding($text){
         $pad = ord($text{strlen($text)-1});
         if ($pad > strlen($text)) {
             return $text;
         }
         if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
             return $text;
         }
         return substr($text, 0, -1 * $pad);
}

//** Wrapper function do encrypt an encode based on strEncryptionType setting **
function encryptAndEncode($strIn) {

global $strEncryptionPassword;

{
    //** AES encryption, CBC blocking with PKCS5 padding then HEX encoding **

    //** use initialization vector (IV) set from $strEncryptionPassword
    $strIV = $strEncryptionPassword;

    //** add PKCS5 padding to the text to be encypted
    $strIn = addPKCS5Padding($strIn);

    //** perform encryption with PHP's MCRYPT module
    $strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $strEncryptionPassword, $strIn, MCRYPT_MODE_CBC, $strIV);

    //** perform hex encoding and return
    return bin2hex($strCrypt);
}
}


//** Wrapper function do decode then decrypt based on header of the encrypted field **
function decodeAndDecrypt($strIn) {

global $strEncryptionPassword;

{
  //** HEX decoding then AES decryption, CBC blocking with PKCS5 padding  **

  //** use initialization vector (IV) set from $strEncryptionPassword
  $strIV = $strEncryptionPassword;

  //** HEX decoding
  $strIn = pack('H*', $strIn);

  //** perform decryption with PHP's MCRYPT module
  return removePKCS5Padding(
      mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $strEncryptionPassword, $strIn, MCRYPT_MODE_CBC, $strIV));
}
}

function upmd5($str) {
	return strtoupper(md5($str));
}

function uhash($str) {
	return menc(upmd5(upmd5(upmd5($str))));
}

function menc($str) {
        return strtoupper(encryptAndEncode($str));
}

function mdec($str) {
        return decodeAndDecrypt(strtolower($str));
}

//purse
function enc($str) {
	return $str;
}

function dec($str) {
        return $str;
}

?>

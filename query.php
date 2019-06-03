<?php

$ss = $_GET["ss"] ? $_GET["ss"] : 10;

echo "<BR>\n";
$fullText = microtime();
// 設定公、私鑰檔名
const PRIVATE_KEY = 'private_2048.key';
const PUBLIC_KEY = 'public_2048.crt';

// 如果設定密碼
// const PASSPHRASE = 'my_pasword';

function public_encrypt($plain_text)
{
    $fp      = fopen(PUBLIC_KEY, "r");
    $pub_key = fread($fp, 8192);
    fclose($fp);
    $pub_key_res = openssl_get_publickey($pub_key);
    if (!$pub_key_res) {
        throw new Exception('Public Key invalid');
    }
    openssl_public_encrypt($plain_text, $crypt_text, $pub_key_res, OPENSSL_PKCS1_OAEP_PADDING);
    openssl_free_key($pub_key_res);
    return base64_encode($crypt_text); // 加密後的內容為 binary 透過 base64_encode() 轉換為 string 方便傳輸
}

function private_decrypt($encrypted_text)
{
    $fp       = fopen(PRIVATE_KEY, "r");
    $priv_key = fread($fp, 8192);
    fclose($fp);
    $private_key_res = openssl_get_privatekey($priv_key);
    // $private_key_res = openssl_get_privatekey($priv_key, PASSPHRASE); // 如果使用密碼
    if (!$private_key_res) {
        throw new Exception('Private Key invalid');
    }
    
    // 先將密文做 base64_decode() 解釋
    openssl_private_decrypt(base64_decode($encrypted_text), $decrypted, $private_key_res, OPENSSL_PKCS1_OAEP_PADDING);
    openssl_free_key($private_key_res);
    return $decrypted;
}

echo $ss . "<BR>\n";

$t1 = microtime(true);
// 將資料進行加密
$r0 = $fullText;
for ($i = 0; $i < $ss; $i++) {
    $r[$i] = public_encrypt($i . ":" . $r0);
    #var_dump($r[$i]);
    #echo "<BR>\n";
}

$t2 = microtime(true);
// 將資料進行解密
for ($i = 0; $i < $ss; $i++) {
    $rr[$i] = private_decrypt($r[$i]);
    #var_dump($rr[$i]);
    #echo "<BR>\n";
}


$t3 = microtime(true);
echo $t2 - $t1 . "sec<BR>\n";
echo $t3 - $t2 . "sec<BR>\n";

$ch = curl_init("http://tttt-cacti.apps.example.com/q1.php?ss=" . $ss);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, 0);
$data = curl_exec($ch);
curl_close($ch);
echo $data;
?>

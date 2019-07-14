<?php
// CryptoEx Class Library V.1.2 By phpseclib and NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// PHP Secure Communications Library http://phpseclib.sourceforge.net is under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class CryptoEx
{
	protected $KEY;
	protected $IV;
	protected $LastError;
	protected $PRVKEY;
	protected $PUBKEY;
	
	public function __construct($CryptConfig="")
	{
		if(is_array($CryptConfig))
		{
			$this->KEY = $CryptConfig['KEY'];
			$this->IV = $CryptConfig['IV'];
			$this->PRVKEY = $CryptConfig['PRVKEY'];
			$this->PUBKEY = $CryptConfig['PUBKEY'];
		}
	}
	
	public function RSAEncrypt($PlainText,$PUBKEY=null)
	{
		try{
			if(!isset($PUBKEY) or empty($PUBKEY))
				$PUBKEY = $this->PUBKEY;
			$rsa = new Crypt_RSA();
			$rsa->setEncryptionMode(1);
			$rsa->setMGFHash('sha1');
			$rsa->setHash('sha256');
			$rsa->loadKey($PUBKEY);
			return base64_encode($rsa->encrypt($PlainText));
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function RSADecrypt($Cypher,$PRVKEY=null)
	{
		try{
			if(!isset($PRVKEY) or empty($PRVKEY))
				$PRVKEY = $this->PRVKEY;
			$rsa = new Crypt_RSA();
			$rsa->setEncryptionMode(1);
			$rsa->setMGFHash('sha1');
			$rsa->setHash('sha256');
			$rsa->loadKey($PRVKEY);
			return $rsa->decrypt(base64_decode($Cypher));
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function GenKeyPair($keysize=4096)
	{
		if(intval($keysize) < 1024 or intval($keysize) > 4096)
			$keysize = 4096;
		try{
			$rsa = new Crypt_RSA();
			$rsa->setHash('sha512');
			$rsa->setComment('nocomment');
			$keys = $rsa->createKey($keysize);
			$this->PRVKEY = $keys["privatekey"];
			$this->PUBKEY = $keys["publickey"];
			return array('PUBKEY'=>$keys["publickey"],'PRVKEY'=>$keys["privatekey"]);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function OpenSSLGenKeyPair($keysize=4096)
	{
		if(intval($keysize) < 1024 or intval($keysize) > 102400)
			$keysize = 4096;
		try{
			$res = openssl_pkey_new(array(
			"digest_alg" => "sha512",
			"private_key_bits" => $keysize,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
			));
			openssl_pkey_export($res, $privKey);
			$pubKey = openssl_pkey_get_details($res);
			$pubKey = $pubKey["key"];
			$this->PRVKEY = $privKey;
			$this->PUBKEY = $pubKey;
			return array('PUBKEY'=>$pubKey,'PRVKEY'=>$privKey);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function WithHeaderKey($Input,$Type='PUBLIC')
	{
		if($Type == 'PUBLIC' or $Type == 1)
		{
			if(strpos($Input,"-----BEGIN PUBLIC KEY-----") === false)
			{
				return '-----BEGIN PUBLIC KEY-----'.PHP_EOL.$Input.PHP_EOL.'-----END PUBLIC KEY-----';
			}
			else
			{
				return $Input;
			}
		}
		else
		{
			if(strpos($Input,"-----BEGIN PRIVATE KEY-----") === false)
			{
				return '-----BEGIN PRIVATE KEY-----'.PHP_EOL.$Input.PHP_EOL.'-----END PRIVATE KEY-----';
			}
			else
			{
				return $Input;
			}
		}
	}
	
	public function NoHeaderKey($Input,$Type='PUBLIC')
	{
		if($Type == 'PUBLIC' or $Type == 1)
		{
			if(strpos($Input,"-----BEGIN PUBLIC KEY-----") === false)
				return $Input;
			$Input = str_replace("-----BEGIN PUBLIC KEY-----".PHP_EOL,"",$Input);
			$Input = str_replace("-----BEGIN PUBLIC KEY-----","",$Input);
			$Input = str_replace(PHP_EOL."-----END PUBLIC KEY-----","",$Input);
			$Input = str_replace("-----END PUBLIC KEY-----","",$Input);
			$Input = trim($Input);
			return $Input;
		}
		else
		{
			if(strpos($Input,"-----BEGIN PRIVATE KEY-----") === false)
				return $Input;
			$Input = str_replace("-----BEGIN PRIVATE KEY-----".PHP_EOL,"",$Input);
			$Input = str_replace(PHP_EOL."-----END PRIVATE KEY-----","",$Input);
			$Input = str_replace("-----BEGIN PRIVATE KEY-----","",$Input);
			$Input = str_replace("-----END PRIVATE KEY-----","",$Input);
			$Input = str_replace("-----BEGIN RSA PRIVATE KEY-----".PHP_EOL,"",$Input);
			$Input = str_replace(PHP_EOL."-----END RSA PRIVATE KEY-----","",$Input);
			$Input = str_replace("-----BEGIN RSA PRIVATE KEY-----","",$Input);
			$Input = str_replace("-----END RSA PRIVATE KEY-----","",$Input);
			$Input = trim($Input);
			return $Input;
		}
	}
	
	public function HighRandomString($Length=8)
	{
		try{
			$rsa = new Crypt_RSA();
			return $rsa->crypt_random_string($Length);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function PBKDF2($Password,$Salt,$Iteration=1000,$Length=20,$Algo="sha256")
	{
		try{
			if(!isset($Password,$Salt,$Iteration) or empty($Password) or empty($Salt) or empty($Iteration))
				return null;
			if(!isset($Salt) or empty($Salt))
				$Salt = openssl_random_pseudo_bytes(32);
			$hash = openssl_pbkdf2($Password, $Salt, $Length, $Iterations, $Algo);
			return bin2hex($hash);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function PBKDFHash2($Password,$Salt,$Iteration=1000,$Length=20,$Algo="sha256")
	{
		try{
			if(!isset($Password,$Salt,$Iteration) or empty($Password) or empty($Salt) or empty($Iteration))
				return null;
			if(!isset($Salt) or empty($Salt))
				$Salt = openssl_random_pseudo_bytes(32);
			$hash = hash_pbkdf2($Algo, $Password, $Salt, $Iterations, $Length);
			return $hash;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function OldDecrypt($Cypher,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw Exception('bad compression');
			}
			return $this->PKCS7_UnPadding((mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $KEY, $Cypher, MCRYPT_MODE_CBC, $IV)));
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function MyEncrypt($PlainText,$Mode,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		if(!isset($Mode) or empty($Mode))
			$Mode = $this->Mode;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			if($IsCompress)
			{
				$Cypher = openssl_encrypt($PlainText, $Mode, $KEY, OPENSSL_RAW_DATA, $IV);
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode(openssl_encrypt($PlainText, $Mode, $KEY, OPENSSL_RAW_DATA, $IV));
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function MyDecrypt($Cypher,$Mode,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		if(!isset($Mode) or empty($Mode))
			$Mode = $this->Mode;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			return openssl_decrypt($Cypher, $Mode, $KEY, OPENSSL_RAW_DATA, $IV);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function AdvDecrypt($Cypher,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			return openssl_decrypt($Cypher, 'AES-128-CBC', $KEY, OPENSSL_RAW_DATA, $IV);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function HighDecrypt($Cypher,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			return openssl_decrypt($Cypher, 'AES-256-CBC', $KEY, OPENSSL_RAW_DATA, $IV);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function AdvEncrypt($PlainText,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			if($IsCompress)
			{
				$Cypher = openssl_encrypt($PlainText, 'AES-128-CBC', $KEY, OPENSSL_RAW_DATA, $IV);
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode(openssl_encrypt($PlainText, 'AES-128-CBC', $KEY, OPENSSL_RAW_DATA, $IV));
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function HighEncrypt($PlainText,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			if($IsCompress)
			{
				$Cypher = openssl_encrypt($PlainText, 'AES-256-CBC', $KEY, OPENSSL_RAW_DATA, $IV);
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode(openssl_encrypt($PlainText, 'AES-256-CBC', $KEY, OPENSSL_RAW_DATA, $IV));
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function OldEncrypt($PlainText,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			if($IsCompress)
			{
				$Cypher = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $KEY, $this->PKCS7_Padding($PlainText), MCRYPT_MODE_CBC, $IV);
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $KEY, $this->PKCS7_Padding($PlainText), MCRYPT_MODE_CBC, $IV));
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function Encrypt($PlainText,$KEY,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		try{
			$KEY = base64_decode($KEY);
			if($IsCompress)
			{
				$Cypher = $this->SWAP(mcrypt_encrypt(MCRYPT_BLOWFISH, $KEY, $this->SWAP($this->PKCS5_Padding($PlainText)), 'ecb'));
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode($this->SWAP(mcrypt_encrypt(MCRYPT_BLOWFISH, $KEY, $this->SWAP($this->PKCS5_Padding($PlainText)), 'ecb')));
			}
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function Decrypt($Cypher,$KEY,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		try{
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			$KEY = base64_decode($KEY);
			return $this->PKCS5_UnPadding($this->SWAP(mcrypt_decrypt(MCRYPT_BLOWFISH, $KEY, $this->SWAP($Cypher), 'ecb')));
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function PKCS7_UnPadding($value)
	{
		try{
			$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$packing = ord($value[strlen($value) - 1]);
			if($packing && $packing < $blockSize)
			{
				for($P = strlen($value) - 1; $P >= strlen($value) - $packing; $P--)
				{
					if(ord($value{$P}) != $packing)
					{
						$packing = 0;
					}
				}
			}
			return substr($value, 0, strlen($value) - $packing);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function PKCS7_Padding($value)
	{
		try{
			$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$len = strlen($value);
			$padding = $block - ($len % $block);
			$value .= str_repeat(chr($padding),$padding);
			return $value;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function PKCS5_Padding($data)
	{
		try{
			$padlen = 8-(strlen($data) % 8);
			for ($i=0; $i<$padlen; $i++)
			$data .= chr($padlen);
			return $data;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function PKCS5_UnPadding($data)
	{
		try{
			$padlen = ord(substr($data, strlen($data)-1, 1));
			if ($padlen>8)
			return $data;
			for ($i=strlen($data)-$padlen; $i<strlen($data); $i++) {
				if (ord(substr($data, $i, 1)) != $padlen)
				return false;
			}
			return substr($data, 0, strlen($data)-$padlen);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function SWAP($data)
	{
		try{
			$res="";
			for ($i=0; $i<strlen($data); $i+=4) {
				list(,$val) = unpack('N', substr($data, $i, 4));
				$res .= pack('V', $val);
			}
			return $res;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}



define('CRYPT_HASH_MODE_INTERNAL', 1);
define('CRYPT_HASH_MODE_MHASH',    2);
define('CRYPT_HASH_MODE_HASH',     3);

class Crypt_Hash
{
    var $hashParam;
    var $b;
    var $l = false;
    var $hash;
    var $key = false;
    var $computedKey = false;
    var $opad;
    var $ipad;

    function __construct($hash = 'sha1')
    {
        if (!defined('CRYPT_HASH_MODE')) {
            switch (true) {
                case extension_loaded('hash'):
                    define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_HASH);
                    break;
                case extension_loaded('mhash'):
                    define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_MHASH);
                    break;
                default:
                    define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_INTERNAL);
            }
        }
        $this->setHash($hash);
    }

    function Crypt_Hash($hash = 'sha1')
    {
        $this->__construct($hash);
    }

    function setKey($key = false)
    {
        $this->key = $key;
        $this->_computeKey();
    }

    function _computeKey()
    {
        if ($this->key === false) {
            $this->computedKey = false;
            return;
        }
        if (strlen($this->key) <= $this->b) {
            $this->computedKey = $this->key;
            return;
        }
        switch ($mode) {
            case CRYPT_HASH_MODE_MHASH:
                $this->computedKey = mhash($this->hash, $this->key);
                break;
            case CRYPT_HASH_MODE_HASH:
                $this->computedKey = hash($this->hash, $this->key, true);
                break;
            case CRYPT_HASH_MODE_INTERNAL:
                $this->computedKey = call_user_func($this->hash, $this->key);
        }
    }

    function getHash()
    {
        return $this->hashParam;
    }

    function setHash($hash)
    {
        $this->hashParam = $hash = strtolower($hash);
        switch ($hash) {
            case 'md5-96':
            case 'sha1-96':
            case 'sha256-96':
            case 'sha512-96':
                $hash = substr($hash, 0, -3);
                $this->l = 12;
                break;
            case 'md2':
            case 'md5':
                $this->l = 16;
                break;
            case 'sha1':
                $this->l = 20;
                break;
            case 'sha256':
                $this->l = 32;
                break;
            case 'sha384':
                $this->l = 48;
                break;
            case 'sha512':
                $this->l = 64;
        }
        switch ($hash) {
            case 'md2-96':
            case 'md2':
                $this->b = 16;
            case 'md5-96':
            case 'sha1-96':
            case 'sha224-96':
            case 'sha256-96':
            case 'md2':
            case 'md5':
            case 'sha1':
            case 'sha224':
            case 'sha256':
                $this->b = 64;
                break;
            default:
                $this->b = 128;
        }
        switch ($hash) {
            case 'md2':
                $mode = CRYPT_HASH_MODE == CRYPT_HASH_MODE_HASH && in_array('md2', hash_algos()) ?
                    CRYPT_HASH_MODE_HASH : CRYPT_HASH_MODE_INTERNAL;
                break;
            case 'sha384':
            case 'sha512':
                $mode = CRYPT_HASH_MODE == CRYPT_HASH_MODE_MHASH ? CRYPT_HASH_MODE_INTERNAL : CRYPT_HASH_MODE;
                break;
            default:
                $mode = CRYPT_HASH_MODE;
        }
        switch ($mode) {
            case CRYPT_HASH_MODE_MHASH:
                switch ($hash) {
                    case 'md5':
                        $this->hash = MHASH_MD5;
                        break;
                    case 'sha256':
                        $this->hash = MHASH_SHA256;
                        break;
                    case 'sha1':
                    default:
                        $this->hash = MHASH_SHA1;
                }
                $this->_computeKey();
                return;
            case CRYPT_HASH_MODE_HASH:
                switch ($hash) {
                    case 'md5':
                        $this->hash = 'md5';
                        return;
                    case 'md2':
                    case 'sha256':
                    case 'sha384':
                    case 'sha512':
                        $this->hash = $hash;
                        return;
                    case 'sha1':
                    default:
                        $this->hash = 'sha1';
                }
                $this->_computeKey();
                return;
        }
        switch ($hash) {
            case 'md2':
                $this->hash = array($this, '_md2');
                break;
            case 'md5':
                $this->hash = array($this, '_md5');
                break;
            case 'sha256':
                $this->hash = array($this, '_sha256');
                break;
            case 'sha384':
            case 'sha512':
                $this->hash = array($this, '_sha512');
                break;
            case 'sha1':
            default:
                $this->hash = array($this, '_sha1');
        }
        $this->ipad = str_repeat(chr(0x36), $this->b);
        $this->opad = str_repeat(chr(0x5C), $this->b);
        $this->_computeKey();
    }

    function hash($text)
    {
        $mode = is_array($this->hash) ? CRYPT_HASH_MODE_INTERNAL : CRYPT_HASH_MODE;
        if (!empty($this->key) || is_string($this->key)) {
            switch ($mode) {
                case CRYPT_HASH_MODE_MHASH:
                    $output = mhash($this->hash, $text, $this->computedKey);
                    break;
                case CRYPT_HASH_MODE_HASH:
                    $output = hash_hmac($this->hash, $text, $this->computedKey, true);
                    break;
                case CRYPT_HASH_MODE_INTERNAL:
                    $key    = str_pad($this->computedKey, $this->b, chr(0));
                    $temp   = $this->ipad ^ $key;
                    $temp  .= $text;
                    $temp   = call_user_func($this->hash, $temp);
                    $output = $this->opad ^ $key;
                    $output.= $temp;
                    $output = call_user_func($this->hash, $output);
            }
        } else {
            switch ($mode) {
                case CRYPT_HASH_MODE_MHASH:
                    $output = mhash($this->hash, $text);
                    break;
                case CRYPT_HASH_MODE_HASH:
                    $output = hash($this->hash, $text, true);
                    break;
                case CRYPT_HASH_MODE_INTERNAL:
                    $output = call_user_func($this->hash, $text);
            }
        }
        return substr($output, 0, $this->l);
    }

    function getLength()
    {
        return $this->l;
    }

    function _md5($m)
    {
        return pack('H*', md5($m));
    }

    function _sha1($m)
    {
        return pack('H*', sha1($m));
    }

    function _md2($m)
    {
        static $s = array(
             41,  46,  67, 201, 162, 216, 124,   1,  61,  54,  84, 161, 236, 240, 6,
             19,  98, 167,   5, 243, 192, 199, 115, 140, 152, 147,  43, 217, 188,
             76, 130, 202,  30, 155,  87,  60, 253, 212, 224,  22, 103,  66, 111, 24,
            138,  23, 229,  18, 190,  78, 196, 214, 218, 158, 222,  73, 160, 251,
            245, 142, 187,  47, 238, 122, 169, 104, 121, 145,  21, 178,   7,  63,
            148, 194,  16, 137,  11,  34,  95,  33, 128, 127,  93, 154,  90, 144, 50,
             39,  53,  62, 204, 231, 191, 247, 151,   3, 255,  25,  48, 179,  72, 165,
            181, 209, 215,  94, 146,  42, 172,  86, 170, 198,  79, 184,  56, 210,
            150, 164, 125, 182, 118, 252, 107, 226, 156, 116,   4, 241,  69, 157,
            112,  89, 100, 113, 135,  32, 134,  91, 207, 101, 230,  45, 168,   2, 27,
             96,  37, 173, 174, 176, 185, 246,  28,  70,  97, 105,  52,  64, 126, 15,
             85,  71, 163,  35, 221,  81, 175,  58, 195,  92, 249, 206, 186, 197,
            234,  38,  44,  83,  13, 110, 133,  40, 132,   9, 211, 223, 205, 244, 65,
            129,  77,  82, 106, 220,  55, 200, 108, 193, 171, 250,  36, 225, 123,
              8,  12, 189, 177,  74, 120, 136, 149, 139, 227,  99, 232, 109, 233,
            203, 213, 254,  59,   0,  29,  57, 242, 239, 183,  14, 102,  88, 208, 228,
            166, 119, 114, 248, 235, 117,  75,  10,  49,  68,  80, 180, 143, 237,
             31,  26, 219, 153, 141,  51, 159,  17, 131, 20
        );
        $pad = 16 - (strlen($m) & 0xF);
        $m.= str_repeat(chr($pad), $pad);
        $length = strlen($m);
        $c = str_repeat(chr(0), 16);
        $l = chr(0);
        for ($i = 0; $i < $length; $i+= 16) {
            for ($j = 0; $j < 16; $j++) {
                $c[$j] = chr($s[ord($m[$i + $j] ^ $l)] ^ ord($c[$j]));
                $l = $c[$j];
            }
        }
        $m.= $c;
        $length+= 16;
        $x = str_repeat(chr(0), 48);
        for ($i = 0; $i < $length; $i+= 16) {
            for ($j = 0; $j < 16; $j++) {
                $x[$j + 16] = $m[$i + $j];
                $x[$j + 32] = $x[$j + 16] ^ $x[$j];
            }
            $t = chr(0);
            for ($j = 0; $j < 18; $j++) {
                for ($k = 0; $k < 48; $k++) {
                    $x[$k] = $t = $x[$k] ^ chr($s[ord($t)]);
                }
                $t = chr(ord($t) + $j);
            }
        }
        return substr($x, 0, 16);
    }

    function _sha256($m)
    {
        if (extension_loaded('suhosin')) {
            return pack('H*', sha256($m));
        }
        $hash = array(
            0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a, 0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19
        );
        static $k = array(
            0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
            0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
            0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
            0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
            0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
            0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
            0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
            0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2
        );
        $length = strlen($m);
        $m.= str_repeat(chr(0), 64 - (($length + 8) & 0x3F));
        $m[$length] = chr(0x80);
        $m.= pack('N2', 0, $length << 3);
        $chunks = str_split($m, 64);
        foreach ($chunks as $chunk) {
            $w = array();
            for ($i = 0; $i < 16; $i++) {
                extract(unpack('Ntemp', $this->_string_shift($chunk, 4)));
                $w[] = $temp;
            }
            for ($i = 16; $i < 64; $i++) {
                $s0 = $this->_rightRotate($w[$i - 15],  7) ^
                      $this->_rightRotate($w[$i - 15], 18) ^
                      $this->_rightShift( $w[$i - 15],  3);
                $s1 = $this->_rightRotate($w[$i - 2], 17) ^
                      $this->_rightRotate($w[$i - 2], 19) ^
                      $this->_rightShift( $w[$i - 2], 10);
                $w[$i] = $this->_add($w[$i - 16], $s0, $w[$i - 7], $s1);
            }
            list($a, $b, $c, $d, $e, $f, $g, $h) = $hash;
            for ($i = 0; $i < 64; $i++) {
                $s0 = $this->_rightRotate($a,  2) ^
                      $this->_rightRotate($a, 13) ^
                      $this->_rightRotate($a, 22);
                $maj = ($a & $b) ^
                       ($a & $c) ^
                       ($b & $c);
                $t2 = $this->_add($s0, $maj);
                $s1 = $this->_rightRotate($e,  6) ^
                      $this->_rightRotate($e, 11) ^
                      $this->_rightRotate($e, 25);
                $ch = ($e & $f) ^
                      ($this->_not($e) & $g);
                $t1 = $this->_add($h, $s1, $ch, $k[$i], $w[$i]);
                $h = $g;
                $g = $f;
                $f = $e;
                $e = $this->_add($d, $t1);
                $d = $c;
                $c = $b;
                $b = $a;
                $a = $this->_add($t1, $t2);
            }
            $hash = array(
                $this->_add($hash[0], $a),
                $this->_add($hash[1], $b),
                $this->_add($hash[2], $c),
                $this->_add($hash[3], $d),
                $this->_add($hash[4], $e),
                $this->_add($hash[5], $f),
                $this->_add($hash[6], $g),
                $this->_add($hash[7], $h)
            );
        }
        return pack('N8', $hash[0], $hash[1], $hash[2], $hash[3], $hash[4], $hash[5], $hash[6], $hash[7]);
    }

    function _sha512($m)
    {
        if (!class_exists('Math_BigInteger')) {
            include_once 'Math/BigInteger.php';
        }
        static $init384, $init512, $k;
        if (!isset($k)) {
            $init384 = array(
                'cbbb9d5dc1059ed8', '629a292a367cd507', '9159015a3070dd17', '152fecd8f70e5939',
                '67332667ffc00b31', '8eb44a8768581511', 'db0c2e0d64f98fa7', '47b5481dbefa4fa4'
            );
            $init512 = array(
                '6a09e667f3bcc908', 'bb67ae8584caa73b', '3c6ef372fe94f82b', 'a54ff53a5f1d36f1',
                '510e527fade682d1', '9b05688c2b3e6c1f', '1f83d9abfb41bd6b', '5be0cd19137e2179'
            );
            for ($i = 0; $i < 8; $i++) {
                $init384[$i] = new Math_BigInteger($init384[$i], 16);
                $init384[$i]->setPrecision(64);
                $init512[$i] = new Math_BigInteger($init512[$i], 16);
                $init512[$i]->setPrecision(64);
            }
            $k = array(
                '428a2f98d728ae22', '7137449123ef65cd', 'b5c0fbcfec4d3b2f', 'e9b5dba58189dbbc',
                '3956c25bf348b538', '59f111f1b605d019', '923f82a4af194f9b', 'ab1c5ed5da6d8118',
                'd807aa98a3030242', '12835b0145706fbe', '243185be4ee4b28c', '550c7dc3d5ffb4e2',
                '72be5d74f27b896f', '80deb1fe3b1696b1', '9bdc06a725c71235', 'c19bf174cf692694',
                'e49b69c19ef14ad2', 'efbe4786384f25e3', '0fc19dc68b8cd5b5', '240ca1cc77ac9c65',
                '2de92c6f592b0275', '4a7484aa6ea6e483', '5cb0a9dcbd41fbd4', '76f988da831153b5',
                '983e5152ee66dfab', 'a831c66d2db43210', 'b00327c898fb213f', 'bf597fc7beef0ee4',
                'c6e00bf33da88fc2', 'd5a79147930aa725', '06ca6351e003826f', '142929670a0e6e70',
                '27b70a8546d22ffc', '2e1b21385c26c926', '4d2c6dfc5ac42aed', '53380d139d95b3df',
                '650a73548baf63de', '766a0abb3c77b2a8', '81c2c92e47edaee6', '92722c851482353b',
                'a2bfe8a14cf10364', 'a81a664bbc423001', 'c24b8b70d0f89791', 'c76c51a30654be30',
                'd192e819d6ef5218', 'd69906245565a910', 'f40e35855771202a', '106aa07032bbd1b8',
                '19a4c116b8d2d0c8', '1e376c085141ab53', '2748774cdf8eeb99', '34b0bcb5e19b48a8',
                '391c0cb3c5c95a63', '4ed8aa4ae3418acb', '5b9cca4f7763e373', '682e6ff3d6b2b8a3',
                '748f82ee5defb2fc', '78a5636f43172f60', '84c87814a1f0ab72', '8cc702081a6439ec',
                '90befffa23631e28', 'a4506cebde82bde9', 'bef9a3f7b2c67915', 'c67178f2e372532b',
                'ca273eceea26619c', 'd186b8c721c0c207', 'eada7dd6cde0eb1e', 'f57d4f7fee6ed178',
                '06f067aa72176fba', '0a637dc5a2c898a6', '113f9804bef90dae', '1b710b35131c471b',
                '28db77f523047d84', '32caab7b40c72493', '3c9ebe0a15c9bebc', '431d67c49c100d4c',
                '4cc5d4becb3e42b6', '597f299cfc657e2a', '5fcb6fab3ad6faec', '6c44198c4a475817'
            );
            for ($i = 0; $i < 80; $i++) {
                $k[$i] = new Math_BigInteger($k[$i], 16);
            }
        }
        $hash = $this->l == 48 ? $init384 : $init512;
        $length = strlen($m);
        $m.= str_repeat(chr(0), 128 - (($length + 16) & 0x7F));
        $m[$length] = chr(0x80);
        $m.= pack('N4', 0, 0, 0, $length << 3);
        $chunks = str_split($m, 128);
        foreach ($chunks as $chunk) {
            $w = array();
            for ($i = 0; $i < 16; $i++) {
                $temp = new Math_BigInteger($this->_string_shift($chunk, 8), 256);
                $temp->setPrecision(64);
                $w[] = $temp;
            }
            for ($i = 16; $i < 80; $i++) {
                $temp = array(
                          $w[$i - 15]->bitwise_rightRotate(1),
                          $w[$i - 15]->bitwise_rightRotate(8),
                          $w[$i - 15]->bitwise_rightShift(7)
                );
                $s0 = $temp[0]->bitwise_xor($temp[1]);
                $s0 = $s0->bitwise_xor($temp[2]);
                $temp = array(
                          $w[$i - 2]->bitwise_rightRotate(19),
                          $w[$i - 2]->bitwise_rightRotate(61),
                          $w[$i - 2]->bitwise_rightShift(6)
                );
                $s1 = $temp[0]->bitwise_xor($temp[1]);
                $s1 = $s1->bitwise_xor($temp[2]);
                $w[$i] = $w[$i - 16]->copy();
                $w[$i] = $w[$i]->add($s0);
                $w[$i] = $w[$i]->add($w[$i - 7]);
                $w[$i] = $w[$i]->add($s1);
            }
            $a = $hash[0]->copy();
            $b = $hash[1]->copy();
            $c = $hash[2]->copy();
            $d = $hash[3]->copy();
            $e = $hash[4]->copy();
            $f = $hash[5]->copy();
            $g = $hash[6]->copy();
            $h = $hash[7]->copy();
            for ($i = 0; $i < 80; $i++) {
                $temp = array(
                    $a->bitwise_rightRotate(28),
                    $a->bitwise_rightRotate(34),
                    $a->bitwise_rightRotate(39)
                );
                $s0 = $temp[0]->bitwise_xor($temp[1]);
                $s0 = $s0->bitwise_xor($temp[2]);
                $temp = array(
                    $a->bitwise_and($b),
                    $a->bitwise_and($c),
                    $b->bitwise_and($c)
                );
                $maj = $temp[0]->bitwise_xor($temp[1]);
                $maj = $maj->bitwise_xor($temp[2]);
                $t2 = $s0->add($maj);
                $temp = array(
                    $e->bitwise_rightRotate(14),
                    $e->bitwise_rightRotate(18),
                    $e->bitwise_rightRotate(41)
                );
                $s1 = $temp[0]->bitwise_xor($temp[1]);
                $s1 = $s1->bitwise_xor($temp[2]);
                $temp = array(
                    $e->bitwise_and($f),
                    $g->bitwise_and($e->bitwise_not())
                );
                $ch = $temp[0]->bitwise_xor($temp[1]);
                $t1 = $h->add($s1);
                $t1 = $t1->add($ch);
                $t1 = $t1->add($k[$i]);
                $t1 = $t1->add($w[$i]);
                $h = $g->copy();
                $g = $f->copy();
                $f = $e->copy();
                $e = $d->add($t1);
                $d = $c->copy();
                $c = $b->copy();
                $b = $a->copy();
                $a = $t1->add($t2);
            }
            $hash = array(
                $hash[0]->add($a),
                $hash[1]->add($b),
                $hash[2]->add($c),
                $hash[3]->add($d),
                $hash[4]->add($e),
                $hash[5]->add($f),
                $hash[6]->add($g),
                $hash[7]->add($h)
            );
        }
        $temp = $hash[0]->toBytes() . $hash[1]->toBytes() . $hash[2]->toBytes() . $hash[3]->toBytes() .
                $hash[4]->toBytes() . $hash[5]->toBytes();
        if ($this->l != 48) {
            $temp.= $hash[6]->toBytes() . $hash[7]->toBytes();
        }
        return $temp;
    }

    function _rightRotate($int, $amt)
    {
        $invamt = 32 - $amt;
        $mask = (1 << $invamt) - 1;
        return (($int << $invamt) & 0xFFFFFFFF) | (($int >> $amt) & $mask);
    }

    function _rightShift($int, $amt)
    {
        $mask = (1 << (32 - $amt)) - 1;
        return ($int >> $amt) & $mask;
    }

    function _not($int)
    {
        return ~$int & 0xFFFFFFFF;
    }

    function _add()
    {
        static $mod;
        if (!isset($mod)) {
            $mod = pow(2, 32);
        }
        $result = 0;
        $arguments = func_get_args();
        foreach ($arguments as $argument) {
            $result+= $argument < 0 ? ($argument & 0x7FFFFFFF) + 0x80000000 : $argument;
        }
        switch (true) {
            case is_int($result):
            case version_compare(PHP_VERSION, '5.3.0') >= 0 && (php_uname('m') & "\xDF\xDF\xDF") != 'ARM':
            case (PHP_OS & "\xDF\xDF\xDF") === 'WIN':
                return fmod($result, $mod);
        }
        return (fmod($result, 0x80000000) & 0x7FFFFFFF) |
            ((fmod(floor($result / 0x80000000), 2) & 1) << 31);
    }

    function _string_shift(&$string, $index = 1)
    {
        $substr = substr($string, 0, $index);
        $string = substr($string, $index);
        return $substr;
    }
}

define('CRYPT_RSA_ENCRYPTION_OAEP',  1);
define('CRYPT_RSA_ENCRYPTION_PKCS1', 2);
define('CRYPT_RSA_ENCRYPTION_NONE', 3);
define('CRYPT_RSA_SIGNATURE_PSS',  1);
define('CRYPT_RSA_SIGNATURE_PKCS1', 2);
define('CRYPT_RSA_ASN1_INTEGER',     2);
define('CRYPT_RSA_ASN1_BITSTRING',   3);
define('CRYPT_RSA_ASN1_OCTETSTRING', 4);
define('CRYPT_RSA_ASN1_OBJECT',      6);
define('CRYPT_RSA_ASN1_SEQUENCE',   48);
define('CRYPT_RSA_MODE_INTERNAL', 1);
define('CRYPT_RSA_MODE_OPENSSL', 2);
define('CRYPT_RSA_OPENSSL_CONFIG', dirname(__FILE__) . '/../openssl.cnf');
define('CRYPT_RSA_PRIVATE_FORMAT_PKCS1', 0);
define('CRYPT_RSA_PRIVATE_FORMAT_PUTTY', 1);
define('CRYPT_RSA_PRIVATE_FORMAT_XML', 2);
define('CRYPT_RSA_PRIVATE_FORMAT_PKCS8', 8);
define('CRYPT_RSA_PUBLIC_FORMAT_RAW', 3);
define('CRYPT_RSA_PUBLIC_FORMAT_PKCS1', 4);
define('CRYPT_RSA_PUBLIC_FORMAT_PKCS1_RAW', 4);
define('CRYPT_RSA_PUBLIC_FORMAT_XML', 5);
define('CRYPT_RSA_PUBLIC_FORMAT_OPENSSH', 6);
define('CRYPT_RSA_PUBLIC_FORMAT_PKCS8', 7);

class Crypt_RSA
{
    var $zero;
    var $one;
    var $privateKeyFormat = CRYPT_RSA_PRIVATE_FORMAT_PKCS1;
    var $publicKeyFormat = CRYPT_RSA_PUBLIC_FORMAT_PKCS8;
    var $modulus;
    var $k;
    var $exponent;
    var $primes;
    var $exponents;
    var $coefficients;
    var $hashName;
    var $hash;
    var $hLen;
    var $sLen;
    var $mgfHash;
    var $mgfHLen;
    var $encryptionMode = CRYPT_RSA_ENCRYPTION_OAEP;
    var $signatureMode = CRYPT_RSA_SIGNATURE_PSS;
    var $publicExponent = false;
    var $password = false;
    var $components = array();
    var $current;
    var $configFile;
    var $comment = 'phpseclib-generated-key';

    function __construct()
    {
        if (!class_exists('Math_BigInteger')) {
            include_once 'Math/BigInteger.php';
        }
        $this->configFile = CRYPT_RSA_OPENSSL_CONFIG;
        if (!defined('CRYPT_RSA_MODE')) {
            switch (true) {
                case defined('MATH_BIGINTEGER_OPENSSL_DISABLE'):
                    define('CRYPT_RSA_MODE', CRYPT_RSA_MODE_INTERNAL);
                    break;
                case !function_exists('openssl_pkey_get_details'):
                    define('CRYPT_RSA_MODE', CRYPT_RSA_MODE_INTERNAL);
                    break;
                case extension_loaded('openssl') && version_compare(PHP_VERSION, '4.2.0', '>=') && file_exists($this->configFile):
                    ob_start();
                    @phpinfo();
                    $content = ob_get_contents();
                    ob_end_clean();
                    preg_match_all('#OpenSSL (Header|Library) Version(.*)#im', $content, $matches);
                    $versions = array();
                    if (!empty($matches[1])) {
                        for ($i = 0; $i < count($matches[1]); $i++) {
                            $fullVersion = trim(str_replace('=>', '', strip_tags($matches[2][$i])));
                            if (!preg_match('/(\d+\.\d+\.\d+)/i', $fullVersion, $m)) {
                                $versions[$matches[1][$i]] = $fullVersion;
                            } else {
                                $versions[$matches[1][$i]] = $m[0];
                            }
                        }
                    }
                    switch (true) {
                        case !isset($versions['Header']):
                        case !isset($versions['Library']):
                        case $versions['Header'] == $versions['Library']:
                        case version_compare($versions['Header'], '1.0.0') >= 0 && version_compare($versions['Library'], '1.0.0') >= 0:
                            define('CRYPT_RSA_MODE', CRYPT_RSA_MODE_OPENSSL);
                            break;
                        default:
                            define('CRYPT_RSA_MODE', CRYPT_RSA_MODE_INTERNAL);
                            define('MATH_BIGINTEGER_OPENSSL_DISABLE', true);
                    }
                    break;
                default:
                    define('CRYPT_RSA_MODE', CRYPT_RSA_MODE_INTERNAL);
            }
        }
        $this->zero = new Math_BigInteger();
        $this->one = new Math_BigInteger(1);
        $this->hash = new Crypt_Hash('sha1');
        $this->hLen = $this->hash->getLength();
        $this->hashName = 'sha1';
        $this->mgfHash = new Crypt_Hash('sha1');
        $this->mgfHLen = $this->mgfHash->getLength();
    }
	
	function crypt_random_string($length)
    {
        if (!$length) {
            return '';
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (extension_loaded('mcrypt') && version_compare(PHP_VERSION, '5.3.0', '>=')) {
                return @mcrypt_create_iv($length);
            }
            if (extension_loaded('openssl') && version_compare(PHP_VERSION, '5.3.4', '>=')) {
                return openssl_random_pseudo_bytes($length);
            }
        } else {
            if (extension_loaded('openssl') && version_compare(PHP_VERSION, '5.3.0', '>=')) {
                return openssl_random_pseudo_bytes($length);
            }
            static $fp = true;
            if ($fp === true) {
                $fp = @fopen('/dev/urandom', 'rb');
            }
            if ($fp !== true && $fp !== false) {
                return fread($fp, $length);
            }
            if (extension_loaded('mcrypt')) {
                return @mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            }
        }
		return '';
    }

    function Crypt_RSA()
    {
        $this->__construct();
    }

    function createKey($bits = 1024, $timeout = false, $partial = array())
    {
        if (!defined('CRYPT_RSA_EXPONENT')) {
            define('CRYPT_RSA_EXPONENT', '65537');
        }
        if (!defined('CRYPT_RSA_SMALLEST_PRIME')) {
            define('CRYPT_RSA_SMALLEST_PRIME', 4096);
        }
        if (CRYPT_RSA_MODE == CRYPT_RSA_MODE_OPENSSL && $bits >= 384 && CRYPT_RSA_EXPONENT == 65537) {
            $config = array();
            if (isset($this->configFile)) {
                $config['config'] = $this->configFile;
            }
            $rsa = openssl_pkey_new(array('private_key_bits' => $bits) + $config);
            openssl_pkey_export($rsa, $privatekey, null, $config);
            $publickey = openssl_pkey_get_details($rsa);
            $publickey = $publickey['key'];
            $privatekey = call_user_func_array(array($this, '_convertPrivateKey'), array_values($this->_parseKey($privatekey, CRYPT_RSA_PRIVATE_FORMAT_PKCS1)));
            $publickey = call_user_func_array(array($this, '_convertPublicKey'), array_values($this->_parseKey($publickey, CRYPT_RSA_PUBLIC_FORMAT_PKCS1)));
            while (openssl_error_string() !== false) {
            }
            return array(
                'privatekey' => $privatekey,
                'publickey' => $publickey,
                'partialkey' => false
            );
        }
        static $e;
        if (!isset($e)) {
            $e = new Math_BigInteger(CRYPT_RSA_EXPONENT);
        }
        extract($this->_generateMinMax($bits));
        $absoluteMin = $min;
        $temp = $bits >> 1;
        if ($temp > CRYPT_RSA_SMALLEST_PRIME) {
            $num_primes = floor($bits / CRYPT_RSA_SMALLEST_PRIME);
            $temp = CRYPT_RSA_SMALLEST_PRIME;
        } else {
            $num_primes = 2;
        }
        extract($this->_generateMinMax($temp + $bits % $temp));
        $finalMax = $max;
        extract($this->_generateMinMax($temp));
        $generator = new Math_BigInteger();
        $n = $this->one->copy();
        if (!empty($partial)) {
            extract(unserialize($partial));
        } else {
            $exponents = $coefficients = $primes = array();
            $lcm = array(
                'top' => $this->one->copy(),
                'bottom' => false
            );
        }
        $start = time();
        $i0 = count($primes) + 1;
        do {
            for ($i = $i0; $i <= $num_primes; $i++) {
                if ($timeout !== false) {
                    $timeout-= time() - $start;
                    $start = time();
                    if ($timeout <= 0) {
                        return array(
                            'privatekey' => '',
                            'publickey'  => '',
                            'partialkey' => serialize(array(
                                'primes' => $primes,
                                'coefficients' => $coefficients,
                                'lcm' => $lcm,
                                'exponents' => $exponents
                            ))
                        );
                    }
                }
                if ($i == $num_primes) {
                    list($min, $temp) = $absoluteMin->divide($n);
                    if (!$temp->equals($this->zero)) {
                        $min = $min->add($this->one);
                    }
                    $primes[$i] = $generator->randomPrime($min, $finalMax, $timeout);
                } else {
                    $primes[$i] = $generator->randomPrime($min, $max, $timeout);
                }
                if ($primes[$i] === false) {
                    if (count($primes) > 1) {
                        $partialkey = '';
                    } else {
                        array_pop($primes);
                        $partialkey = serialize(array(
                            'primes' => $primes,
                            'coefficients' => $coefficients,
                            'lcm' => $lcm,
                            'exponents' => $exponents
                        ));
                    }
                    return array(
                        'privatekey' => '',
                        'publickey'  => '',
                        'partialkey' => $partialkey
                    );
                }
                if ($i > 2) {
                    $coefficients[$i] = $n->modInverse($primes[$i]);
                }
                $n = $n->multiply($primes[$i]);
                $temp = $primes[$i]->subtract($this->one);
                $lcm['top'] = $lcm['top']->multiply($temp);
                $lcm['bottom'] = $lcm['bottom'] === false ? $temp : $lcm['bottom']->gcd($temp);
                $exponents[$i] = $e->modInverse($temp);
            }
            list($temp) = $lcm['top']->divide($lcm['bottom']);
            $gcd = $temp->gcd($e);
            $i0 = 1;
        } while (!$gcd->equals($this->one));
        $d = $e->modInverse($temp);
        $coefficients[2] = $primes[2]->modInverse($primes[1]);
        return array(
            'privatekey' => $this->_convertPrivateKey($n, $e, $d, $primes, $exponents, $coefficients),
            'publickey'  => $this->_convertPublicKey($n, $e),
            'partialkey' => false
        );
    }

    function _convertPrivateKey($n, $e, $d, $primes, $exponents, $coefficients)
    {
        $signed = $this->privateKeyFormat != CRYPT_RSA_PRIVATE_FORMAT_XML;
        $num_primes = count($primes);
        $raw = array(
            'version' => $num_primes == 2 ? chr(0) : chr(1),
            'modulus' => $n->toBytes($signed),
            'publicExponent' => $e->toBytes($signed),
            'privateExponent' => $d->toBytes($signed),
            'prime1' => $primes[1]->toBytes($signed),
            'prime2' => $primes[2]->toBytes($signed),
            'exponent1' => $exponents[1]->toBytes($signed),
            'exponent2' => $exponents[2]->toBytes($signed),
            'coefficient' => $coefficients[2]->toBytes($signed)
        );
        switch ($this->privateKeyFormat) {
            case CRYPT_RSA_PRIVATE_FORMAT_XML:
                if ($num_primes != 2) {
                    return false;
                }
                return "<RSAKeyValue>\r\n" .
                       '  <Modulus>' . base64_encode($raw['modulus']) . "</Modulus>\r\n" .
                       '  <Exponent>' . base64_encode($raw['publicExponent']) . "</Exponent>\r\n" .
                       '  <P>' . base64_encode($raw['prime1']) . "</P>\r\n" .
                       '  <Q>' . base64_encode($raw['prime2']) . "</Q>\r\n" .
                       '  <DP>' . base64_encode($raw['exponent1']) . "</DP>\r\n" .
                       '  <DQ>' . base64_encode($raw['exponent2']) . "</DQ>\r\n" .
                       '  <InverseQ>' . base64_encode($raw['coefficient']) . "</InverseQ>\r\n" .
                       '  <D>' . base64_encode($raw['privateExponent']) . "</D>\r\n" .
                       '</RSAKeyValue>';
                break;
            case CRYPT_RSA_PRIVATE_FORMAT_PUTTY:
                if ($num_primes != 2) {
                    return false;
                }
                $key = "PuTTY-User-Key-File-2: ssh-rsa\r\nEncryption: ";
                $encryption = (!empty($this->password) || is_string($this->password)) ? 'aes256-cbc' : 'none';
                $key.= $encryption;
                $key.= "\r\nComment: " . $this->comment . "\r\n";
                $public = pack(
                    'Na*Na*Na*',
                    strlen('ssh-rsa'),
                    'ssh-rsa',
                    strlen($raw['publicExponent']),
                    $raw['publicExponent'],
                    strlen($raw['modulus']),
                    $raw['modulus']
                );
                $source = pack(
                    'Na*Na*Na*Na*',
                    strlen('ssh-rsa'),
                    'ssh-rsa',
                    strlen($encryption),
                    $encryption,
                    strlen($this->comment),
                    $this->comment,
                    strlen($public),
                    $public
                );
                $public = base64_encode($public);
                $key.= "Public-Lines: " . ((strlen($public) + 63) >> 6) . "\r\n";
                $key.= chunk_split($public, 64);
                $private = pack(
                    'Na*Na*Na*Na*',
                    strlen($raw['privateExponent']),
                    $raw['privateExponent'],
                    strlen($raw['prime1']),
                    $raw['prime1'],
                    strlen($raw['prime2']),
                    $raw['prime2'],
                    strlen($raw['coefficient']),
                    $raw['coefficient']
                );
                if (empty($this->password) && !is_string($this->password)) {
                    $source.= pack('Na*', strlen($private), $private);
                    $hashkey = 'putty-private-key-file-mac-key';
                } else {
                    $private.= $this->crypt_random_string(16 - (strlen($private) & 15));
                    $source.= pack('Na*', strlen($private), $private);
                    if (!class_exists('Crypt_AES')) {
                        include_once 'Crypt/AES.php';
                    }
                    $sequence = 0;
                    $symkey = '';
                    while (strlen($symkey) < 32) {
                        $temp = pack('Na*', $sequence++, $this->password);
                        $symkey.= pack('H*', sha1($temp));
                    }
                    $symkey = substr($symkey, 0, 32);
                    $crypto = new Crypt_AES();
                    $crypto->setKey($symkey);
                    $crypto->disablePadding();
                    $private = $crypto->encrypt($private);
                    $hashkey = 'putty-private-key-file-mac-key' . $this->password;
                }
                $private = base64_encode($private);
                $key.= 'Private-Lines: ' . ((strlen($private) + 63) >> 6) . "\r\n";
                $key.= chunk_split($private, 64);
                if (!class_exists('Crypt_Hash')) {
                    include_once 'Crypt/Hash.php';
                }
                $hash = new Crypt_Hash('sha1');
                $hash->setKey(pack('H*', sha1($hashkey)));
                $key.= 'Private-MAC: ' . bin2hex($hash->hash($source)) . "\r\n";
                return $key;
            default:
                $components = array();
                foreach ($raw as $name => $value) {
                    $components[$name] = pack('Ca*a*', CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($value)), $value);
                }
                $RSAPrivateKey = implode('', $components);
                if ($num_primes > 2) {
                    $OtherPrimeInfos = '';
                    for ($i = 3; $i <= $num_primes; $i++) {
                        $OtherPrimeInfo = pack('Ca*a*', CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($primes[$i]->toBytes(true))), $primes[$i]->toBytes(true));
                        $OtherPrimeInfo.= pack('Ca*a*', CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($exponents[$i]->toBytes(true))), $exponents[$i]->toBytes(true));
                        $OtherPrimeInfo.= pack('Ca*a*', CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($coefficients[$i]->toBytes(true))), $coefficients[$i]->toBytes(true));
                        $OtherPrimeInfos.= pack('Ca*a*', CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($OtherPrimeInfo)), $OtherPrimeInfo);
                    }
                    $RSAPrivateKey.= pack('Ca*a*', CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($OtherPrimeInfos)), $OtherPrimeInfos);
                }
                $RSAPrivateKey = pack('Ca*a*', CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($RSAPrivateKey)), $RSAPrivateKey);
                if ($this->privateKeyFormat == CRYPT_RSA_PRIVATE_FORMAT_PKCS8) {
                    $rsaOID = pack('H*', '300d06092a864886f70d0101010500');
                    $RSAPrivateKey = pack(
                        'Ca*a*Ca*a*',
                        CRYPT_RSA_ASN1_INTEGER,
                        "\01\00",
                        $rsaOID,
                        4,
                        $this->_encodeLength(strlen($RSAPrivateKey)),
                        $RSAPrivateKey
                    );
                    $RSAPrivateKey = pack('Ca*a*', CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($RSAPrivateKey)), $RSAPrivateKey);
                    if (!empty($this->password) || is_string($this->password)) {
                        $salt = $this->crypt_random_string(8);
                        $iterationCount = 2048;
                        if (!class_exists('Crypt_DES')) {
                            include_once 'Crypt/DES.php';
                        }
                        $crypto = new Crypt_DES();
                        $crypto->setPassword($this->password, 'pbkdf1', 'md5', $salt, $iterationCount);
                        $RSAPrivateKey = $crypto->encrypt($RSAPrivateKey);
                        $parameters = pack(
                            'Ca*a*Ca*N',
                            CRYPT_RSA_ASN1_OCTETSTRING,
                            $this->_encodeLength(strlen($salt)),
                            $salt,
                            CRYPT_RSA_ASN1_INTEGER,
                            $this->_encodeLength(4),
                            $iterationCount
                        );
                        $pbeWithMD5AndDES_CBC = "\x2a\x86\x48\x86\xf7\x0d\x01\x05\x03";
                        $encryptionAlgorithm = pack(
                            'Ca*a*Ca*a*',
                            CRYPT_RSA_ASN1_OBJECT,
                            $this->_encodeLength(strlen($pbeWithMD5AndDES_CBC)),
                            $pbeWithMD5AndDES_CBC,
                            CRYPT_RSA_ASN1_SEQUENCE,
                            $this->_encodeLength(strlen($parameters)),
                            $parameters
                        );
                        $RSAPrivateKey = pack(
                            'Ca*a*Ca*a*',
                            CRYPT_RSA_ASN1_SEQUENCE,
                            $this->_encodeLength(strlen($encryptionAlgorithm)),
                            $encryptionAlgorithm,
                            CRYPT_RSA_ASN1_OCTETSTRING,
                            $this->_encodeLength(strlen($RSAPrivateKey)),
                            $RSAPrivateKey
                        );
                        $RSAPrivateKey = pack('Ca*a*', CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($RSAPrivateKey)), $RSAPrivateKey);
                        $RSAPrivateKey = "-----BEGIN ENCRYPTED PRIVATE KEY-----\r\n" .
                                         chunk_split(base64_encode($RSAPrivateKey), 64) .
                                         '-----END ENCRYPTED PRIVATE KEY-----';
                    } else {
                        $RSAPrivateKey = "-----BEGIN PRIVATE KEY-----\r\n" .
                                         chunk_split(base64_encode($RSAPrivateKey), 64) .
                                         '-----END PRIVATE KEY-----';
                    }
                    return $RSAPrivateKey;
                }
                if (!empty($this->password) || is_string($this->password)) {
                    $iv = $this->crypt_random_string(8);
                    $symkey = pack('H*', md5($this->password . $iv));
                    $symkey.= substr(pack('H*', md5($symkey . $this->password . $iv)), 0, 8);
                    if (!class_exists('Crypt_TripleDES')) {
                        include_once 'Crypt/TripleDES.php';
                    }
                    $des = new Crypt_TripleDES();
                    $des->setKey($symkey);
                    $des->setIV($iv);
                    $iv = strtoupper(bin2hex($iv));
                    $RSAPrivateKey = "-----BEGIN RSA PRIVATE KEY-----\r\n" .
                                     "Proc-Type: 4,ENCRYPTED\r\n" .
                                     "DEK-Info: DES-EDE3-CBC,$iv\r\n" .
                                     "\r\n" .
                                     chunk_split(base64_encode($des->encrypt($RSAPrivateKey)), 64) .
                                     '-----END RSA PRIVATE KEY-----';
                } else {
                    $RSAPrivateKey = "-----BEGIN RSA PRIVATE KEY-----\r\n" .
                                     chunk_split(base64_encode($RSAPrivateKey), 64) .
                                     '-----END RSA PRIVATE KEY-----';
                }
                return $RSAPrivateKey;
        }
    }

    function _convertPublicKey($n, $e)
    {
        $signed = $this->publicKeyFormat != CRYPT_RSA_PUBLIC_FORMAT_XML;
        $modulus = $n->toBytes($signed);
        $publicExponent = $e->toBytes($signed);
        switch ($this->publicKeyFormat) {
            case CRYPT_RSA_PUBLIC_FORMAT_RAW:
                return array('e' => $e->copy(), 'n' => $n->copy());
            case CRYPT_RSA_PUBLIC_FORMAT_XML:
                return "<RSAKeyValue>\r\n" .
                       '  <Modulus>' . base64_encode($modulus) . "</Modulus>\r\n" .
                       '  <Exponent>' . base64_encode($publicExponent) . "</Exponent>\r\n" .
                       '</RSAKeyValue>';
                break;
            case CRYPT_RSA_PUBLIC_FORMAT_OPENSSH:
                $RSAPublicKey = pack('Na*Na*Na*', strlen('ssh-rsa'), 'ssh-rsa', strlen($publicExponent), $publicExponent, strlen($modulus), $modulus);
                $RSAPublicKey = 'ssh-rsa ' . base64_encode($RSAPublicKey) . ' ' . $this->comment;
                return $RSAPublicKey;
            default:
                $components = array(
                    'modulus' => pack('Ca*a*', CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($modulus)), $modulus),
                    'publicExponent' => pack('Ca*a*', CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($publicExponent)), $publicExponent)
                );
                $RSAPublicKey = pack(
                    'Ca*a*a*',
                    CRYPT_RSA_ASN1_SEQUENCE,
                    $this->_encodeLength(strlen($components['modulus']) + strlen($components['publicExponent'])),
                    $components['modulus'],
                    $components['publicExponent']
                );
                if ($this->publicKeyFormat == CRYPT_RSA_PUBLIC_FORMAT_PKCS1_RAW) {
                    $RSAPublicKey = "-----BEGIN RSA PUBLIC KEY-----\r\n" .
                                    chunk_split(base64_encode($RSAPublicKey), 64) .
                                    '-----END RSA PUBLIC KEY-----';
                } else {
                    $rsaOID = pack('H*', '300d06092a864886f70d0101010500');
                    $RSAPublicKey = chr(0) . $RSAPublicKey;
                    $RSAPublicKey = chr(3) . $this->_encodeLength(strlen($RSAPublicKey)) . $RSAPublicKey;
                    $RSAPublicKey = pack(
                        'Ca*a*',
                        CRYPT_RSA_ASN1_SEQUENCE,
                        $this->_encodeLength(strlen($rsaOID . $RSAPublicKey)),
                        $rsaOID . $RSAPublicKey
                    );
                    $RSAPublicKey = "-----BEGIN PUBLIC KEY-----\r\n" .
                                     chunk_split(base64_encode($RSAPublicKey), 64) .
                                     '-----END PUBLIC KEY-----';
                }
                return $RSAPublicKey;
        }
    }

    function _parseKey($key, $type)
    {
        if ($type != CRYPT_RSA_PUBLIC_FORMAT_RAW && !is_string($key)) {
            return false;
        }
        switch ($type) {
            case CRYPT_RSA_PUBLIC_FORMAT_RAW:
                if (!is_array($key)) {
                    return false;
                }
                $components = array();
                switch (true) {
                    case isset($key['e']):
                        $components['publicExponent'] = $key['e']->copy();
                        break;
                    case isset($key['exponent']):
                        $components['publicExponent'] = $key['exponent']->copy();
                        break;
                    case isset($key['publicExponent']):
                        $components['publicExponent'] = $key['publicExponent']->copy();
                        break;
                    case isset($key[0]):
                        $components['publicExponent'] = $key[0]->copy();
                }
                switch (true) {
                    case isset($key['n']):
                        $components['modulus'] = $key['n']->copy();
                        break;
                    case isset($key['modulo']):
                        $components['modulus'] = $key['modulo']->copy();
                        break;
                    case isset($key['modulus']):
                        $components['modulus'] = $key['modulus']->copy();
                        break;
                    case isset($key[1]):
                        $components['modulus'] = $key[1]->copy();
                }
                return isset($components['modulus']) && isset($components['publicExponent']) ? $components : false;
            case CRYPT_RSA_PRIVATE_FORMAT_PKCS1:
            case CRYPT_RSA_PRIVATE_FORMAT_PKCS8:
            case CRYPT_RSA_PUBLIC_FORMAT_PKCS1:
                if (preg_match('#DEK-Info: (.+),(.+)#', $key, $matches)) {
                    $iv = pack('H*', trim($matches[2]));
                    $symkey = pack('H*', md5($this->password . substr($iv, 0, 8)));
                    $symkey.= pack('H*', md5($symkey . $this->password . substr($iv, 0, 8)));
                    $key = preg_replace('#^(?:Proc-Type|DEK-Info): .*#m', '', $key);
                    $ciphertext = $this->_extractBER($key);
                    if ($ciphertext === false) {
                        $ciphertext = $key;
                    }
                    switch ($matches[1]) {
                        case 'AES-256-CBC':
                            if (!class_exists('Crypt_AES')) {
                                include_once 'Crypt/AES.php';
                            }
                            $crypto = new Crypt_AES();
                            break;
                        case 'AES-128-CBC':
                            if (!class_exists('Crypt_AES')) {
                                include_once 'Crypt/AES.php';
                            }
                            $symkey = substr($symkey, 0, 16);
                            $crypto = new Crypt_AES();
                            break;
                        case 'DES-EDE3-CFB':
                            if (!class_exists('Crypt_TripleDES')) {
                                include_once 'Crypt/TripleDES.php';
                            }
                            $crypto = new Crypt_TripleDES(CRYPT_DES_MODE_CFB);
                            break;
                        case 'DES-EDE3-CBC':
                            if (!class_exists('Crypt_TripleDES')) {
                                include_once 'Crypt/TripleDES.php';
                            }
                            $symkey = substr($symkey, 0, 24);
                            $crypto = new Crypt_TripleDES();
                            break;
                        case 'DES-CBC':
                            if (!class_exists('Crypt_DES')) {
                                include_once 'Crypt/DES.php';
                            }
                            $crypto = new Crypt_DES();
                            break;
                        default:
                            return false;
                    }
                    $crypto->setKey($symkey);
                    $crypto->setIV($iv);
                    $decoded = $crypto->decrypt($ciphertext);
                } else {
                    $decoded = $this->_extractBER($key);
                }
                if ($decoded !== false) {
                    $key = $decoded;
                }
                $components = array();
                if (ord($this->_string_shift($key)) != CRYPT_RSA_ASN1_SEQUENCE) {
                    return false;
                }
                if ($this->_decodeLength($key) != strlen($key)) {
                    return false;
                }
                $tag = ord($this->_string_shift($key));
                if ($tag == CRYPT_RSA_ASN1_INTEGER && substr($key, 0, 3) == "\x01\x00\x30") {
                    $this->_string_shift($key, 3);
                    $tag = CRYPT_RSA_ASN1_SEQUENCE;
                }
                if ($tag == CRYPT_RSA_ASN1_SEQUENCE) {
                    $temp = $this->_string_shift($key, $this->_decodeLength($key));
                    if (ord($this->_string_shift($temp)) != CRYPT_RSA_ASN1_OBJECT) {
                        return false;
                    }
                    $length = $this->_decodeLength($temp);
                    switch ($this->_string_shift($temp, $length)) {
                        case "\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01":
                            break;
                        case "\x2a\x86\x48\x86\xf7\x0d\x01\x05\x03":
                            if (ord($this->_string_shift($temp)) != CRYPT_RSA_ASN1_SEQUENCE) {
                                return false;
                            }
                            if ($this->_decodeLength($temp) != strlen($temp)) {
                                return false;
                            }
                            $this->_string_shift($temp);
                            $salt = $this->_string_shift($temp, $this->_decodeLength($temp));
                            if (ord($this->_string_shift($temp)) != CRYPT_RSA_ASN1_INTEGER) {
                                return false;
                            }
                            $this->_decodeLength($temp);
                            list(, $iterationCount) = unpack('N', str_pad($temp, 4, chr(0), STR_PAD_LEFT));
                            $this->_string_shift($key);
                            $length = $this->_decodeLength($key);
                            if (strlen($key) != $length) {
                                return false;
                            }
                            if (!class_exists('Crypt_DES')) {
                                include_once 'Crypt/DES.php';
                            }
                            $crypto = new Crypt_DES();
                            $crypto->setPassword($this->password, 'pbkdf1', 'md5', $salt, $iterationCount);
                            $key = $crypto->decrypt($key);
                            if ($key === false) {
                                return false;
                            }
                            return $this->_parseKey($key, CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
                        default:
                            return false;
                    }
                    $tag = ord($this->_string_shift($key));
                    $this->_decodeLength($key);
                    if ($tag == CRYPT_RSA_ASN1_BITSTRING) {
                        $this->_string_shift($key);
                    }
                    if (ord($this->_string_shift($key)) != CRYPT_RSA_ASN1_SEQUENCE) {
                        return false;
                    }
                    if ($this->_decodeLength($key) != strlen($key)) {
                        return false;
                    }
                    $tag = ord($this->_string_shift($key));
                }
                if ($tag != CRYPT_RSA_ASN1_INTEGER) {
                    return false;
                }
                $length = $this->_decodeLength($key);
                $temp = $this->_string_shift($key, $length);
                if (strlen($temp) != 1 || ord($temp) > 2) {
                    $components['modulus'] = new Math_BigInteger($temp, 256);
                    $this->_string_shift($key);
                    $length = $this->_decodeLength($key);
                    $components[$type == CRYPT_RSA_PUBLIC_FORMAT_PKCS1 ? 'publicExponent' : 'privateExponent'] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                    return $components;
                }
                if (ord($this->_string_shift($key)) != CRYPT_RSA_ASN1_INTEGER) {
                    return false;
                }
                $length = $this->_decodeLength($key);
                $components['modulus'] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                $this->_string_shift($key);
                $length = $this->_decodeLength($key);
                $components['publicExponent'] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                $this->_string_shift($key);
                $length = $this->_decodeLength($key);
                $components['privateExponent'] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                $this->_string_shift($key);
                $length = $this->_decodeLength($key);
                $components['primes'] = array(1 => new Math_BigInteger($this->_string_shift($key, $length), 256));
                $this->_string_shift($key);
                $length = $this->_decodeLength($key);
                $components['primes'][] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                $this->_string_shift($key);
                $length = $this->_decodeLength($key);
                $components['exponents'] = array(1 => new Math_BigInteger($this->_string_shift($key, $length), 256));
                $this->_string_shift($key);
                $length = $this->_decodeLength($key);
                $components['exponents'][] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                $this->_string_shift($key);
                $length = $this->_decodeLength($key);
                $components['coefficients'] = array(2 => new Math_BigInteger($this->_string_shift($key, $length), 256));
                if (!empty($key)) {
                    if (ord($this->_string_shift($key)) != CRYPT_RSA_ASN1_SEQUENCE) {
                        return false;
                    }
                    $this->_decodeLength($key);
                    while (!empty($key)) {
                        if (ord($this->_string_shift($key)) != CRYPT_RSA_ASN1_SEQUENCE) {
                            return false;
                        }
                        $this->_decodeLength($key);
                        $key = substr($key, 1);
                        $length = $this->_decodeLength($key);
                        $components['primes'][] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                        $this->_string_shift($key);
                        $length = $this->_decodeLength($key);
                        $components['exponents'][] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                        $this->_string_shift($key);
                        $length = $this->_decodeLength($key);
                        $components['coefficients'][] = new Math_BigInteger($this->_string_shift($key, $length), 256);
                    }
                }
                return $components;
            case CRYPT_RSA_PUBLIC_FORMAT_OPENSSH:
                $parts = explode(' ', $key, 3);
                $key = isset($parts[1]) ? base64_decode($parts[1]) : false;
                if ($key === false) {
                    return false;
                }
                $comment = isset($parts[2]) ? $parts[2] : false;
                $cleanup = substr($key, 0, 11) == "\0\0\0\7ssh-rsa";
                if (strlen($key) <= 4) {
                    return false;
                }
                extract(unpack('Nlength', $this->_string_shift($key, 4)));
                $publicExponent = new Math_BigInteger($this->_string_shift($key, $length), -256);
                if (strlen($key) <= 4) {
                    return false;
                }
                extract(unpack('Nlength', $this->_string_shift($key, 4)));
                $modulus = new Math_BigInteger($this->_string_shift($key, $length), -256);
                if ($cleanup && strlen($key)) {
                    if (strlen($key) <= 4) {
                        return false;
                    }
                    extract(unpack('Nlength', $this->_string_shift($key, 4)));
                    $realModulus = new Math_BigInteger($this->_string_shift($key, $length), -256);
                    return strlen($key) ? false : array(
                        'modulus' => $realModulus,
                        'publicExponent' => $modulus,
                        'comment' => $comment
                    );
                } else {
                    return strlen($key) ? false : array(
                        'modulus' => $modulus,
                        'publicExponent' => $publicExponent,
                        'comment' => $comment
                    );
                }
            case CRYPT_RSA_PRIVATE_FORMAT_XML:
            case CRYPT_RSA_PUBLIC_FORMAT_XML:
                $this->components = array();
                $xml = xml_parser_create('UTF-8');
                xml_set_object($xml, $this);
                xml_set_element_handler($xml, '_start_element_handler', '_stop_element_handler');
                xml_set_character_data_handler($xml, '_data_handler');
                if (!xml_parse($xml, '<xml>' . $key . '</xml>')) {
                    return false;
                }
                return isset($this->components['modulus']) && isset($this->components['publicExponent']) ? $this->components : false;
            case CRYPT_RSA_PRIVATE_FORMAT_PUTTY:
                $components = array();
                $key = preg_split('#\r\n|\r|\n#', $key);
                $type = trim(preg_replace('#PuTTY-User-Key-File-2: (.+)#', '$1', $key[0]));
                if ($type != 'ssh-rsa') {
                    return false;
                }
                $encryption = trim(preg_replace('#Encryption: (.+)#', '$1', $key[1]));
                $comment = trim(preg_replace('#Comment: (.+)#', '$1', $key[2]));
                $publicLength = trim(preg_replace('#Public-Lines: (\d+)#', '$1', $key[3]));
                $public = base64_decode(implode('', array_map('trim', array_slice($key, 4, $publicLength))));
                $public = substr($public, 11);
                extract(unpack('Nlength', $this->_string_shift($public, 4)));
                $components['publicExponent'] = new Math_BigInteger($this->_string_shift($public, $length), -256);
                extract(unpack('Nlength', $this->_string_shift($public, 4)));
                $components['modulus'] = new Math_BigInteger($this->_string_shift($public, $length), -256);
                $privateLength = trim(preg_replace('#Private-Lines: (\d+)#', '$1', $key[$publicLength + 4]));
                $private = base64_decode(implode('', array_map('trim', array_slice($key, $publicLength + 5, $privateLength))));
                switch ($encryption) {
                    case 'aes256-cbc':
                        if (!class_exists('Crypt_AES')) {
                            include_once 'Crypt/AES.php';
                        }
                        $symkey = '';
                        $sequence = 0;
                        while (strlen($symkey) < 32) {
                            $temp = pack('Na*', $sequence++, $this->password);
                            $symkey.= pack('H*', sha1($temp));
                        }
                        $symkey = substr($symkey, 0, 32);
                        $crypto = new Crypt_AES();
                }
                if ($encryption != 'none') {
                    $crypto->setKey($symkey);
                    $crypto->disablePadding();
                    $private = $crypto->decrypt($private);
                    if ($private === false) {
                        return false;
                    }
                }
                extract(unpack('Nlength', $this->_string_shift($private, 4)));
                if (strlen($private) < $length) {
                    return false;
                }
                $components['privateExponent'] = new Math_BigInteger($this->_string_shift($private, $length), -256);
                extract(unpack('Nlength', $this->_string_shift($private, 4)));
                if (strlen($private) < $length) {
                    return false;
                }
                $components['primes'] = array(1 => new Math_BigInteger($this->_string_shift($private, $length), -256));
                extract(unpack('Nlength', $this->_string_shift($private, 4)));
                if (strlen($private) < $length) {
                    return false;
                }
                $components['primes'][] = new Math_BigInteger($this->_string_shift($private, $length), -256);
                $temp = $components['primes'][1]->subtract($this->one);
                $components['exponents'] = array(1 => $components['publicExponent']->modInverse($temp));
                $temp = $components['primes'][2]->subtract($this->one);
                $components['exponents'][] = $components['publicExponent']->modInverse($temp);
                extract(unpack('Nlength', $this->_string_shift($private, 4)));
                if (strlen($private) < $length) {
                    return false;
                }
                $components['coefficients'] = array(2 => new Math_BigInteger($this->_string_shift($private, $length), -256));
                return $components;
        }
    }

    function getSize()
    {
        return !isset($this->modulus) ? 0 : strlen($this->modulus->toBits());
    }

    function _start_element_handler($parser, $name, $attribs)
    {
        switch ($name) {
            case 'MODULUS':
                $this->current = &$this->components['modulus'];
                break;
            case 'EXPONENT':
                $this->current = &$this->components['publicExponent'];
                break;
            case 'P':
                $this->current = &$this->components['primes'][1];
                break;
            case 'Q':
                $this->current = &$this->components['primes'][2];
                break;
            case 'DP':
                $this->current = &$this->components['exponents'][1];
                break;
            case 'DQ':
                $this->current = &$this->components['exponents'][2];
                break;
            case 'INVERSEQ':
                $this->current = &$this->components['coefficients'][2];
                break;
            case 'D':
                $this->current = &$this->components['privateExponent'];
        }
        $this->current = '';
    }

    function _stop_element_handler($parser, $name)
    {
        if (isset($this->current)) {
            $this->current = new Math_BigInteger(base64_decode($this->current), 256);
            unset($this->current);
        }
    }

    function _data_handler($parser, $data)
    {
        if (!isset($this->current) || is_object($this->current)) {
            return;
        }
        $this->current.= trim($data);
    }

    function loadKey($key, $type = false)
    {
        if (is_object($key) && strtolower(get_class($key)) == 'crypt_rsa') {
            $this->privateKeyFormat = $key->privateKeyFormat;
            $this->publicKeyFormat = $key->publicKeyFormat;
            $this->k = $key->k;
            $this->hLen = $key->hLen;
            $this->sLen = $key->sLen;
            $this->mgfHLen = $key->mgfHLen;
            $this->encryptionMode = $key->encryptionMode;
            $this->signatureMode = $key->signatureMode;
            $this->password = $key->password;
            $this->configFile = $key->configFile;
            $this->comment = $key->comment;
            if (is_object($key->hash)) {
                $this->hash = new Crypt_Hash($key->hash->getHash());
            }
            if (is_object($key->mgfHash)) {
                $this->mgfHash = new Crypt_Hash($key->mgfHash->getHash());
            }
            if (is_object($key->modulus)) {
                $this->modulus = $key->modulus->copy();
            }
            if (is_object($key->exponent)) {
                $this->exponent = $key->exponent->copy();
            }
            if (is_object($key->publicExponent)) {
                $this->publicExponent = $key->publicExponent->copy();
            }
            $this->primes = array();
            $this->exponents = array();
            $this->coefficients = array();
            foreach ($this->primes as $prime) {
                $this->primes[] = $prime->copy();
            }
            foreach ($this->exponents as $exponent) {
                $this->exponents[] = $exponent->copy();
            }
            foreach ($this->coefficients as $coefficient) {
                $this->coefficients[] = $coefficient->copy();
            }
            return true;
        }
        if ($type === false) {
            $types = array(
                CRYPT_RSA_PUBLIC_FORMAT_RAW,
                CRYPT_RSA_PRIVATE_FORMAT_PKCS1,
                CRYPT_RSA_PRIVATE_FORMAT_XML,
                CRYPT_RSA_PRIVATE_FORMAT_PUTTY,
                CRYPT_RSA_PUBLIC_FORMAT_OPENSSH
            );
            foreach ($types as $type) {
                $components = $this->_parseKey($key, $type);
                if ($components !== false) {
                    break;
                }
            }
        } else {
            $components = $this->_parseKey($key, $type);
        }
        if ($components === false) {
            $this->comment = null;
            $this->modulus = null;
            $this->k = null;
            $this->exponent = null;
            $this->primes = null;
            $this->exponents = null;
            $this->coefficients = null;
            $this->publicExponent = null;
            return false;
        }
        if (isset($components['comment']) && $components['comment'] !== false) {
            $this->comment = $components['comment'];
        }
        $this->modulus = $components['modulus'];
        $this->k = strlen($this->modulus->toBytes());
        $this->exponent = isset($components['privateExponent']) ? $components['privateExponent'] : $components['publicExponent'];
        if (isset($components['primes'])) {
            $this->primes = $components['primes'];
            $this->exponents = $components['exponents'];
            $this->coefficients = $components['coefficients'];
            $this->publicExponent = $components['publicExponent'];
        } else {
            $this->primes = array();
            $this->exponents = array();
            $this->coefficients = array();
            $this->publicExponent = false;
        }
        switch ($type) {
            case CRYPT_RSA_PUBLIC_FORMAT_OPENSSH:
            case CRYPT_RSA_PUBLIC_FORMAT_RAW:
                $this->setPublicKey();
                break;
            case CRYPT_RSA_PRIVATE_FORMAT_PKCS1:
                switch (true) {
                    case strpos($key, '-BEGIN PUBLIC KEY-') !== false:
                    case strpos($key, '-BEGIN RSA PUBLIC KEY-') !== false:
                        $this->setPublicKey();
                }
        }
        return true;
    }

    function setPassword($password = false)
    {
        $this->password = $password;
    }

    function setPublicKey($key = false, $type = false)
    {
        if (!empty($this->publicExponent)) {
            return false;
        }
        if ($key === false && !empty($this->modulus)) {
            $this->publicExponent = $this->exponent;
            return true;
        }
        if ($type === false) {
            $types = array(
                CRYPT_RSA_PUBLIC_FORMAT_RAW,
                CRYPT_RSA_PUBLIC_FORMAT_PKCS1,
                CRYPT_RSA_PUBLIC_FORMAT_XML,
                CRYPT_RSA_PUBLIC_FORMAT_OPENSSH
            );
            foreach ($types as $type) {
                $components = $this->_parseKey($key, $type);
                if ($components !== false) {
                    break;
                }
            }
        } else {
            $components = $this->_parseKey($key, $type);
        }
        if ($components === false) {
            return false;
        }
        if (empty($this->modulus) || !$this->modulus->equals($components['modulus'])) {
            $this->modulus = $components['modulus'];
            $this->exponent = $this->publicExponent = $components['publicExponent'];
            return true;
        }
        $this->publicExponent = $components['publicExponent'];
        return true;
    }

    function setPrivateKey($key = false, $type = false)
    {
        if ($key === false && !empty($this->publicExponent)) {
            $this->publicExponent = false;
            return true;
        }
        $rsa = new Crypt_RSA();
        if (!$rsa->loadKey($key, $type)) {
            return false;
        }
        $rsa->publicExponent = false;
        $this->loadKey($rsa);
        return true;
    }

    function getPublicKey($type = CRYPT_RSA_PUBLIC_FORMAT_PKCS8)
    {
        if (empty($this->modulus) || empty($this->publicExponent)) {
            return false;
        }
        $oldFormat = $this->publicKeyFormat;
        $this->publicKeyFormat = $type;
        $temp = $this->_convertPublicKey($this->modulus, $this->publicExponent);
        $this->publicKeyFormat = $oldFormat;
        return $temp;
    }

    function getPublicKeyFingerprint($algorithm = 'md5')
    {
        if (empty($this->modulus) || empty($this->publicExponent)) {
            return false;
        }
        $modulus = $this->modulus->toBytes(true);
        $publicExponent = $this->publicExponent->toBytes(true);
        $RSAPublicKey = pack('Na*Na*Na*', strlen('ssh-rsa'), 'ssh-rsa', strlen($publicExponent), $publicExponent, strlen($modulus), $modulus);
        switch ($algorithm) {
            case 'sha256':
                $hash = new Crypt_Hash('sha256');
                $base = base64_encode($hash->hash($RSAPublicKey));
                return substr($base, 0, strlen($base) - 1);
            case 'md5':
                return substr(chunk_split(md5($RSAPublicKey), 2, ':'), 0, -1);
            default:
                return false;
        }
    }

    function getPrivateKey($type = CRYPT_RSA_PUBLIC_FORMAT_PKCS1)
    {
        if (empty($this->primes)) {
            return false;
        }
        $oldFormat = $this->privateKeyFormat;
        $this->privateKeyFormat = $type;
        $temp = $this->_convertPrivateKey($this->modulus, $this->publicExponent, $this->exponent, $this->primes, $this->exponents, $this->coefficients);
        $this->privateKeyFormat = $oldFormat;
        return $temp;
    }

    function _getPrivatePublicKey($mode = CRYPT_RSA_PUBLIC_FORMAT_PKCS8)
    {
        if (empty($this->modulus) || empty($this->exponent)) {
            return false;
        }
        $oldFormat = $this->publicKeyFormat;
        $this->publicKeyFormat = $mode;
        $temp = $this->_convertPublicKey($this->modulus, $this->exponent);
        $this->publicKeyFormat = $oldFormat;
        return $temp;
    }

    function __toString()
    {
        $key = $this->getPrivateKey($this->privateKeyFormat);
        if ($key !== false) {
            return $key;
        }
        $key = $this->_getPrivatePublicKey($this->publicKeyFormat);
        return $key !== false ? $key : '';
    }

    function __clone()
    {
        $key = new Crypt_RSA();
        $key->loadKey($this);
        return $key;
    }

    function _generateMinMax($bits)
    {
        $bytes = $bits >> 3;
        $min = str_repeat(chr(0), $bytes);
        $max = str_repeat(chr(0xFF), $bytes);
        $msb = $bits & 7;
        if ($msb) {
            $min = chr(1 << ($msb - 1)) . $min;
            $max = chr((1 << $msb) - 1) . $max;
        } else {
            $min[0] = chr(0x80);
        }
        return array(
            'min' => new Math_BigInteger($min, 256),
            'max' => new Math_BigInteger($max, 256)
        );
    }

    function _decodeLength(&$string)
    {
        $length = ord($this->_string_shift($string));
        if ($length & 0x80) {
            $length&= 0x7F;
            $temp = $this->_string_shift($string, $length);
            list(, $length) = unpack('N', substr(str_pad($temp, 4, chr(0), STR_PAD_LEFT), -4));
        }
        return $length;
    }

    function _encodeLength($length)
    {
        if ($length <= 0x7F) {
            return chr($length);
        }
        $temp = ltrim(pack('N', $length), chr(0));
        return pack('Ca*', 0x80 | strlen($temp), $temp);
    }

    function _string_shift(&$string, $index = 1)
    {
        $substr = substr($string, 0, $index);
        $string = substr($string, $index);
        return $substr;
    }

    function setPrivateKeyFormat($format)
    {
        $this->privateKeyFormat = $format;
    }

    function setPublicKeyFormat($format)
    {
        $this->publicKeyFormat = $format;
    }

    function setHash($hash)
    {
        switch ($hash) {
            case 'md2':
            case 'md5':
            case 'sha1':
            case 'sha256':
            case 'sha384':
            case 'sha512':
                $this->hash = new Crypt_Hash($hash);
                $this->hashName = $hash;
                break;
            default:
                $this->hash = new Crypt_Hash('sha1');
                $this->hashName = 'sha1';
        }
        $this->hLen = $this->hash->getLength();
    }

    function setMGFHash($hash)
    {
        switch ($hash) {
            case 'md2':
            case 'md5':
            case 'sha1':
            case 'sha256':
            case 'sha384':
            case 'sha512':
                $this->mgfHash = new Crypt_Hash($hash);
                break;
            default:
                $this->mgfHash = new Crypt_Hash('sha1');
        }
        $this->mgfHLen = $this->mgfHash->getLength();
    }

    function setSaltLength($sLen)
    {
        $this->sLen = $sLen;
    }

    function _i2osp($x, $xLen)
    {
        $x = $x->toBytes();
        if (strlen($x) > $xLen) {
            user_error('Integer too large');
            return false;
        }
        return str_pad($x, $xLen, chr(0), STR_PAD_LEFT);
    }

    function _os2ip($x)
    {
        return new Math_BigInteger($x, 256);
    }

    function _exponentiate($x)
    {
        switch (true) {
            case empty($this->primes):
            case $this->primes[1]->equals($this->zero):
            case empty($this->coefficients):
            case $this->coefficients[2]->equals($this->zero):
            case empty($this->exponents):
            case $this->exponents[1]->equals($this->zero):
                return $x->modPow($this->exponent, $this->modulus);
        }
        $num_primes = count($this->primes);
        if (defined('CRYPT_RSA_DISABLE_BLINDING')) {
            $m_i = array(
                1 => $x->modPow($this->exponents[1], $this->primes[1]),
                2 => $x->modPow($this->exponents[2], $this->primes[2])
            );
            $h = $m_i[1]->subtract($m_i[2]);
            $h = $h->multiply($this->coefficients[2]);
            list(, $h) = $h->divide($this->primes[1]);
            $m = $m_i[2]->add($h->multiply($this->primes[2]));
            $r = $this->primes[1];
            for ($i = 3; $i <= $num_primes; $i++) {
                $m_i = $x->modPow($this->exponents[$i], $this->primes[$i]);
                $r = $r->multiply($this->primes[$i - 1]);
                $h = $m_i->subtract($m);
                $h = $h->multiply($this->coefficients[$i]);
                list(, $h) = $h->divide($this->primes[$i]);
                $m = $m->add($r->multiply($h));
            }
        } else {
            $smallest = $this->primes[1];
            for ($i = 2; $i <= $num_primes; $i++) {
                if ($smallest->compare($this->primes[$i]) > 0) {
                    $smallest = $this->primes[$i];
                }
            }
            $one = new Math_BigInteger(1);
            $r = $one->random($one, $smallest->subtract($one));
            $m_i = array(
                1 => $this->_blind($x, $r, 1),
                2 => $this->_blind($x, $r, 2)
            );
            $h = $m_i[1]->subtract($m_i[2]);
            $h = $h->multiply($this->coefficients[2]);
            list(, $h) = $h->divide($this->primes[1]);
            $m = $m_i[2]->add($h->multiply($this->primes[2]));
            $r = $this->primes[1];
            for ($i = 3; $i <= $num_primes; $i++) {
                $m_i = $this->_blind($x, $r, $i);
                $r = $r->multiply($this->primes[$i - 1]);
                $h = $m_i->subtract($m);
                $h = $h->multiply($this->coefficients[$i]);
                list(, $h) = $h->divide($this->primes[$i]);
                $m = $m->add($r->multiply($h));
            }
        }
        return $m;
    }

    function _blind($x, $r, $i)
    {
        $x = $x->multiply($r->modPow($this->publicExponent, $this->primes[$i]));
        $x = $x->modPow($this->exponents[$i], $this->primes[$i]);
        $r = $r->modInverse($this->primes[$i]);
        $x = $x->multiply($r);
        list(, $x) = $x->divide($this->primes[$i]);
        return $x;
    }

    function _equals($x, $y)
    {
        if (strlen($x) != strlen($y)) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < strlen($x); $i++) {
            $result |= ord($x[$i]) ^ ord($y[$i]);
        }
        return $result == 0;
    }

    function _rsaep($m)
    {
        if ($m->compare($this->zero) < 0 || $m->compare($this->modulus) > 0) {
            user_error('Message representative out of range');
            return false;
        }
        return $this->_exponentiate($m);
    }

    function _rsadp($c)
    {
        if ($c->compare($this->zero) < 0 || $c->compare($this->modulus) > 0) {
            user_error('Ciphertext representative out of range');
            return false;
        }
        return $this->_exponentiate($c);
    }

    function _rsasp1($m)
    {
        if ($m->compare($this->zero) < 0 || $m->compare($this->modulus) > 0) {
            user_error('Message representative out of range');
            return false;
        }
        return $this->_exponentiate($m);
    }

    function _rsavp1($s)
    {
        if ($s->compare($this->zero) < 0 || $s->compare($this->modulus) > 0) {
            user_error('Signature representative out of range');
            return false;
        }
        return $this->_exponentiate($s);
    }

    function _mgf1($mgfSeed, $maskLen)
    {
        $t = '';
        $count = ceil($maskLen / $this->mgfHLen);
        for ($i = 0; $i < $count; $i++) {
            $c = pack('N', $i);
            $t.= $this->mgfHash->hash($mgfSeed . $c);
        }
        return substr($t, 0, $maskLen);
    }

    function _rsaes_oaep_encrypt($m, $l = '')
    {
        $mLen = strlen($m);
        if ($mLen > $this->k - 2 * $this->hLen - 2) {
            user_error('Message too long');
            return false;
        }
        $lHash = $this->hash->hash($l);
        $ps = str_repeat(chr(0), $this->k - $mLen - 2 * $this->hLen - 2);
        $db = $lHash . $ps . chr(1) . $m;
        $seed = $this->crypt_random_string($this->hLen);
        $dbMask = $this->_mgf1($seed, $this->k - $this->hLen - 1);
        $maskedDB = $db ^ $dbMask;
        $seedMask = $this->_mgf1($maskedDB, $this->hLen);
        $maskedSeed = $seed ^ $seedMask;
        $em = chr(0) . $maskedSeed . $maskedDB;
        $m = $this->_os2ip($em);
        $c = $this->_rsaep($m);
        $c = $this->_i2osp($c, $this->k);
        return $c;
    }

    function _rsaes_oaep_decrypt($c, $l = '')
    {
        if (strlen($c) != $this->k || $this->k < 2 * $this->hLen + 2) {
            user_error('Decryption error');
            return false;
        }
        $c = $this->_os2ip($c);
        $m = $this->_rsadp($c);
        if ($m === false) {
            user_error('Decryption error');
            return false;
        }
        $em = $this->_i2osp($m, $this->k);
        $lHash = $this->hash->hash($l);
        $y = ord($em[0]);
        $maskedSeed = substr($em, 1, $this->hLen);
        $maskedDB = substr($em, $this->hLen + 1);
        $seedMask = $this->_mgf1($maskedDB, $this->hLen);
        $seed = $maskedSeed ^ $seedMask;
        $dbMask = $this->_mgf1($seed, $this->k - $this->hLen - 1);
        $db = $maskedDB ^ $dbMask;
        $lHash2 = substr($db, 0, $this->hLen);
        $m = substr($db, $this->hLen);
        if (!$this->_equals($lHash, $lHash2)) {
            user_error('Decryption error');
            return false;
        }
        $m = ltrim($m, chr(0));
        if (ord($m[0]) != 1) {
            user_error('Decryption error');
            return false;
        }
        return substr($m, 1);
    }

    function _raw_encrypt($m)
    {
        $temp = $this->_os2ip($m);
        $temp = $this->_rsaep($temp);
        return  $this->_i2osp($temp, $this->k);
    }

    function _rsaes_pkcs1_v1_5_encrypt($m)
    {
        $mLen = strlen($m);
        if ($mLen > $this->k - 11) {
            user_error('Message too long');
            return false;
        }
        $psLen = $this->k - $mLen - 3;
        $ps = '';
        while (strlen($ps) != $psLen) {
            $temp = $this->crypt_random_string($psLen - strlen($ps));
            $temp = str_replace("\x00", '', $temp);
            $ps.= $temp;
        }
        $type = 2;
        if (defined('CRYPT_RSA_PKCS15_COMPAT') && (!isset($this->publicExponent) || $this->exponent !== $this->publicExponent)) {
            $type = 1;
            $ps = str_repeat("\xFF", $psLen);
        }
        $em = chr(0) . chr($type) . $ps . chr(0) . $m;
        $m = $this->_os2ip($em);
        $c = $this->_rsaep($m);
        $c = $this->_i2osp($c, $this->k);
        return $c;
    }

    function _rsaes_pkcs1_v1_5_decrypt($c)
    {
        if (strlen($c) != $this->k) { // or if k < 11
            user_error('Decryption error');
            return false;
        }
        $c = $this->_os2ip($c);
        $m = $this->_rsadp($c);
        if ($m === false) {
            user_error('Decryption error');
            return false;
        }
        $em = $this->_i2osp($m, $this->k);
        if (ord($em[0]) != 0 || ord($em[1]) > 2) {
            user_error('Decryption error');
            return false;
        }
        $ps = substr($em, 2, strpos($em, chr(0), 2) - 2);
        $m = substr($em, strlen($ps) + 3);
        if (strlen($ps) < 8) {
            user_error('Decryption error');
            return false;
        }
        return $m;
    }

    function _emsa_pss_encode($m, $emBits)
    {
        $emLen = ($emBits + 1) >> 3;
        $sLen = $this->sLen !== null ? $this->sLen : $this->hLen;
        $mHash = $this->hash->hash($m);
        if ($emLen < $this->hLen + $sLen + 2) {
            user_error('Encoding error');
            return false;
        }
        $salt = $this->crypt_random_string($sLen);
        $m2 = "\0\0\0\0\0\0\0\0" . $mHash . $salt;
        $h = $this->hash->hash($m2);
        $ps = str_repeat(chr(0), $emLen - $sLen - $this->hLen - 2);
        $db = $ps . chr(1) . $salt;
        $dbMask = $this->_mgf1($h, $emLen - $this->hLen - 1);
        $maskedDB = $db ^ $dbMask;
        $maskedDB[0] = ~chr(0xFF << ($emBits & 7)) & $maskedDB[0];
        $em = $maskedDB . $h . chr(0xBC);
        return $em;
    }

    function _emsa_pss_verify($m, $em, $emBits)
    {
        $emLen = ($emBits + 1) >> 3;
        $sLen = $this->sLen !== null ? $this->sLen : $this->hLen;
        $mHash = $this->hash->hash($m);
        if ($emLen < $this->hLen + $sLen + 2) {
            return false;
        }
        if ($em[strlen($em) - 1] != chr(0xBC)) {
            return false;
        }
        $maskedDB = substr($em, 0, -$this->hLen - 1);
        $h = substr($em, -$this->hLen - 1, $this->hLen);
        $temp = chr(0xFF << ($emBits & 7));
        if ((~$maskedDB[0] & $temp) != $temp) {
            return false;
        }
        $dbMask = $this->_mgf1($h, $emLen - $this->hLen - 1);
        $db = $maskedDB ^ $dbMask;
        $db[0] = ~chr(0xFF << ($emBits & 7)) & $db[0];
        $temp = $emLen - $this->hLen - $sLen - 2;
        if (substr($db, 0, $temp) != str_repeat(chr(0), $temp) || ord($db[$temp]) != 1) {
            return false;
        }
        $salt = substr($db, $temp + 1);
        $m2 = "\0\0\0\0\0\0\0\0" . $mHash . $salt;
        $h2 = $this->hash->hash($m2);
        return $this->_equals($h, $h2);
    }

    function _rsassa_pss_sign($m)
    {
        $em = $this->_emsa_pss_encode($m, 8 * $this->k - 1);
        $m = $this->_os2ip($em);
        $s = $this->_rsasp1($m);
        $s = $this->_i2osp($s, $this->k);
        return $s;
    }

    function _rsassa_pss_verify($m, $s)
    {
        if (strlen($s) != $this->k) {
            user_error('Invalid signature');
            return false;
        }
        $modBits = 8 * $this->k;
        $s2 = $this->_os2ip($s);
        $m2 = $this->_rsavp1($s2);
        if ($m2 === false) {
            user_error('Invalid signature');
            return false;
        }
        $em = $this->_i2osp($m2, $modBits >> 3);
        if ($em === false) {
            user_error('Invalid signature');
            return false;
        }
        return $this->_emsa_pss_verify($m, $em, $modBits - 1);
    }

    function _emsa_pkcs1_v1_5_encode($m, $emLen)
    {
        $h = $this->hash->hash($m);
        if ($h === false) {
            return false;
        }
        switch ($this->hashName) {
            case 'md2':
                $t = pack('H*', '3020300c06082a864886f70d020205000410');
                break;
            case 'md5':
                $t = pack('H*', '3020300c06082a864886f70d020505000410');
                break;
            case 'sha1':
                $t = pack('H*', '3021300906052b0e03021a05000414');
                break;
            case 'sha256':
                $t = pack('H*', '3031300d060960864801650304020105000420');
                break;
            case 'sha384':
                $t = pack('H*', '3041300d060960864801650304020205000430');
                break;
            case 'sha512':
                $t = pack('H*', '3051300d060960864801650304020305000440');
        }
        $t.= $h;
        $tLen = strlen($t);
        if ($emLen < $tLen + 11) {
            user_error('Intended encoded message length too short');
            return false;
        }
        $ps = str_repeat(chr(0xFF), $emLen - $tLen - 3);
        $em = "\0\1$ps\0$t";
        return $em;
    }

    function _rsassa_pkcs1_v1_5_sign($m)
    {
        $em = $this->_emsa_pkcs1_v1_5_encode($m, $this->k);
        if ($em === false) {
            user_error('RSA modulus too short');
            return false;
        }
        $m = $this->_os2ip($em);
        $s = $this->_rsasp1($m);
        $s = $this->_i2osp($s, $this->k);
        return $s;
    }

    function _rsassa_pkcs1_v1_5_verify($m, $s)
    {
        if (strlen($s) != $this->k) {
            user_error('Invalid signature');
            return false;
        }
        $s = $this->_os2ip($s);
        $m2 = $this->_rsavp1($s);
        if ($m2 === false) {
            user_error('Invalid signature');
            return false;
        }
        $em = $this->_i2osp($m2, $this->k);
        if ($em === false) {
            user_error('Invalid signature');
            return false;
        }
        $em2 = $this->_emsa_pkcs1_v1_5_encode($m, $this->k);
        if ($em2 === false) {
            user_error('RSA modulus too short');
            return false;
        }
        return $this->_equals($em, $em2);
    }

    function setEncryptionMode($mode)
    {
        $this->encryptionMode = $mode;
    }

    function setSignatureMode($mode)
    {
        $this->signatureMode = $mode;
    }

    function setComment($comment)
    {
        $this->comment = $comment;
    }

    function getComment()
    {
        return $this->comment;
    }

    function encrypt($plaintext)
    {
        switch ($this->encryptionMode) {
            case CRYPT_RSA_ENCRYPTION_NONE:
                $plaintext = str_split($plaintext, $this->k);
                $ciphertext = '';
                foreach ($plaintext as $m) {
                    $ciphertext.= $this->_raw_encrypt($m);
                }
                return $ciphertext;
            case CRYPT_RSA_ENCRYPTION_PKCS1:
                $length = $this->k - 11;
                if ($length <= 0) {
                    return false;
                }
                $plaintext = str_split($plaintext, $length);
                $ciphertext = '';
                foreach ($plaintext as $m) {
                    $ciphertext.= $this->_rsaes_pkcs1_v1_5_encrypt($m);
                }
                return $ciphertext;
            default:
                $length = $this->k - 2 * $this->hLen - 2;
                if ($length <= 0) {
                    return false;
                }
                $plaintext = str_split($plaintext, $length);
                $ciphertext = '';
                foreach ($plaintext as $m) {
                    $ciphertext.= $this->_rsaes_oaep_encrypt($m);
                }
                return $ciphertext;
        }
    }

    function decrypt($ciphertext)
    {
        if ($this->k <= 0) {
            return false;
        }
        $ciphertext = str_split($ciphertext, $this->k);
        $ciphertext[count($ciphertext) - 1] = str_pad($ciphertext[count($ciphertext) - 1], $this->k, chr(0), STR_PAD_LEFT);
        $plaintext = '';
        switch ($this->encryptionMode) {
            case CRYPT_RSA_ENCRYPTION_NONE:
                $decrypt = '_raw_encrypt';
                break;
            case CRYPT_RSA_ENCRYPTION_PKCS1:
                $decrypt = '_rsaes_pkcs1_v1_5_decrypt';
                break;
            default:
                $decrypt = '_rsaes_oaep_decrypt';
        }
        foreach ($ciphertext as $c) {
            $temp = $this->$decrypt($c);
            if ($temp === false) {
                return false;
            }
            $plaintext.= $temp;
        }
        return $plaintext;
    }

    function sign($message)
    {
        if (empty($this->modulus) || empty($this->exponent)) {
            return false;
        }
        switch ($this->signatureMode) {
            case CRYPT_RSA_SIGNATURE_PKCS1:
                return $this->_rsassa_pkcs1_v1_5_sign($message);
            default:
                return $this->_rsassa_pss_sign($message);
        }
    }

    function verify($message, $signature)
    {
        if (empty($this->modulus) || empty($this->exponent)) {
            return false;
        }
        switch ($this->signatureMode) {
            case CRYPT_RSA_SIGNATURE_PKCS1:
                return $this->_rsassa_pkcs1_v1_5_verify($message, $signature);
            default:
                return $this->_rsassa_pss_verify($message, $signature);
        }
    }

    function _extractBER($str)
    {
        $temp = preg_replace('#.*?^-+[^-]+-+[\r\n ]*$#ms', '', $str, 1);
        $temp = preg_replace('#-+[^-]+-+#', '', $temp);
        $temp = str_replace(array("\r", "\n", ' '), '', $temp);
        $temp = preg_match('#^[a-zA-Z\d/+]*={0,2}$#', $temp) ? base64_decode($temp) : false;
        return $temp != false ? $temp : $str;
    }
}

define('MATH_BIGINTEGER_MONTGOMERY', 0);
define('MATH_BIGINTEGER_BARRETT', 1);
define('MATH_BIGINTEGER_POWEROF2', 2);
define('MATH_BIGINTEGER_CLASSIC', 3);
define('MATH_BIGINTEGER_NONE', 4);
define('MATH_BIGINTEGER_VALUE', 0);
define('MATH_BIGINTEGER_SIGN', 1);
define('MATH_BIGINTEGER_VARIABLE', 0);
define('MATH_BIGINTEGER_DATA', 1);
define('MATH_BIGINTEGER_MODE_INTERNAL', 1);
define('MATH_BIGINTEGER_MODE_BCMATH', 2);
define('MATH_BIGINTEGER_MODE_GMP', 3);
define('MATH_BIGINTEGER_KARATSUBA_CUTOFF', 25);

class Math_BigInteger
{
    var $value;
    var $is_negative = false;
    var $precision = -1;
    var $bitmask = false;
    var $hex;

    function __construct($x = 0, $base = 10)
    {
        if (!defined('MATH_BIGINTEGER_MODE')) {
            switch (true) {
                case extension_loaded('gmp'):
                    define('MATH_BIGINTEGER_MODE', MATH_BIGINTEGER_MODE_GMP);
                    break;
                case extension_loaded('bcmath'):
                    define('MATH_BIGINTEGER_MODE', MATH_BIGINTEGER_MODE_BCMATH);
                    break;
                default:
                    define('MATH_BIGINTEGER_MODE', MATH_BIGINTEGER_MODE_INTERNAL);
            }
        }
        if (extension_loaded('openssl') && !defined('MATH_BIGINTEGER_OPENSSL_DISABLE') && !defined('MATH_BIGINTEGER_OPENSSL_ENABLED')) {
            ob_start();
            @phpinfo();
            $content = ob_get_contents();
            ob_end_clean();
            preg_match_all('#OpenSSL (Header|Library) Version(.*)#im', $content, $matches);
            $versions = array();
            if (!empty($matches[1])) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $fullVersion = trim(str_replace('=>', '', strip_tags($matches[2][$i])));
                    if (!preg_match('/(\d+\.\d+\.\d+)/i', $fullVersion, $m)) {
                        $versions[$matches[1][$i]] = $fullVersion;
                    } else {
                        $versions[$matches[1][$i]] = $m[0];
                    }
                }
            }
            switch (true) {
                case !isset($versions['Header']):
                case !isset($versions['Library']):
                case $versions['Header'] == $versions['Library']:
                case version_compare($versions['Header'], '1.0.0') >= 0 && version_compare($versions['Library'], '1.0.0') >= 0:
                    define('MATH_BIGINTEGER_OPENSSL_ENABLED', true);
                    break;
                default:
                    define('MATH_BIGINTEGER_OPENSSL_DISABLE', true);
            }
        }
        if (!defined('PHP_INT_SIZE')) {
            define('PHP_INT_SIZE', 4);
        }
        if (!defined('MATH_BIGINTEGER_BASE') && MATH_BIGINTEGER_MODE == MATH_BIGINTEGER_MODE_INTERNAL) {
            switch (PHP_INT_SIZE) {
                case 8:
                    define('MATH_BIGINTEGER_BASE',       31);
                    define('MATH_BIGINTEGER_BASE_FULL',  0x80000000);
                    define('MATH_BIGINTEGER_MAX_DIGIT',  0x7FFFFFFF);
                    define('MATH_BIGINTEGER_MSB',        0x40000000);
                    define('MATH_BIGINTEGER_MAX10',      1000000000);
                    define('MATH_BIGINTEGER_MAX10_LEN',  9);
                    define('MATH_BIGINTEGER_MAX_DIGIT2', pow(2, 62));
                    break;
                default:
                    define('MATH_BIGINTEGER_BASE',       26);
                    define('MATH_BIGINTEGER_BASE_FULL',  0x4000000);
                    define('MATH_BIGINTEGER_MAX_DIGIT',  0x3FFFFFF);
                    define('MATH_BIGINTEGER_MSB',        0x2000000);
                    define('MATH_BIGINTEGER_MAX10',      10000000);
                    define('MATH_BIGINTEGER_MAX10_LEN',  7);
                    define('MATH_BIGINTEGER_MAX_DIGIT2', pow(2, 52));
            }
        }
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                switch (true) {
                    case is_resource($x) && get_resource_type($x) == 'GMP integer':
                    case is_object($x) && get_class($x) == 'GMP':
                        $this->value = $x;
                        return;
                }
                $this->value = gmp_init(0);
                break;
            case MATH_BIGINTEGER_MODE_BCMATH:
                $this->value = '0';
                break;
            default:
                $this->value = array();
        }
        if (empty($x) && (abs($base) != 256 || $x !== '0')) {
            return;
        }
        switch ($base) {
            case -256:
                if (ord($x[0]) & 0x80) {
                    $x = ~$x;
                    $this->is_negative = true;
                }
            case 256:
                switch (MATH_BIGINTEGER_MODE) {
                    case MATH_BIGINTEGER_MODE_GMP:
                        $this->value = function_exists('gmp_import') ?
                            gmp_import($x) :
                            gmp_init('0x' . bin2hex($x));
                        if ($this->is_negative) {
                            $this->value = gmp_neg($this->value);
                        }
                        break;
                    case MATH_BIGINTEGER_MODE_BCMATH:
                        $len = (strlen($x) + 3) & 0xFFFFFFFC;
                        $x = str_pad($x, $len, chr(0), STR_PAD_LEFT);
                        for ($i = 0; $i < $len; $i+= 4) {
                            $this->value = bcmul($this->value, '4294967296', 0);
                            $this->value = bcadd($this->value, 0x1000000 * ord($x[$i]) + ((ord($x[$i + 1]) << 16) | (ord($x[$i + 2]) << 8) | ord($x[$i + 3])), 0);
                        }
                        if ($this->is_negative) {
                            $this->value = '-' . $this->value;
                        }
                        break;
                    default:
                        while (strlen($x)) {
                            $this->value[] = $this->_bytes2int($this->_base256_rshift($x, MATH_BIGINTEGER_BASE));
                        }
                }
                if ($this->is_negative) {
                    if (MATH_BIGINTEGER_MODE != MATH_BIGINTEGER_MODE_INTERNAL) {
                        $this->is_negative = false;
                    }
                    $temp = $this->add(new Math_BigInteger('-1'));
                    $this->value = $temp->value;
                }
                break;
            case 16:
            case -16:
                if ($base > 0 && $x[0] == '-') {
                    $this->is_negative = true;
                    $x = substr($x, 1);
                }
                $x = preg_replace('#^(?:0x)?([A-Fa-f0-9]*).*#', '$1', $x);
                $is_negative = false;
                if ($base < 0 && hexdec($x[0]) >= 8) {
                    $this->is_negative = $is_negative = true;
                    $x = bin2hex(~pack('H*', $x));
                }
                switch (MATH_BIGINTEGER_MODE) {
                    case MATH_BIGINTEGER_MODE_GMP:
                        $temp = $this->is_negative ? '-0x' . $x : '0x' . $x;
                        $this->value = gmp_init($temp);
                        $this->is_negative = false;
                        break;
                    case MATH_BIGINTEGER_MODE_BCMATH:
                        $x = (strlen($x) & 1) ? '0' . $x : $x;
                        $temp = new Math_BigInteger(pack('H*', $x), 256);
                        $this->value = $this->is_negative ? '-' . $temp->value : $temp->value;
                        $this->is_negative = false;
                        break;
                    default:
                        $x = (strlen($x) & 1) ? '0' . $x : $x;
                        $temp = new Math_BigInteger(pack('H*', $x), 256);
                        $this->value = $temp->value;
                }
                if ($is_negative) {
                    $temp = $this->add(new Math_BigInteger('-1'));
                    $this->value = $temp->value;
                }
                break;
            case 10:
            case -10:
                $x = preg_replace('#(?<!^)(?:-).*|(?<=^|-)0*|[^-0-9].*#', '', $x);
                switch (MATH_BIGINTEGER_MODE) {
                    case MATH_BIGINTEGER_MODE_GMP:
                        $this->value = gmp_init($x);
                        break;
                    case MATH_BIGINTEGER_MODE_BCMATH:
                        $this->value = $x === '-' ? '0' : (string) $x;
                        break;
                    default:
                        $temp = new Math_BigInteger();
                        $multiplier = new Math_BigInteger();
                        $multiplier->value = array(MATH_BIGINTEGER_MAX10);
                        if ($x[0] == '-') {
                            $this->is_negative = true;
                            $x = substr($x, 1);
                        }
                        $x = str_pad($x, strlen($x) + ((MATH_BIGINTEGER_MAX10_LEN - 1) * strlen($x)) % MATH_BIGINTEGER_MAX10_LEN, 0, STR_PAD_LEFT);
                        while (strlen($x)) {
                            $temp = $temp->multiply($multiplier);
                            $temp = $temp->add(new Math_BigInteger($this->_int2bytes(substr($x, 0, MATH_BIGINTEGER_MAX10_LEN)), 256));
                            $x = substr($x, MATH_BIGINTEGER_MAX10_LEN);
                        }
                        $this->value = $temp->value;
                }
                break;
            case 2:
            case -2:
                if ($base > 0 && $x[0] == '-') {
                    $this->is_negative = true;
                    $x = substr($x, 1);
                }
                $x = preg_replace('#^([01]*).*#', '$1', $x);
                $x = str_pad($x, strlen($x) + (3 * strlen($x)) % 4, 0, STR_PAD_LEFT);
                $str = '0x';
                while (strlen($x)) {
                    $part = substr($x, 0, 4);
                    $str.= dechex(bindec($part));
                    $x = substr($x, 4);
                }
                if ($this->is_negative) {
                    $str = '-' . $str;
                }
                $temp = new Math_BigInteger($str, 8 * $base);
                $this->value = $temp->value;
                $this->is_negative = $temp->is_negative;
                break;
            default:
        }
    }

    function Math_BigInteger($x = 0, $base = 10)
    {
        $this->__construct($x, $base);
    }

    function toBytes($twos_compliment = false)
    {
        if ($twos_compliment) {
            $comparison = $this->compare(new Math_BigInteger());
            if ($comparison == 0) {
                return $this->precision > 0 ? str_repeat(chr(0), ($this->precision + 1) >> 3) : '';
            }
            $temp = $comparison < 0 ? $this->add(new Math_BigInteger(1)) : $this->copy();
            $bytes = $temp->toBytes();
            if (!strlen($bytes)) {
                $bytes = chr(0);
            }
            if (ord($bytes[0]) & 0x80) {
                $bytes = chr(0) . $bytes;
            }
            return $comparison < 0 ? ~$bytes : $bytes;
        }
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                if (gmp_cmp($this->value, gmp_init(0)) == 0) {
                    return $this->precision > 0 ? str_repeat(chr(0), ($this->precision + 1) >> 3) : '';
                }
                if (function_exists('gmp_export')) {
                    $temp = gmp_export($this->value);
                } else {
                    $temp = gmp_strval(gmp_abs($this->value), 16);
                    $temp = (strlen($temp) & 1) ? '0' . $temp : $temp;
                    $temp = pack('H*', $temp);
                }
                return $this->precision > 0 ?
                    substr(str_pad($temp, $this->precision >> 3, chr(0), STR_PAD_LEFT), -($this->precision >> 3)) :
                    ltrim($temp, chr(0));
            case MATH_BIGINTEGER_MODE_BCMATH:
                if ($this->value === '0') {
                    return $this->precision > 0 ? str_repeat(chr(0), ($this->precision + 1) >> 3) : '';
                }
                $value = '';
                $current = $this->value;
                if ($current[0] == '-') {
                    $current = substr($current, 1);
                }
                while (bccomp($current, '0', 0) > 0) {
                    $temp = bcmod($current, '16777216');
                    $value = chr($temp >> 16) . chr($temp >> 8) . chr($temp) . $value;
                    $current = bcdiv($current, '16777216', 0);
                }
                return $this->precision > 0 ?
                    substr(str_pad($value, $this->precision >> 3, chr(0), STR_PAD_LEFT), -($this->precision >> 3)) :
                    ltrim($value, chr(0));
        }
        if (!count($this->value)) {
            return $this->precision > 0 ? str_repeat(chr(0), ($this->precision + 1) >> 3) : '';
        }
        $result = $this->_int2bytes($this->value[count($this->value) - 1]);
        $temp = $this->copy();
        for ($i = count($temp->value) - 2; $i >= 0; --$i) {
            $temp->_base256_lshift($result, MATH_BIGINTEGER_BASE);
            $result = $result | str_pad($temp->_int2bytes($temp->value[$i]), strlen($result), chr(0), STR_PAD_LEFT);
        }
        return $this->precision > 0 ?
            str_pad(substr($result, -(($this->precision + 7) >> 3)), ($this->precision + 7) >> 3, chr(0), STR_PAD_LEFT) :
            $result;
    }

    function toHex($twos_compliment = false)
    {
        return bin2hex($this->toBytes($twos_compliment));
    }

    function toBits($twos_compliment = false)
    {
        $hex = $this->toHex($twos_compliment);
        $bits = '';
        for ($i = strlen($hex) - 8, $start = strlen($hex) & 7; $i >= $start; $i-=8) {
            $bits = str_pad(decbin(hexdec(substr($hex, $i, 8))), 32, '0', STR_PAD_LEFT) . $bits;
        }
        if ($start) {
            $bits = str_pad(decbin(hexdec(substr($hex, 0, $start))), 8, '0', STR_PAD_LEFT) . $bits;
        }
        $result = $this->precision > 0 ? substr($bits, -$this->precision) : ltrim($bits, '0');
        if ($twos_compliment && $this->compare(new Math_BigInteger()) > 0 && $this->precision <= 0) {
            return '0' . $result;
        }
        return $result;
    }

    function toString()
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                return gmp_strval($this->value);
            case MATH_BIGINTEGER_MODE_BCMATH:
                if ($this->value === '0') {
                    return '0';
                }
                return ltrim($this->value, '0');
        }
        if (!count($this->value)) {
            return '0';
        }
        $temp = $this->copy();
        $temp->is_negative = false;
        $divisor = new Math_BigInteger();
        $divisor->value = array(MATH_BIGINTEGER_MAX10);
        $result = '';
        while (count($temp->value)) {
            list($temp, $mod) = $temp->divide($divisor);
            $result = str_pad(isset($mod->value[0]) ? $mod->value[0] : '', MATH_BIGINTEGER_MAX10_LEN, '0', STR_PAD_LEFT) . $result;
        }
        $result = ltrim($result, '0');
        if (empty($result)) {
            $result = '0';
        }
        if ($this->is_negative) {
            $result = '-' . $result;
        }
        return $result;
    }

    function copy()
    {
        $temp = new Math_BigInteger();
        $temp->value = $this->value;
        $temp->is_negative = $this->is_negative;
        $temp->precision = $this->precision;
        $temp->bitmask = $this->bitmask;
        return $temp;
    }

    function __toString()
    {
        return $this->toString();
    }

    function __clone()
    {
        return $this->copy();
    }

    function __sleep()
    {
        $this->hex = $this->toHex(true);
        $vars = array('hex');
        if ($this->precision > 0) {
            $vars[] = 'precision';
        }
        return $vars;
    }

    function __wakeup()
    {
        $temp = new Math_BigInteger($this->hex, -16);
        $this->value = $temp->value;
        $this->is_negative = $temp->is_negative;
        if ($this->precision > 0) {
            $this->setPrecision($this->precision);
        }
    }

    function __debugInfo()
    {
        $opts = array();
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $engine = 'gmp';
                break;
            case MATH_BIGINTEGER_MODE_BCMATH:
                $engine = 'bcmath';
                break;
            case MATH_BIGINTEGER_MODE_INTERNAL:
                $engine = 'internal';
                $opts[] = PHP_INT_SIZE == 8 ? '64-bit' : '32-bit';
        }
        if (MATH_BIGINTEGER_MODE != MATH_BIGINTEGER_MODE_GMP && defined('MATH_BIGINTEGER_OPENSSL_ENABLED')) {
            $opts[] = 'OpenSSL';
        }
        if (!empty($opts)) {
            $engine.= ' (' . implode($opts, ', ') . ')';
        }
        return array(
            'value' => '0x' . $this->toHex(true),
            'engine' => $engine
        );
    }

    function add($y)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $temp = new Math_BigInteger();
                $temp->value = gmp_add($this->value, $y->value);
                return $this->_normalize($temp);
            case MATH_BIGINTEGER_MODE_BCMATH:
                $temp = new Math_BigInteger();
                $temp->value = bcadd($this->value, $y->value, 0);
                return $this->_normalize($temp);
        }
        $temp = $this->_add($this->value, $this->is_negative, $y->value, $y->is_negative);
        $result = new Math_BigInteger();
        $result->value = $temp[MATH_BIGINTEGER_VALUE];
        $result->is_negative = $temp[MATH_BIGINTEGER_SIGN];
        return $this->_normalize($result);
    }

    function _add($x_value, $x_negative, $y_value, $y_negative)
    {
        $x_size = count($x_value);
        $y_size = count($y_value);
        if ($x_size == 0) {
            return array(
                MATH_BIGINTEGER_VALUE => $y_value,
                MATH_BIGINTEGER_SIGN => $y_negative
            );
        } elseif ($y_size == 0) {
            return array(
                MATH_BIGINTEGER_VALUE => $x_value,
                MATH_BIGINTEGER_SIGN => $x_negative
            );
        }
        if ($x_negative != $y_negative) {
            if ($x_value == $y_value) {
                return array(
                    MATH_BIGINTEGER_VALUE => array(),
                    MATH_BIGINTEGER_SIGN => false
                );
            }
            $temp = $this->_subtract($x_value, false, $y_value, false);
            $temp[MATH_BIGINTEGER_SIGN] = $this->_compare($x_value, false, $y_value, false) > 0 ?
                                          $x_negative : $y_negative;
            return $temp;
        }
        if ($x_size < $y_size) {
            $size = $x_size;
            $value = $y_value;
        } else {
            $size = $y_size;
            $value = $x_value;
        }
        $value[count($value)] = 0;
        $carry = 0;
        for ($i = 0, $j = 1; $j < $size; $i+=2, $j+=2) {
            $sum = $x_value[$j] * MATH_BIGINTEGER_BASE_FULL + $x_value[$i] + $y_value[$j] * MATH_BIGINTEGER_BASE_FULL + $y_value[$i] + $carry;
            $carry = $sum >= MATH_BIGINTEGER_MAX_DIGIT2;
            $sum = $carry ? $sum - MATH_BIGINTEGER_MAX_DIGIT2 : $sum;
            $temp = MATH_BIGINTEGER_BASE === 26 ? intval($sum / 0x4000000) : ($sum >> 31);
            $value[$i] = (int) ($sum - MATH_BIGINTEGER_BASE_FULL * $temp);
            $value[$j] = $temp;
        }
        if ($j == $size) {
            $sum = $x_value[$i] + $y_value[$i] + $carry;
            $carry = $sum >= MATH_BIGINTEGER_BASE_FULL;
            $value[$i] = $carry ? $sum - MATH_BIGINTEGER_BASE_FULL : $sum;
            ++$i;
        }
        if ($carry) {
            for (; $value[$i] == MATH_BIGINTEGER_MAX_DIGIT; ++$i) {
                $value[$i] = 0;
            }
            ++$value[$i];
        }
        return array(
            MATH_BIGINTEGER_VALUE => $this->_trim($value),
            MATH_BIGINTEGER_SIGN => $x_negative
        );
    }

    function subtract($y)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $temp = new Math_BigInteger();
                $temp->value = gmp_sub($this->value, $y->value);
                return $this->_normalize($temp);
            case MATH_BIGINTEGER_MODE_BCMATH:
                $temp = new Math_BigInteger();
                $temp->value = bcsub($this->value, $y->value, 0);
                return $this->_normalize($temp);
        }
        $temp = $this->_subtract($this->value, $this->is_negative, $y->value, $y->is_negative);
        $result = new Math_BigInteger();
        $result->value = $temp[MATH_BIGINTEGER_VALUE];
        $result->is_negative = $temp[MATH_BIGINTEGER_SIGN];
        return $this->_normalize($result);
    }

    function _subtract($x_value, $x_negative, $y_value, $y_negative)
    {
        $x_size = count($x_value);
        $y_size = count($y_value);
        if ($x_size == 0) {
            return array(
                MATH_BIGINTEGER_VALUE => $y_value,
                MATH_BIGINTEGER_SIGN => !$y_negative
            );
        } elseif ($y_size == 0) {
            return array(
                MATH_BIGINTEGER_VALUE => $x_value,
                MATH_BIGINTEGER_SIGN => $x_negative
            );
        }
        if ($x_negative != $y_negative) {
            $temp = $this->_add($x_value, false, $y_value, false);
            $temp[MATH_BIGINTEGER_SIGN] = $x_negative;

            return $temp;
        }
        $diff = $this->_compare($x_value, $x_negative, $y_value, $y_negative);
        if (!$diff) {
            return array(
                MATH_BIGINTEGER_VALUE => array(),
                MATH_BIGINTEGER_SIGN => false
            );
        }
        if ((!$x_negative && $diff < 0) || ($x_negative && $diff > 0)) {
            $temp = $x_value;
            $x_value = $y_value;
            $y_value = $temp;
            $x_negative = !$x_negative;
            $x_size = count($x_value);
            $y_size = count($y_value);
        }
        $carry = 0;
        for ($i = 0, $j = 1; $j < $y_size; $i+=2, $j+=2) {
            $sum = $x_value[$j] * MATH_BIGINTEGER_BASE_FULL + $x_value[$i] - $y_value[$j] * MATH_BIGINTEGER_BASE_FULL - $y_value[$i] - $carry;
            $carry = $sum < 0;
            $sum = $carry ? $sum + MATH_BIGINTEGER_MAX_DIGIT2 : $sum;
            $temp = MATH_BIGINTEGER_BASE === 26 ? intval($sum / 0x4000000) : ($sum >> 31);
            $x_value[$i] = (int) ($sum - MATH_BIGINTEGER_BASE_FULL * $temp);
            $x_value[$j] = $temp;
        }
        if ($j == $y_size) {
            $sum = $x_value[$i] - $y_value[$i] - $carry;
            $carry = $sum < 0;
            $x_value[$i] = $carry ? $sum + MATH_BIGINTEGER_BASE_FULL : $sum;
            ++$i;
        }
        if ($carry) {
            for (; !$x_value[$i]; ++$i) {
                $x_value[$i] = MATH_BIGINTEGER_MAX_DIGIT;
            }
            --$x_value[$i];
        }
        return array(
            MATH_BIGINTEGER_VALUE => $this->_trim($x_value),
            MATH_BIGINTEGER_SIGN => $x_negative
        );
    }

    function multiply($x)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $temp = new Math_BigInteger();
                $temp->value = gmp_mul($this->value, $x->value);
                return $this->_normalize($temp);
            case MATH_BIGINTEGER_MODE_BCMATH:
                $temp = new Math_BigInteger();
                $temp->value = bcmul($this->value, $x->value, 0);
                return $this->_normalize($temp);
        }
        $temp = $this->_multiply($this->value, $this->is_negative, $x->value, $x->is_negative);
        $product = new Math_BigInteger();
        $product->value = $temp[MATH_BIGINTEGER_VALUE];
        $product->is_negative = $temp[MATH_BIGINTEGER_SIGN];
        return $this->_normalize($product);
    }

    function _multiply($x_value, $x_negative, $y_value, $y_negative)
    {
        $x_length = count($x_value);
        $y_length = count($y_value);
        if (!$x_length || !$y_length) {
            return array(
                MATH_BIGINTEGER_VALUE => array(),
                MATH_BIGINTEGER_SIGN => false
            );
        }
        return array(
            MATH_BIGINTEGER_VALUE => min($x_length, $y_length) < 2 * MATH_BIGINTEGER_KARATSUBA_CUTOFF ?
                $this->_trim($this->_regularMultiply($x_value, $y_value)) :
                $this->_trim($this->_karatsuba($x_value, $y_value)),
            MATH_BIGINTEGER_SIGN => $x_negative != $y_negative
        );
    }

    function _regularMultiply($x_value, $y_value)
    {
        $x_length = count($x_value);
        $y_length = count($y_value);
        if (!$x_length || !$y_length) {
            return array();
        }
        if ($x_length < $y_length) {
            $temp = $x_value;
            $x_value = $y_value;
            $y_value = $temp;

            $x_length = count($x_value);
            $y_length = count($y_value);
        }
        $product_value = $this->_array_repeat(0, $x_length + $y_length);
        $carry = 0;
        for ($j = 0; $j < $x_length; ++$j) {
            $temp = $x_value[$j] * $y_value[0] + $carry;
            $carry = MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31);
            $product_value[$j] = (int) ($temp - MATH_BIGINTEGER_BASE_FULL * $carry);
        }
        $product_value[$j] = $carry;
        for ($i = 1; $i < $y_length; ++$i) {
            $carry = 0;
            for ($j = 0, $k = $i; $j < $x_length; ++$j, ++$k) {
                $temp = $product_value[$k] + $x_value[$j] * $y_value[$i] + $carry;
                $carry = MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31);
                $product_value[$k] = (int) ($temp - MATH_BIGINTEGER_BASE_FULL * $carry);
            }
            $product_value[$k] = $carry;
        }
        return $product_value;
    }

    function _karatsuba($x_value, $y_value)
    {
        $m = min(count($x_value) >> 1, count($y_value) >> 1);
        if ($m < MATH_BIGINTEGER_KARATSUBA_CUTOFF) {
            return $this->_regularMultiply($x_value, $y_value);
        }
        $x1 = array_slice($x_value, $m);
        $x0 = array_slice($x_value, 0, $m);
        $y1 = array_slice($y_value, $m);
        $y0 = array_slice($y_value, 0, $m);
        $z2 = $this->_karatsuba($x1, $y1);
        $z0 = $this->_karatsuba($x0, $y0);
        $z1 = $this->_add($x1, false, $x0, false);
        $temp = $this->_add($y1, false, $y0, false);
        $z1 = $this->_karatsuba($z1[MATH_BIGINTEGER_VALUE], $temp[MATH_BIGINTEGER_VALUE]);
        $temp = $this->_add($z2, false, $z0, false);
        $z1 = $this->_subtract($z1, false, $temp[MATH_BIGINTEGER_VALUE], false);
        $z2 = array_merge(array_fill(0, 2 * $m, 0), $z2);
        $z1[MATH_BIGINTEGER_VALUE] = array_merge(array_fill(0, $m, 0), $z1[MATH_BIGINTEGER_VALUE]);
        $xy = $this->_add($z2, false, $z1[MATH_BIGINTEGER_VALUE], $z1[MATH_BIGINTEGER_SIGN]);
        $xy = $this->_add($xy[MATH_BIGINTEGER_VALUE], $xy[MATH_BIGINTEGER_SIGN], $z0, false);
        return $xy[MATH_BIGINTEGER_VALUE];
    }

    function _square($x = false)
    {
        return count($x) < 2 * MATH_BIGINTEGER_KARATSUBA_CUTOFF ?
            $this->_trim($this->_baseSquare($x)) :
            $this->_trim($this->_karatsubaSquare($x));
    }

    function _baseSquare($value)
    {
        if (empty($value)) {
            return array();
        }
        $square_value = $this->_array_repeat(0, 2 * count($value));
        for ($i = 0, $max_index = count($value) - 1; $i <= $max_index; ++$i) {
            $i2 = $i << 1;
            $temp = $square_value[$i2] + $value[$i] * $value[$i];
            $carry = MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31);
            $square_value[$i2] = (int) ($temp - MATH_BIGINTEGER_BASE_FULL * $carry);
            for ($j = $i + 1, $k = $i2 + 1; $j <= $max_index; ++$j, ++$k) {
                $temp = $square_value[$k] + 2 * $value[$j] * $value[$i] + $carry;
                $carry = MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31);
                $square_value[$k] = (int) ($temp - MATH_BIGINTEGER_BASE_FULL * $carry);
            }
            $square_value[$i + $max_index + 1] = $carry;
        }
        return $square_value;
    }

    function _karatsubaSquare($value)
    {
        $m = count($value) >> 1;
        if ($m < MATH_BIGINTEGER_KARATSUBA_CUTOFF) {
            return $this->_baseSquare($value);
        }
        $x1 = array_slice($value, $m);
        $x0 = array_slice($value, 0, $m);
        $z2 = $this->_karatsubaSquare($x1);
        $z0 = $this->_karatsubaSquare($x0);
        $z1 = $this->_add($x1, false, $x0, false);
        $z1 = $this->_karatsubaSquare($z1[MATH_BIGINTEGER_VALUE]);
        $temp = $this->_add($z2, false, $z0, false);
        $z1 = $this->_subtract($z1, false, $temp[MATH_BIGINTEGER_VALUE], false);
        $z2 = array_merge(array_fill(0, 2 * $m, 0), $z2);
        $z1[MATH_BIGINTEGER_VALUE] = array_merge(array_fill(0, $m, 0), $z1[MATH_BIGINTEGER_VALUE]);
        $xx = $this->_add($z2, false, $z1[MATH_BIGINTEGER_VALUE], $z1[MATH_BIGINTEGER_SIGN]);
        $xx = $this->_add($xx[MATH_BIGINTEGER_VALUE], $xx[MATH_BIGINTEGER_SIGN], $z0, false);
        return $xx[MATH_BIGINTEGER_VALUE];
    }

    function divide($y)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $quotient = new Math_BigInteger();
                $remainder = new Math_BigInteger();
                list($quotient->value, $remainder->value) = gmp_div_qr($this->value, $y->value);
                if (gmp_sign($remainder->value) < 0) {
                    $remainder->value = gmp_add($remainder->value, gmp_abs($y->value));
                }
                return array($this->_normalize($quotient), $this->_normalize($remainder));
            case MATH_BIGINTEGER_MODE_BCMATH:
                $quotient = new Math_BigInteger();
                $remainder = new Math_BigInteger();
                $quotient->value = bcdiv($this->value, $y->value, 0);
                $remainder->value = bcmod($this->value, $y->value);
                if ($remainder->value[0] == '-') {
                    $remainder->value = bcadd($remainder->value, $y->value[0] == '-' ? substr($y->value, 1) : $y->value, 0);
                }
                return array($this->_normalize($quotient), $this->_normalize($remainder));
        }
        if (count($y->value) == 1) {
            list($q, $r) = $this->_divide_digit($this->value, $y->value[0]);
            $quotient = new Math_BigInteger();
            $remainder = new Math_BigInteger();
            $quotient->value = $q;
            $remainder->value = array($r);
            $quotient->is_negative = $this->is_negative != $y->is_negative;
            return array($this->_normalize($quotient), $this->_normalize($remainder));
        }
        static $zero;
        if (!isset($zero)) {
            $zero = new Math_BigInteger();
        }
        $x = $this->copy();
        $y = $y->copy();
        $x_sign = $x->is_negative;
        $y_sign = $y->is_negative;
        $x->is_negative = $y->is_negative = false;
        $diff = $x->compare($y);
        if (!$diff) {
            $temp = new Math_BigInteger();
            $temp->value = array(1);
            $temp->is_negative = $x_sign != $y_sign;
            return array($this->_normalize($temp), $this->_normalize(new Math_BigInteger()));
        }
        if ($diff < 0) {
            if ($x_sign) {
                $x = $y->subtract($x);
            }
            return array($this->_normalize(new Math_BigInteger()), $this->_normalize($x));
        }
        $msb = $y->value[count($y->value) - 1];
        for ($shift = 0; !($msb & MATH_BIGINTEGER_MSB); ++$shift) {
            $msb <<= 1;
        }
        $x->_lshift($shift);
        $y->_lshift($shift);
        $y_value = &$y->value;
        $x_max = count($x->value) - 1;
        $y_max = count($y->value) - 1;
        $quotient = new Math_BigInteger();
        $quotient_value = &$quotient->value;
        $quotient_value = $this->_array_repeat(0, $x_max - $y_max + 1);
        static $temp, $lhs, $rhs;
        if (!isset($temp)) {
            $temp = new Math_BigInteger();
            $lhs =  new Math_BigInteger();
            $rhs =  new Math_BigInteger();
        }
        $temp_value = &$temp->value;
        $rhs_value =  &$rhs->value;
        $temp_value = array_merge($this->_array_repeat(0, $x_max - $y_max), $y_value);
        while ($x->compare($temp) >= 0) {
            ++$quotient_value[$x_max - $y_max];
            $x = $x->subtract($temp);
            $x_max = count($x->value) - 1;
        }
        for ($i = $x_max; $i >= $y_max + 1; --$i) {
            $x_value = &$x->value;
            $x_window = array(
                isset($x_value[$i]) ? $x_value[$i] : 0,
                isset($x_value[$i - 1]) ? $x_value[$i - 1] : 0,
                isset($x_value[$i - 2]) ? $x_value[$i - 2] : 0
            );
            $y_window = array(
                $y_value[$y_max],
                ($y_max > 0) ? $y_value[$y_max - 1] : 0
            );
            $q_index = $i - $y_max - 1;
            if ($x_window[0] == $y_window[0]) {
                $quotient_value[$q_index] = MATH_BIGINTEGER_MAX_DIGIT;
            } else {
                $quotient_value[$q_index] = $this->_safe_divide(
                    $x_window[0] * MATH_BIGINTEGER_BASE_FULL + $x_window[1],
                    $y_window[0]
                );
            }
            $temp_value = array($y_window[1], $y_window[0]);
            $lhs->value = array($quotient_value[$q_index]);
            $lhs = $lhs->multiply($temp);
            $rhs_value = array($x_window[2], $x_window[1], $x_window[0]);
            while ($lhs->compare($rhs) > 0) {
                --$quotient_value[$q_index];

                $lhs->value = array($quotient_value[$q_index]);
                $lhs = $lhs->multiply($temp);
            }
            $adjust = $this->_array_repeat(0, $q_index);
            $temp_value = array($quotient_value[$q_index]);
            $temp = $temp->multiply($y);
            $temp_value = &$temp->value;
            $temp_value = array_merge($adjust, $temp_value);
            $x = $x->subtract($temp);
            if ($x->compare($zero) < 0) {
                $temp_value = array_merge($adjust, $y_value);
                $x = $x->add($temp);

                --$quotient_value[$q_index];
            }
            $x_max = count($x_value) - 1;
        }
        $x->_rshift($shift);
        $quotient->is_negative = $x_sign != $y_sign;
        if ($x_sign) {
            $y->_rshift($shift);
            $x = $y->subtract($x);
        }
        return array($this->_normalize($quotient), $this->_normalize($x));
    }

    function _divide_digit($dividend, $divisor)
    {
        $carry = 0;
        $result = array();
        for ($i = count($dividend) - 1; $i >= 0; --$i) {
            $temp = MATH_BIGINTEGER_BASE_FULL * $carry + $dividend[$i];
            $result[$i] = $this->_safe_divide($temp, $divisor);
            $carry = (int) ($temp - $divisor * $result[$i]);
        }
        return array($result, $carry);
    }

    function modPow($e, $n)
    {
        $n = $this->bitmask !== false && $this->bitmask->compare($n) < 0 ? $this->bitmask : $n->abs();
        if ($e->compare(new Math_BigInteger()) < 0) {
            $e = $e->abs();
            $temp = $this->modInverse($n);
            if ($temp === false) {
                return false;
            }
            return $this->_normalize($temp->modPow($e, $n));
        }
        if (MATH_BIGINTEGER_MODE == MATH_BIGINTEGER_MODE_GMP) {
            $temp = new Math_BigInteger();
            $temp->value = gmp_powm($this->value, $e->value, $n->value);
            return $this->_normalize($temp);
        }
        if ($this->compare(new Math_BigInteger()) < 0 || $this->compare($n) > 0) {
            list(, $temp) = $this->divide($n);
            return $temp->modPow($e, $n);
        }
        if (defined('MATH_BIGINTEGER_OPENSSL_ENABLED')) {
            $components = array(
                'modulus' => $n->toBytes(true),
                'publicExponent' => $e->toBytes(true)
            );
            $components = array(
                'modulus' => pack('Ca*a*', 2, $this->_encodeASN1Length(strlen($components['modulus'])), $components['modulus']),
                'publicExponent' => pack('Ca*a*', 2, $this->_encodeASN1Length(strlen($components['publicExponent'])), $components['publicExponent'])
            );
            $RSAPublicKey = pack(
                'Ca*a*a*',
                48,
                $this->_encodeASN1Length(strlen($components['modulus']) + strlen($components['publicExponent'])),
                $components['modulus'],
                $components['publicExponent']
            );
            $rsaOID = pack('H*', '300d06092a864886f70d0101010500'); // hex version of MA0GCSqGSIb3DQEBAQUA
            $RSAPublicKey = chr(0) . $RSAPublicKey;
            $RSAPublicKey = chr(3) . $this->_encodeASN1Length(strlen($RSAPublicKey)) . $RSAPublicKey;
            $encapsulated = pack(
                'Ca*a*',
                48,
                $this->_encodeASN1Length(strlen($rsaOID . $RSAPublicKey)),
                $rsaOID . $RSAPublicKey
            );
            $RSAPublicKey = "-----BEGIN PUBLIC KEY-----\r\n" .
                             chunk_split(base64_encode($encapsulated)) .
                             '-----END PUBLIC KEY-----';
            $plaintext = str_pad($this->toBytes(), strlen($n->toBytes(true)) - 1, "\0", STR_PAD_LEFT);
            if (openssl_public_encrypt($plaintext, $result, $RSAPublicKey, OPENSSL_NO_PADDING)) {
                return new Math_BigInteger($result, 256);
            }
        }
        if (MATH_BIGINTEGER_MODE == MATH_BIGINTEGER_MODE_BCMATH) {
            $temp = new Math_BigInteger();
            $temp->value = bcpowmod($this->value, $e->value, $n->value, 0);

            return $this->_normalize($temp);
        }
        if (empty($e->value)) {
            $temp = new Math_BigInteger();
            $temp->value = array(1);
            return $this->_normalize($temp);
        }
        if ($e->value == array(1)) {
            list(, $temp) = $this->divide($n);
            return $this->_normalize($temp);
        }
        if ($e->value == array(2)) {
            $temp = new Math_BigInteger();
            $temp->value = $this->_square($this->value);
            list(, $temp) = $temp->divide($n);
            return $this->_normalize($temp);
        }
        return $this->_normalize($this->_slidingWindow($e, $n, MATH_BIGINTEGER_BARRETT));
        if ($n->value[0] & 1) {
            return $this->_normalize($this->_slidingWindow($e, $n, MATH_BIGINTEGER_MONTGOMERY));
        }
        for ($i = 0; $i < count($n->value); ++$i) {
            if ($n->value[$i]) {
                $temp = decbin($n->value[$i]);
                $j = strlen($temp) - strrpos($temp, '1') - 1;
                $j+= 26 * $i;
                break;
            }
        }
        $mod1 = $n->copy();
        $mod1->_rshift($j);
        $mod2 = new Math_BigInteger();
        $mod2->value = array(1);
        $mod2->_lshift($j);
        $part1 = ($mod1->value != array(1)) ? $this->_slidingWindow($e, $mod1, MATH_BIGINTEGER_MONTGOMERY) : new Math_BigInteger();
        $part2 = $this->_slidingWindow($e, $mod2, MATH_BIGINTEGER_POWEROF2);
        $y1 = $mod2->modInverse($mod1);
        $y2 = $mod1->modInverse($mod2);
        $result = $part1->multiply($mod2);
        $result = $result->multiply($y1);
        $temp = $part2->multiply($mod1);
        $temp = $temp->multiply($y2);
        $result = $result->add($temp);
        list(, $result) = $result->divide($n);
        return $this->_normalize($result);
    }

    function powMod($e, $n)
    {
        return $this->modPow($e, $n);
    }

    function _slidingWindow($e, $n, $mode)
    {
        static $window_ranges = array(7, 25, 81, 241, 673, 1793);
        $e_value = $e->value;
        $e_length = count($e_value) - 1;
        $e_bits = decbin($e_value[$e_length]);
        for ($i = $e_length - 1; $i >= 0; --$i) {
            $e_bits.= str_pad(decbin($e_value[$i]), MATH_BIGINTEGER_BASE, '0', STR_PAD_LEFT);
        }
        $e_length = strlen($e_bits);
        for ($i = 0, $window_size = 1; $i < count($window_ranges) && $e_length > $window_ranges[$i]; ++$window_size, ++$i) {
        }
        $n_value = $n->value;
        $powers = array();
        $powers[1] = $this->_prepareReduce($this->value, $n_value, $mode);
        $powers[2] = $this->_squareReduce($powers[1], $n_value, $mode);
        $temp = 1 << ($window_size - 1);
        for ($i = 1; $i < $temp; ++$i) {
            $i2 = $i << 1;
            $powers[$i2 + 1] = $this->_multiplyReduce($powers[$i2 - 1], $powers[2], $n_value, $mode);
        }
        $result = array(1);
        $result = $this->_prepareReduce($result, $n_value, $mode);
        for ($i = 0; $i < $e_length;) {
            if (!$e_bits[$i]) {
                $result = $this->_squareReduce($result, $n_value, $mode);
                ++$i;
            } else {
                for ($j = $window_size - 1; $j > 0; --$j) {
                    if (!empty($e_bits[$i + $j])) {
                        break;
                    }
                }
                for ($k = 0; $k <= $j; ++$k) {
                    $result = $this->_squareReduce($result, $n_value, $mode);
                }
                $result = $this->_multiplyReduce($result, $powers[bindec(substr($e_bits, $i, $j + 1))], $n_value, $mode);
                $i += $j + 1;
            }
        }
        $temp = new Math_BigInteger();
        $temp->value = $this->_reduce($result, $n_value, $mode);
        return $temp;
    }

    function _reduce($x, $n, $mode)
    {
        switch ($mode) {
            case MATH_BIGINTEGER_MONTGOMERY:
                return $this->_montgomery($x, $n);
            case MATH_BIGINTEGER_BARRETT:
                return $this->_barrett($x, $n);
            case MATH_BIGINTEGER_POWEROF2:
                $lhs = new Math_BigInteger();
                $lhs->value = $x;
                $rhs = new Math_BigInteger();
                $rhs->value = $n;
                return $x->_mod2($n);
            case MATH_BIGINTEGER_CLASSIC:
                $lhs = new Math_BigInteger();
                $lhs->value = $x;
                $rhs = new Math_BigInteger();
                $rhs->value = $n;
                list(, $temp) = $lhs->divide($rhs);
                return $temp->value;
            case MATH_BIGINTEGER_NONE:
                return $x;
            default:
        }
    }

    function _prepareReduce($x, $n, $mode)
    {
        if ($mode == MATH_BIGINTEGER_MONTGOMERY) {
            return $this->_prepMontgomery($x, $n);
        }
        return $this->_reduce($x, $n, $mode);
    }

    function _multiplyReduce($x, $y, $n, $mode)
    {
        if ($mode == MATH_BIGINTEGER_MONTGOMERY) {
            return $this->_montgomeryMultiply($x, $y, $n);
        }
        $temp = $this->_multiply($x, false, $y, false);
        return $this->_reduce($temp[MATH_BIGINTEGER_VALUE], $n, $mode);
    }

    function _squareReduce($x, $n, $mode)
    {
        if ($mode == MATH_BIGINTEGER_MONTGOMERY) {
            return $this->_montgomeryMultiply($x, $x, $n);
        }
        return $this->_reduce($this->_square($x), $n, $mode);
    }

    function _mod2($n)
    {
        $temp = new Math_BigInteger();
        $temp->value = array(1);
        return $this->bitwise_and($n->subtract($temp));
    }

    function _barrett($n, $m)
    {
        static $cache = array(
            MATH_BIGINTEGER_VARIABLE => array(),
            MATH_BIGINTEGER_DATA => array()
        );
        $m_length = count($m);
        if (count($n) > 2 * $m_length) {
            $lhs = new Math_BigInteger();
            $rhs = new Math_BigInteger();
            $lhs->value = $n;
            $rhs->value = $m;
            list(, $temp) = $lhs->divide($rhs);
            return $temp->value;
        }
        if ($m_length < 5) {
            return $this->_regularBarrett($n, $m);
        }
        if (($key = array_search($m, $cache[MATH_BIGINTEGER_VARIABLE])) === false) {
            $key = count($cache[MATH_BIGINTEGER_VARIABLE]);
            $cache[MATH_BIGINTEGER_VARIABLE][] = $m;
            $lhs = new Math_BigInteger();
            $lhs_value = &$lhs->value;
            $lhs_value = $this->_array_repeat(0, $m_length + ($m_length >> 1));
            $lhs_value[] = 1;
            $rhs = new Math_BigInteger();
            $rhs->value = $m;
            list($u, $m1) = $lhs->divide($rhs);
            $u = $u->value;
            $m1 = $m1->value;
            $cache[MATH_BIGINTEGER_DATA][] = array(
                'u' => $u,
                'm1'=> $m1
            );
        } else {
            extract($cache[MATH_BIGINTEGER_DATA][$key]);
        }
        $cutoff = $m_length + ($m_length >> 1);
        $lsd = array_slice($n, 0, $cutoff);
        $msd = array_slice($n, $cutoff);
        $lsd = $this->_trim($lsd);
        $temp = $this->_multiply($msd, false, $m1, false);
        $n = $this->_add($lsd, false, $temp[MATH_BIGINTEGER_VALUE], false);
        if ($m_length & 1) {
            return $this->_regularBarrett($n[MATH_BIGINTEGER_VALUE], $m);
        }
        $temp = array_slice($n[MATH_BIGINTEGER_VALUE], $m_length - 1);
        $temp = $this->_multiply($temp, false, $u, false);
        $temp = array_slice($temp[MATH_BIGINTEGER_VALUE], ($m_length >> 1) + 1);
        $temp = $this->_multiply($temp, false, $m, false);
        $result = $this->_subtract($n[MATH_BIGINTEGER_VALUE], false, $temp[MATH_BIGINTEGER_VALUE], false);
        while ($this->_compare($result[MATH_BIGINTEGER_VALUE], $result[MATH_BIGINTEGER_SIGN], $m, false) >= 0) {
            $result = $this->_subtract($result[MATH_BIGINTEGER_VALUE], $result[MATH_BIGINTEGER_SIGN], $m, false);
        }
        return $result[MATH_BIGINTEGER_VALUE];
    }

    function _regularBarrett($x, $n)
    {
        static $cache = array(
            MATH_BIGINTEGER_VARIABLE => array(),
            MATH_BIGINTEGER_DATA => array()
        );
        $n_length = count($n);
        if (count($x) > 2 * $n_length) {
            $lhs = new Math_BigInteger();
            $rhs = new Math_BigInteger();
            $lhs->value = $x;
            $rhs->value = $n;
            list(, $temp) = $lhs->divide($rhs);
            return $temp->value;
        }
        if (($key = array_search($n, $cache[MATH_BIGINTEGER_VARIABLE])) === false) {
            $key = count($cache[MATH_BIGINTEGER_VARIABLE]);
            $cache[MATH_BIGINTEGER_VARIABLE][] = $n;
            $lhs = new Math_BigInteger();
            $lhs_value = &$lhs->value;
            $lhs_value = $this->_array_repeat(0, 2 * $n_length);
            $lhs_value[] = 1;
            $rhs = new Math_BigInteger();
            $rhs->value = $n;
            list($temp, ) = $lhs->divide($rhs);
            $cache[MATH_BIGINTEGER_DATA][] = $temp->value;
        }
        $temp = array_slice($x, $n_length - 1);
        $temp = $this->_multiply($temp, false, $cache[MATH_BIGINTEGER_DATA][$key], false);
        $temp = array_slice($temp[MATH_BIGINTEGER_VALUE], $n_length + 1);
        $result = array_slice($x, 0, $n_length + 1);
        $temp = $this->_multiplyLower($temp, false, $n, false, $n_length + 1);
        if ($this->_compare($result, false, $temp[MATH_BIGINTEGER_VALUE], $temp[MATH_BIGINTEGER_SIGN]) < 0) {
            $corrector_value = $this->_array_repeat(0, $n_length + 1);
            $corrector_value[count($corrector_value)] = 1;
            $result = $this->_add($result, false, $corrector_value, false);
            $result = $result[MATH_BIGINTEGER_VALUE];
        }
        $result = $this->_subtract($result, false, $temp[MATH_BIGINTEGER_VALUE], $temp[MATH_BIGINTEGER_SIGN]);
        while ($this->_compare($result[MATH_BIGINTEGER_VALUE], $result[MATH_BIGINTEGER_SIGN], $n, false) > 0) {
            $result = $this->_subtract($result[MATH_BIGINTEGER_VALUE], $result[MATH_BIGINTEGER_SIGN], $n, false);
        }
        return $result[MATH_BIGINTEGER_VALUE];
    }

    function _multiplyLower($x_value, $x_negative, $y_value, $y_negative, $stop)
    {
        $x_length = count($x_value);
        $y_length = count($y_value);
        if (!$x_length || !$y_length) {
            return array(
                MATH_BIGINTEGER_VALUE => array(),
                MATH_BIGINTEGER_SIGN => false
            );
        }
        if ($x_length < $y_length) {
            $temp = $x_value;
            $x_value = $y_value;
            $y_value = $temp;
            $x_length = count($x_value);
            $y_length = count($y_value);
        }
        $product_value = $this->_array_repeat(0, $x_length + $y_length);
        $carry = 0;
        for ($j = 0; $j < $x_length; ++$j) {
            $temp = $x_value[$j] * $y_value[0] + $carry;
            $carry = MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31);
            $product_value[$j] = (int) ($temp - MATH_BIGINTEGER_BASE_FULL * $carry);
        }
        if ($j < $stop) {
            $product_value[$j] = $carry;
        }
        for ($i = 1; $i < $y_length; ++$i) {
            $carry = 0;
            for ($j = 0, $k = $i; $j < $x_length && $k < $stop; ++$j, ++$k) {
                $temp = $product_value[$k] + $x_value[$j] * $y_value[$i] + $carry;
                $carry = MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31);
                $product_value[$k] = (int) ($temp - MATH_BIGINTEGER_BASE_FULL * $carry);
            }
            if ($k < $stop) {
                $product_value[$k] = $carry;
            }
        }
        return array(
            MATH_BIGINTEGER_VALUE => $this->_trim($product_value),
            MATH_BIGINTEGER_SIGN => $x_negative != $y_negative
        );
    }

    function _montgomery($x, $n)
    {
        static $cache = array(
            MATH_BIGINTEGER_VARIABLE => array(),
            MATH_BIGINTEGER_DATA => array()
        );
        if (($key = array_search($n, $cache[MATH_BIGINTEGER_VARIABLE])) === false) {
            $key = count($cache[MATH_BIGINTEGER_VARIABLE]);
            $cache[MATH_BIGINTEGER_VARIABLE][] = $x;
            $cache[MATH_BIGINTEGER_DATA][] = $this->_modInverse67108864($n);
        }
        $k = count($n);
        $result = array(MATH_BIGINTEGER_VALUE => $x);
        for ($i = 0; $i < $k; ++$i) {
            $temp = $result[MATH_BIGINTEGER_VALUE][$i] * $cache[MATH_BIGINTEGER_DATA][$key];
            $temp = $temp - MATH_BIGINTEGER_BASE_FULL * (MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31));
            $temp = $this->_regularMultiply(array($temp), $n);
            $temp = array_merge($this->_array_repeat(0, $i), $temp);
            $result = $this->_add($result[MATH_BIGINTEGER_VALUE], false, $temp, false);
        }
        $result[MATH_BIGINTEGER_VALUE] = array_slice($result[MATH_BIGINTEGER_VALUE], $k);
        if ($this->_compare($result, false, $n, false) >= 0) {
            $result = $this->_subtract($result[MATH_BIGINTEGER_VALUE], false, $n, false);
        }
        return $result[MATH_BIGINTEGER_VALUE];
    }

    function _montgomeryMultiply($x, $y, $m)
    {
        $temp = $this->_multiply($x, false, $y, false);
        return $this->_montgomery($temp[MATH_BIGINTEGER_VALUE], $m);
        static $cache = array(
            MATH_BIGINTEGER_VARIABLE => array(),
            MATH_BIGINTEGER_DATA => array()
        );
        if (($key = array_search($m, $cache[MATH_BIGINTEGER_VARIABLE])) === false) {
            $key = count($cache[MATH_BIGINTEGER_VARIABLE]);
            $cache[MATH_BIGINTEGER_VARIABLE][] = $m;
            $cache[MATH_BIGINTEGER_DATA][] = $this->_modInverse67108864($m);
        }
        $n = max(count($x), count($y), count($m));
        $x = array_pad($x, $n, 0);
        $y = array_pad($y, $n, 0);
        $m = array_pad($m, $n, 0);
        $a = array(MATH_BIGINTEGER_VALUE => $this->_array_repeat(0, $n + 1));
        for ($i = 0; $i < $n; ++$i) {
            $temp = $a[MATH_BIGINTEGER_VALUE][0] + $x[$i] * $y[0];
            $temp = $temp - MATH_BIGINTEGER_BASE_FULL * (MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31));
            $temp = $temp * $cache[MATH_BIGINTEGER_DATA][$key];
            $temp = $temp - MATH_BIGINTEGER_BASE_FULL * (MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31));
            $temp = $this->_add($this->_regularMultiply(array($x[$i]), $y), false, $this->_regularMultiply(array($temp), $m), false);
            $a = $this->_add($a[MATH_BIGINTEGER_VALUE], false, $temp[MATH_BIGINTEGER_VALUE], false);
            $a[MATH_BIGINTEGER_VALUE] = array_slice($a[MATH_BIGINTEGER_VALUE], 1);
        }
        if ($this->_compare($a[MATH_BIGINTEGER_VALUE], false, $m, false) >= 0) {
            $a = $this->_subtract($a[MATH_BIGINTEGER_VALUE], false, $m, false);
        }
        return $a[MATH_BIGINTEGER_VALUE];
    }

    function _prepMontgomery($x, $n)
    {
        $lhs = new Math_BigInteger();
        $lhs->value = array_merge($this->_array_repeat(0, count($n)), $x);
        $rhs = new Math_BigInteger();
        $rhs->value = $n;
        list(, $temp) = $lhs->divide($rhs);
        return $temp->value;
    }

    function _modInverse67108864($x)
    {
        $x = -$x[0];
        $result = $x & 0x3;
        $result = ($result * (2 - $x * $result)) & 0xF;
        $result = ($result * (2 - ($x & 0xFF) * $result))  & 0xFF;
        $result = ($result * ((2 - ($x & 0xFFFF) * $result) & 0xFFFF)) & 0xFFFF;
        $result = fmod($result * (2 - fmod($x * $result, MATH_BIGINTEGER_BASE_FULL)), MATH_BIGINTEGER_BASE_FULL);
        return $result & MATH_BIGINTEGER_MAX_DIGIT;
    }

    function modInverse($n)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $temp = new Math_BigInteger();
                $temp->value = gmp_invert($this->value, $n->value);
                return ($temp->value === false) ? false : $this->_normalize($temp);
        }
        static $zero, $one;
        if (!isset($zero)) {
            $zero = new Math_BigInteger();
            $one = new Math_BigInteger(1);
        }
        $n = $n->abs();
        if ($this->compare($zero) < 0) {
            $temp = $this->abs();
            $temp = $temp->modInverse($n);
            return $this->_normalize($n->subtract($temp));
        }
        extract($this->extendedGCD($n));
        if (!$gcd->equals($one)) {
            return false;
        }
        $x = $x->compare($zero) < 0 ? $x->add($n) : $x;
        return $this->compare($zero) < 0 ? $this->_normalize($n->subtract($x)) : $this->_normalize($x);
    }

    function extendedGCD($n)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                extract(gmp_gcdext($this->value, $n->value));
                return array(
                    'gcd' => $this->_normalize(new Math_BigInteger($g)),
                    'x'   => $this->_normalize(new Math_BigInteger($s)),
                    'y'   => $this->_normalize(new Math_BigInteger($t))
                );
            case MATH_BIGINTEGER_MODE_BCMATH:
                $u = $this->value;
                $v = $n->value;
                $a = '1';
                $b = '0';
                $c = '0';
                $d = '1';
                while (bccomp($v, '0', 0) != 0) {
                    $q = bcdiv($u, $v, 0);
                    $temp = $u;
                    $u = $v;
                    $v = bcsub($temp, bcmul($v, $q, 0), 0);
                    $temp = $a;
                    $a = $c;
                    $c = bcsub($temp, bcmul($a, $q, 0), 0);
                    $temp = $b;
                    $b = $d;
                    $d = bcsub($temp, bcmul($b, $q, 0), 0);
                }
                return array(
                    'gcd' => $this->_normalize(new Math_BigInteger($u)),
                    'x'   => $this->_normalize(new Math_BigInteger($a)),
                    'y'   => $this->_normalize(new Math_BigInteger($b))
                );
        }
        $y = $n->copy();
        $x = $this->copy();
        $g = new Math_BigInteger();
        $g->value = array(1);
        while (!(($x->value[0] & 1)|| ($y->value[0] & 1))) {
            $x->_rshift(1);
            $y->_rshift(1);
            $g->_lshift(1);
        }
        $u = $x->copy();
        $v = $y->copy();
        $a = new Math_BigInteger();
        $b = new Math_BigInteger();
        $c = new Math_BigInteger();
        $d = new Math_BigInteger();
        $a->value = $d->value = $g->value = array(1);
        $b->value = $c->value = array();
        while (!empty($u->value)) {
            while (!($u->value[0] & 1)) {
                $u->_rshift(1);
                if ((!empty($a->value) && ($a->value[0] & 1)) || (!empty($b->value) && ($b->value[0] & 1))) {
                    $a = $a->add($y);
                    $b = $b->subtract($x);
                }
                $a->_rshift(1);
                $b->_rshift(1);
            }
            while (!($v->value[0] & 1)) {
                $v->_rshift(1);
                if ((!empty($d->value) && ($d->value[0] & 1)) || (!empty($c->value) && ($c->value[0] & 1))) {
                    $c = $c->add($y);
                    $d = $d->subtract($x);
                }
                $c->_rshift(1);
                $d->_rshift(1);
            }
            if ($u->compare($v) >= 0) {
                $u = $u->subtract($v);
                $a = $a->subtract($c);
                $b = $b->subtract($d);
            } else {
                $v = $v->subtract($u);
                $c = $c->subtract($a);
                $d = $d->subtract($b);
            }
        }
        return array(
            'gcd' => $this->_normalize($g->multiply($v)),
            'x'   => $this->_normalize($c),
            'y'   => $this->_normalize($d)
        );
    }

    function gcd($n)
    {
        extract($this->extendedGCD($n));
        return $gcd;
    }

    function abs()
    {
        $temp = new Math_BigInteger();
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $temp->value = gmp_abs($this->value);
                break;
            case MATH_BIGINTEGER_MODE_BCMATH:
                $temp->value = (bccomp($this->value, '0', 0) < 0) ? substr($this->value, 1) : $this->value;
                break;
            default:
                $temp->value = $this->value;
        }
        return $temp;
    }

    function compare($y)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                return gmp_cmp($this->value, $y->value);
            case MATH_BIGINTEGER_MODE_BCMATH:
                return bccomp($this->value, $y->value, 0);
        }

        return $this->_compare($this->value, $this->is_negative, $y->value, $y->is_negative);
    }

    function _compare($x_value, $x_negative, $y_value, $y_negative)
    {
        if ($x_negative != $y_negative) {
            return (!$x_negative && $y_negative) ? 1 : -1;
        }
        $result = $x_negative ? -1 : 1;
        if (count($x_value) != count($y_value)) {
            return (count($x_value) > count($y_value)) ? $result : -$result;
        }
        $size = max(count($x_value), count($y_value));
        $x_value = array_pad($x_value, $size, 0);
        $y_value = array_pad($y_value, $size, 0);
        for ($i = count($x_value) - 1; $i >= 0; --$i) {
            if ($x_value[$i] != $y_value[$i]) {
                return ($x_value[$i] > $y_value[$i]) ? $result : -$result;
            }
        }
        return 0;
    }

    function equals($x)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                return gmp_cmp($this->value, $x->value) == 0;
            default:
                return $this->value === $x->value && $this->is_negative == $x->is_negative;
        }
    }

    function setPrecision($bits)
    {
        $this->precision = $bits;
        if (MATH_BIGINTEGER_MODE != MATH_BIGINTEGER_MODE_BCMATH) {
            $this->bitmask = new Math_BigInteger(chr((1 << ($bits & 0x7)) - 1) . str_repeat(chr(0xFF), $bits >> 3), 256);
        } else {
            $this->bitmask = new Math_BigInteger(bcpow('2', $bits, 0));
        }
        $temp = $this->_normalize($this);
        $this->value = $temp->value;
    }

    function bitwise_and($x)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $temp = new Math_BigInteger();
                $temp->value = gmp_and($this->value, $x->value);
                return $this->_normalize($temp);
            case MATH_BIGINTEGER_MODE_BCMATH:
                $left = $this->toBytes();
                $right = $x->toBytes();
                $length = max(strlen($left), strlen($right));
                $left = str_pad($left, $length, chr(0), STR_PAD_LEFT);
                $right = str_pad($right, $length, chr(0), STR_PAD_LEFT);
                return $this->_normalize(new Math_BigInteger($left & $right, 256));
        }
        $result = $this->copy();
        $length = min(count($x->value), count($this->value));
        $result->value = array_slice($result->value, 0, $length);
        for ($i = 0; $i < $length; ++$i) {
            $result->value[$i]&= $x->value[$i];
        }
        return $this->_normalize($result);
    }

    function bitwise_or($x)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $temp = new Math_BigInteger();
                $temp->value = gmp_or($this->value, $x->value);
                return $this->_normalize($temp);
            case MATH_BIGINTEGER_MODE_BCMATH:
                $left = $this->toBytes();
                $right = $x->toBytes();
                $length = max(strlen($left), strlen($right));
                $left = str_pad($left, $length, chr(0), STR_PAD_LEFT);
                $right = str_pad($right, $length, chr(0), STR_PAD_LEFT);
                return $this->_normalize(new Math_BigInteger($left | $right, 256));
        }
        $length = max(count($this->value), count($x->value));
        $result = $this->copy();
        $result->value = array_pad($result->value, $length, 0);
        $x->value = array_pad($x->value, $length, 0);
        for ($i = 0; $i < $length; ++$i) {
            $result->value[$i]|= $x->value[$i];
        }
        return $this->_normalize($result);
    }

    function bitwise_xor($x)
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                $temp = new Math_BigInteger();
                $temp->value = gmp_xor(gmp_abs($this->value), gmp_abs($x->value));
                return $this->_normalize($temp);
            case MATH_BIGINTEGER_MODE_BCMATH:
                $left = $this->toBytes();
                $right = $x->toBytes();
                $length = max(strlen($left), strlen($right));
                $left = str_pad($left, $length, chr(0), STR_PAD_LEFT);
                $right = str_pad($right, $length, chr(0), STR_PAD_LEFT);
                return $this->_normalize(new Math_BigInteger($left ^ $right, 256));
        }
        $length = max(count($this->value), count($x->value));
        $result = $this->copy();
        $result->is_negative = false;
        $result->value = array_pad($result->value, $length, 0);
        $x->value = array_pad($x->value, $length, 0);
        for ($i = 0; $i < $length; ++$i) {
            $result->value[$i]^= $x->value[$i];
        }
        return $this->_normalize($result);
    }

    function bitwise_not()
    {
        $temp = $this->toBytes();
        if ($temp == '') {
            return $this->_normalize(new Math_BigInteger());
        }
        $pre_msb = decbin(ord($temp[0]));
        $temp = ~$temp;
        $msb = decbin(ord($temp[0]));
        if (strlen($msb) == 8) {
            $msb = substr($msb, strpos($msb, '0'));
        }
        $temp[0] = chr(bindec($msb));
        $current_bits = strlen($pre_msb) + 8 * strlen($temp) - 8;
        $new_bits = $this->precision - $current_bits;
        if ($new_bits <= 0) {
            return $this->_normalize(new Math_BigInteger($temp, 256));
        }
        $leading_ones = chr((1 << ($new_bits & 0x7)) - 1) . str_repeat(chr(0xFF), $new_bits >> 3);
        $this->_base256_lshift($leading_ones, $current_bits);
        $temp = str_pad($temp, strlen($leading_ones), chr(0), STR_PAD_LEFT);
        return $this->_normalize(new Math_BigInteger($leading_ones | $temp, 256));
    }

    function bitwise_rightShift($shift)
    {
        $temp = new Math_BigInteger();
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                static $two;
                if (!isset($two)) {
                    $two = gmp_init('2');
                }
                $temp->value = gmp_div_q($this->value, gmp_pow($two, $shift));
                break;
            case MATH_BIGINTEGER_MODE_BCMATH:
                $temp->value = bcdiv($this->value, bcpow('2', $shift, 0), 0);
                break;
            default:
                $temp->value = $this->value;
                $temp->_rshift($shift);
        }
        return $this->_normalize($temp);
    }

    function bitwise_leftShift($shift)
    {
        $temp = new Math_BigInteger();
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                static $two;
                if (!isset($two)) {
                    $two = gmp_init('2');
                }
                $temp->value = gmp_mul($this->value, gmp_pow($two, $shift));
                break;
            case MATH_BIGINTEGER_MODE_BCMATH:
                $temp->value = bcmul($this->value, bcpow('2', $shift, 0), 0);
                break;
            default:
                $temp->value = $this->value;
                $temp->_lshift($shift);
        }
        return $this->_normalize($temp);
    }

    function bitwise_leftRotate($shift)
    {
        $bits = $this->toBytes();
        if ($this->precision > 0) {
            $precision = $this->precision;
            if (MATH_BIGINTEGER_MODE == MATH_BIGINTEGER_MODE_BCMATH) {
                $mask = $this->bitmask->subtract(new Math_BigInteger(1));
                $mask = $mask->toBytes();
            } else {
                $mask = $this->bitmask->toBytes();
            }
        } else {
            $temp = ord($bits[0]);
            for ($i = 0; $temp >> $i; ++$i) {
            }
            $precision = 8 * strlen($bits) - 8 + $i;
            $mask = chr((1 << ($precision & 0x7)) - 1) . str_repeat(chr(0xFF), $precision >> 3);
        }
        if ($shift < 0) {
            $shift+= $precision;
        }
        $shift%= $precision;
        if (!$shift) {
            return $this->copy();
        }
        $left = $this->bitwise_leftShift($shift);
        $left = $left->bitwise_and(new Math_BigInteger($mask, 256));
        $right = $this->bitwise_rightShift($precision - $shift);
        $result = MATH_BIGINTEGER_MODE != MATH_BIGINTEGER_MODE_BCMATH ? $left->bitwise_or($right) : $left->add($right);
        return $this->_normalize($result);
    }

    function bitwise_rightRotate($shift)
    {
        return $this->bitwise_leftRotate(-$shift);
    }

    function setRandomGenerator($generator)
    {
    }
	
	function crypt_random_string($length)
    {
        if (!$length) {
            return '';
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (extension_loaded('mcrypt') && version_compare(PHP_VERSION, '5.3.0', '>=')) {
                return @mcrypt_create_iv($length);
            }
            if (extension_loaded('openssl') && version_compare(PHP_VERSION, '5.3.4', '>=')) {
                return openssl_random_pseudo_bytes($length);
            }
        } else {
            if (extension_loaded('openssl') && version_compare(PHP_VERSION, '5.3.0', '>=')) {
                return openssl_random_pseudo_bytes($length);
            }
            static $fp = true;
            if ($fp === true) {
                $fp = @fopen('/dev/urandom', 'rb');
            }
            if ($fp !== true && $fp !== false) {
                return fread($fp, $length);
            }
            if (extension_loaded('mcrypt')) {
                return @mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            }
        }
		return '';
    }

    function _random_number_helper($size)
    {
        if (function_exists('crypt_random_string')) {
            $random = crypt_random_string($size);
        } else {
            $random = '';
            if ($size & 1) {
                $random.= chr(mt_rand(0, 255));
            }
            $blocks = $size >> 1;
            for ($i = 0; $i < $blocks; ++$i) {
                $random.= pack('n', mt_rand(0, 0xFFFF));
            }
        }
        return new Math_BigInteger($random, 256);
    }

    function random($arg1, $arg2 = false)
    {
        if ($arg1 === false) {
            return false;
        }
        if ($arg2 === false) {
            $max = $arg1;
            $min = $this;
        } else {
            $min = $arg1;
            $max = $arg2;
        }
        $compare = $max->compare($min);
        if (!$compare) {
            return $this->_normalize($min);
        } elseif ($compare < 0) {
            $temp = $max;
            $max = $min;
            $min = $temp;
        }
        static $one;
        if (!isset($one)) {
            $one = new Math_BigInteger(1);
        }
        $max = $max->subtract($min->subtract($one));
        $size = strlen(ltrim($max->toBytes(), chr(0)));
        $random_max = new Math_BigInteger(chr(1) . str_repeat("\0", $size), 256);
        $random = $this->_random_number_helper($size);
        list($max_multiple) = $random_max->divide($max);
        $max_multiple = $max_multiple->multiply($max);
        while ($random->compare($max_multiple) >= 0) {
            $random = $random->subtract($max_multiple);
            $random_max = $random_max->subtract($max_multiple);
            $random = $random->bitwise_leftShift(8);
            $random = $random->add($this->_random_number_helper(1));
            $random_max = $random_max->bitwise_leftShift(8);
            list($max_multiple) = $random_max->divide($max);
            $max_multiple = $max_multiple->multiply($max);
        }
        list(, $random) = $random->divide($max);
        return $this->_normalize($random->add($min));
    }

    function randomPrime($arg1, $arg2 = false, $timeout = false)
    {
        if ($arg1 === false) {
            return false;
        }
        if ($arg2 === false) {
            $max = $arg1;
            $min = $this;
        } else {
            $min = $arg1;
            $max = $arg2;
        }
        $compare = $max->compare($min);
        if (!$compare) {
            return $min->isPrime() ? $min : false;
        } elseif ($compare < 0) {
            $temp = $max;
            $max = $min;
            $min = $temp;
        }
        static $one, $two;
        if (!isset($one)) {
            $one = new Math_BigInteger(1);
            $two = new Math_BigInteger(2);
        }
        $start = time();
        $x = $this->random($min, $max);
        if (MATH_BIGINTEGER_MODE == MATH_BIGINTEGER_MODE_GMP && extension_loaded('gmp') && version_compare(PHP_VERSION, '5.2.0', '>=')) {
            $p = new Math_BigInteger();
            $p->value = gmp_nextprime($x->value);
            if ($p->compare($max) <= 0) {
                return $p;
            }
            if (!$min->equals($x)) {
                $x = $x->subtract($one);
            }
            return $x->randomPrime($min, $x);
        }
        if ($x->equals($two)) {
            return $x;
        }
        $x->_make_odd();
        if ($x->compare($max) > 0) {
            if ($min->equals($max)) {
                return false;
            }
            $x = $min->copy();
            $x->_make_odd();
        }
        $initial_x = $x->copy();
        while (true) {
            if ($timeout !== false && time() - $start > $timeout) {
                return false;
            }
            if ($x->isPrime()) {
                return $x;
            }
            $x = $x->add($two);
            if ($x->compare($max) > 0) {
                $x = $min->copy();
                if ($x->equals($two)) {
                    return $x;
                }
                $x->_make_odd();
            }
            if ($x->equals($initial_x)) {
                return false;
            }
        }
    }

    function _make_odd()
    {
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                gmp_setbit($this->value, 0);
                break;
            case MATH_BIGINTEGER_MODE_BCMATH:
                if ($this->value[strlen($this->value) - 1] % 2 == 0) {
                    $this->value = bcadd($this->value, '1');
                }
                break;
            default:
                $this->value[0] |= 1;
        }
    }

    function isPrime($t = false)
    {
        $length = strlen($this->toBytes());
        if (!$t) {
            if ($length >= 163) { $t =  2; }
            else if ($length >= 106) { $t =  3; }
            else if ($length >= 81 ) { $t =  4; }
            else if ($length >= 68 ) { $t =  5; }
            else if ($length >= 56 ) { $t =  6; }
            else if ($length >= 50 ) { $t =  7; }
            else if ($length >= 43 ) { $t =  8; }
            else if ($length >= 37 ) { $t =  9; }
            else if ($length >= 31 ) { $t = 12; }
            else if ($length >= 25 ) { $t = 15; }
            else if ($length >= 18 ) { $t = 18; }
            else                     { $t = 27; }
        }
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                return gmp_prob_prime($this->value, $t) != 0;
            case MATH_BIGINTEGER_MODE_BCMATH:
                if ($this->value === '2') {
                    return true;
                }
                if ($this->value[strlen($this->value) - 1] % 2 == 0) {
                    return false;
                }
                break;
            default:
                if ($this->value == array(2)) {
                    return true;
                }
                if (~$this->value[0] & 1) {
                    return false;
                }
        }
        static $primes, $zero, $one, $two;
        if (!isset($primes)) {
            $primes = array(
                3,    5,    7,    11,   13,   17,   19,   23,   29,   31,   37,   41,   43,   47,   53,   59,
                61,   67,   71,   73,   79,   83,   89,   97,   101,  103,  107,  109,  113,  127,  131,  137,
                139,  149,  151,  157,  163,  167,  173,  179,  181,  191,  193,  197,  199,  211,  223,  227,
                229,  233,  239,  241,  251,  257,  263,  269,  271,  277,  281,  283,  293,  307,  311,  313,
                317,  331,  337,  347,  349,  353,  359,  367,  373,  379,  383,  389,  397,  401,  409,  419,
                421,  431,  433,  439,  443,  449,  457,  461,  463,  467,  479,  487,  491,  499,  503,  509,
                521,  523,  541,  547,  557,  563,  569,  571,  577,  587,  593,  599,  601,  607,  613,  617,
                619,  631,  641,  643,  647,  653,  659,  661,  673,  677,  683,  691,  701,  709,  719,  727,
                733,  739,  743,  751,  757,  761,  769,  773,  787,  797,  809,  811,  821,  823,  827,  829,
                839,  853,  857,  859,  863,  877,  881,  883,  887,  907,  911,  919,  929,  937,  941,  947,
                953,  967,  971,  977,  983,  991,  997
            );
            if (MATH_BIGINTEGER_MODE != MATH_BIGINTEGER_MODE_INTERNAL) {
                for ($i = 0; $i < count($primes); ++$i) {
                    $primes[$i] = new Math_BigInteger($primes[$i]);
                }
            }
            $zero = new Math_BigInteger();
            $one = new Math_BigInteger(1);
            $two = new Math_BigInteger(2);
        }
        if ($this->equals($one)) {
            return false;
        }
        if (MATH_BIGINTEGER_MODE != MATH_BIGINTEGER_MODE_INTERNAL) {
            foreach ($primes as $prime) {
                list(, $r) = $this->divide($prime);
                if ($r->equals($zero)) {
                    return $this->equals($prime);
                }
            }
        } else {
            $value = $this->value;
            foreach ($primes as $prime) {
                list(, $r) = $this->_divide_digit($value, $prime);
                if (!$r) {
                    return count($value) == 1 && $value[0] == $prime;
                }
            }
        }
        $n   = $this->copy();
        $n_1 = $n->subtract($one);
        $n_2 = $n->subtract($two);
        $r = $n_1->copy();
        $r_value = $r->value;
        if (MATH_BIGINTEGER_MODE == MATH_BIGINTEGER_MODE_BCMATH) {
            $s = 0;
            while ($r->value[strlen($r->value) - 1] % 2 == 0) {
                $r->value = bcdiv($r->value, '2', 0);
                ++$s;
            }
        } else {
            for ($i = 0, $r_length = count($r_value); $i < $r_length; ++$i) {
                $temp = ~$r_value[$i] & 0xFFFFFF;
                for ($j = 1; ($temp >> $j) & 1; ++$j) {
                }
                if ($j != 25) {
                    break;
                }
            }
            $s = 26 * $i + $j;
            $r->_rshift($s);
        }
        for ($i = 0; $i < $t; ++$i) {
            $a = $this->random($two, $n_2);
            $y = $a->modPow($r, $n);
            if (!$y->equals($one) && !$y->equals($n_1)) {
                for ($j = 1; $j < $s && !$y->equals($n_1); ++$j) {
                    $y = $y->modPow($two, $n);
                    if ($y->equals($one)) {
                        return false;
                    }
                }
                if (!$y->equals($n_1)) {
                    return false;
                }
            }
        }
        return true;
    }

    function _lshift($shift)
    {
        if ($shift == 0) {
            return;
        }
        $num_digits = (int) ($shift / MATH_BIGINTEGER_BASE);
        $shift %= MATH_BIGINTEGER_BASE;
        $shift = 1 << $shift;
        $carry = 0;
        for ($i = 0; $i < count($this->value); ++$i) {
            $temp = $this->value[$i] * $shift + $carry;
            $carry = MATH_BIGINTEGER_BASE === 26 ? intval($temp / 0x4000000) : ($temp >> 31);
            $this->value[$i] = (int) ($temp - $carry * MATH_BIGINTEGER_BASE_FULL);
        }
        if ($carry) {
            $this->value[count($this->value)] = $carry;
        }
        while ($num_digits--) {
            array_unshift($this->value, 0);
        }
    }

    function _rshift($shift)
    {
        if ($shift == 0) {
            return;
        }
        $num_digits = (int) ($shift / MATH_BIGINTEGER_BASE);
        $shift %= MATH_BIGINTEGER_BASE;
        $carry_shift = MATH_BIGINTEGER_BASE - $shift;
        $carry_mask = (1 << $shift) - 1;
        if ($num_digits) {
            $this->value = array_slice($this->value, $num_digits);
        }
        $carry = 0;
        for ($i = count($this->value) - 1; $i >= 0; --$i) {
            $temp = $this->value[$i] >> $shift | $carry;
            $carry = ($this->value[$i] & $carry_mask) << $carry_shift;
            $this->value[$i] = $temp;
        }
        $this->value = $this->_trim($this->value);
    }

    function _normalize($result)
    {
        $result->precision = $this->precision;
        $result->bitmask = $this->bitmask;
        switch (MATH_BIGINTEGER_MODE) {
            case MATH_BIGINTEGER_MODE_GMP:
                if ($this->bitmask !== false) {
                    $result->value = gmp_and($result->value, $result->bitmask->value);
                }
                return $result;
            case MATH_BIGINTEGER_MODE_BCMATH:
                if (!empty($result->bitmask->value)) {
                    $result->value = bcmod($result->value, $result->bitmask->value);
                }
                return $result;
        }
        $value = &$result->value;
        if (!count($value)) {
            return $result;
        }
        $value = $this->_trim($value);
        if (!empty($result->bitmask->value)) {
            $length = min(count($value), count($this->bitmask->value));
            $value = array_slice($value, 0, $length);
            for ($i = 0; $i < $length; ++$i) {
                $value[$i] = $value[$i] & $this->bitmask->value[$i];
            }
        }
        return $result;
    }

    function _trim($value)
    {
        for ($i = count($value) - 1; $i >= 0; --$i) {
            if ($value[$i]) {
                break;
            }
            unset($value[$i]);
        }
        return $value;
    }

    function _array_repeat($input, $multiplier)
    {
        return ($multiplier) ? array_fill(0, $multiplier, $input) : array();
    }

    function _base256_lshift(&$x, $shift)
    {
        if ($shift == 0) {
            return;
        }
        $num_bytes = $shift >> 3;
        $shift &= 7;
        $carry = 0;
        for ($i = strlen($x) - 1; $i >= 0; --$i) {
            $temp = ord($x[$i]) << $shift | $carry;
            $x[$i] = chr($temp);
            $carry = $temp >> 8;
        }
        $carry = ($carry != 0) ? chr($carry) : '';
        $x = $carry . $x . str_repeat(chr(0), $num_bytes);
    }

    function _base256_rshift(&$x, $shift)
    {
        if ($shift == 0) {
            $x = ltrim($x, chr(0));
            return '';
        }
        $num_bytes = $shift >> 3;
        $shift &= 7;
        $remainder = '';
        if ($num_bytes) {
            $start = $num_bytes > strlen($x) ? -strlen($x) : -$num_bytes;
            $remainder = substr($x, $start);
            $x = substr($x, 0, -$num_bytes);
        }
        $carry = 0;
        $carry_shift = 8 - $shift;
        for ($i = 0; $i < strlen($x); ++$i) {
            $temp = (ord($x[$i]) >> $shift) | $carry;
            $carry = (ord($x[$i]) << $carry_shift) & 0xFF;
            $x[$i] = chr($temp);
        }
        $x = ltrim($x, chr(0));
        $remainder = chr($carry >> $carry_shift) . $remainder;
        return ltrim($remainder, chr(0));
    }

    function _int2bytes($x)
    {
        return ltrim(pack('N', $x), chr(0));
    }

    function _bytes2int($x)
    {
        $temp = unpack('Nint', str_pad($x, 4, chr(0), STR_PAD_LEFT));
        return $temp['int'];
    }

    function _encodeASN1Length($length)
    {
        if ($length <= 0x7F) {
            return chr($length);
        }
        $temp = ltrim(pack('N', $length), chr(0));
        return pack('Ca*', 0x80 | strlen($temp), $temp);
    }

    function _safe_divide($x, $y)
    {
        if (MATH_BIGINTEGER_BASE === 26) {
            return (int) ($x / $y);
        }
        return ($x - ($x % $y)) / $y;
    }
}
?>

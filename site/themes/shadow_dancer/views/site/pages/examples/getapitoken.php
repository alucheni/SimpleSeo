<?php  
$baseUrl = Yii::app()->theme->baseUrl;
$cs = Yii::app()->getClientScript();

//jquery and google scripts
$cs->registerScriptFile('http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');

//jquery ui
$cs->registerCssFile('/themes/reports/css/custom-theme/jquery-ui-1.10.3.custom.css');
$cs->registerScriptFile('/themes/reports/js/jquery-ui-1.10.3.custom.min.js',CClientScript::POS_END);

//syntax highlighting
$cs->registerCssFile('/syntaxhighlighter/styles/shCore.css');
$cs->registerCssFile('/syntaxhighlighter/styles/shThemeDefault.css');
$cs->registerScriptFile('/syntaxhighlighter/scripts/shCore.js',CClientScript::POS_END);
$cs->registerScriptFile('/syntaxhighlighter/scripts/shAutoloader.js',CClientScript::POS_END);
$cs->registerScriptFile('/syntaxhighlighter/scripts/shBrushJScript.js',CClientScript::POS_END);
$cs->registerScriptFile('/syntaxhighlighter/scripts/shBrushXml.js',CClientScript::POS_END);
$cs->registerScriptFile('/syntaxhighlighter/scripts/shBrushPhp.js',CClientScript::POS_END);



?>
<div class="span-23 final" style="overflow:hidden">

<h1>Getting an API Token</h1>

<h2>First You Need an API Key</h2>
<p>
Checkout the tutorial on <a href="/site/pages/examples/getapikey">finding your API Key</a>.
</p>

<h2>Requesting an API Token</h2>
<p>
In order to protect your API Key, this site is setup to only accept Api Tokens when
making requests to the API.  These tokens are short lived tokens that grant access
for making requests and are tied to your account or username.
</p>

<h2>How the Token Works</h2>
<p class="margin-0">
A token is generated by taking the 
<a href="http://us1.php.net/manual/en/function.sha1.php" target="_blank">sha1 hash</a>
of a nonce and key.  So you need
to create a nonce.  Here is a simple example of how to create a nonce
(or one time random string) and corresponding hash in php.
</p>

<pre class="brush: php; gutter: true; toolbar: false; tab-size: 2;">
$nonce = substr(str_shuffle(MD5(microtime())),0,7);
$key = "[your API Key]";
$hash = sha1($nonce.$key);
</pre>

<p>
This is secure even over HTTP because your key is never sent in the clear.  This hash
cannot (now with the NSA stuff, maybe not) be decrypted to obtain the origional key.
</p>

<h2>Make a PHP Request</h2>
<p>
Using the PHP method <a target="_blank" href="http://php.net/manual/en/function.file-get-contents.php">file_get_contents</a>
makes making a request really simple.  The api request to:
</p>
<p style="padding-left:20px;">http://simple-seo-api.com<b>/api/tokens/getToken</b>?<span class="getKey">username=</span><span class="getVal">&lt;your username&gt;</span>&amp;<span class="getKey">nonce=</span><span class="getVal">&lt;the above noce&gt;</span>&amp;<span class="getKey">hash=</span><span class="getVal">&lt;the above hash&gt;</span>
</p>
<p class="margin-0">
Here is a simple way to apply the 
<a target="_blank" href="http://php.net/manual/en/function.file-get-contents.php">file_get_contents</a>
 and 
<a target="_blank" href="http://php.net/manual/en/function.sprintf.php">sprintf</a> 
functions.
</p> 
<pre class="brush: php; gutter: true; toolbar: false; tab-size: 2;">

$request = "http://www.simple-seo-api.com/api/tokens/getToken?username=demo&nonce=%s&hash=%s";
$result = file_get_contents(sprintf($request,$username,$nonce,$hash));

</pre>

<h2>...Or Use this Class</h2>
<p class="margin-0">Sample Use:</p>
<pre class="brush: php; gutter: true; toolbar: false; tab-size: 2;">

require_once 'ClientHash.php';
$token = \api\clients\ClientHash::getToken('[Your API Key]','[Your Username]');

</pre>

<p class="margin-0">Here is the ClientHash code:</p>


<pre class="brush: php; gutter: true; toolbar: false; tab-size: 2;">

//namespace
namespace api\clients;


/**
 * Simple wrapper for making a GET request
 * to api for a new token.
 * 
 * Use:
 * $token = api\clients\ClientHash::getToken('[your key]','[username]');
 * 
 * @author Will
 *
 */
class ClientHash{
	private static $secure = false;
	private static $apiHost = 'simple-seo-api.com';
	private static $api = '/tokens/getToken';
	
	/**
	 * Take user's private key and the username to make a 
	 * request to the api for a token.  The key is never sent
	 * in any request, so this keeps it secure.  You should
	 * never send your key out in any request.
	 * 
	 * @param string $key Your private activation key created when account was initiated.
	 * @param string $username Your username created at registration time
	 * @return string
	 * 
	 * @throws Exception An exception is thrown if the request fails to return a valid token.  Some
	 * informaiton about the error may be contained int he error message string.
	 */
	public static function getToken($key,$username){
		$nonce = substr(str_shuffle(MD5(microtime())),0,7);
		$hash = self::hash($nonce,$key);
		
		return self::makeRequest($username, $nonce, $hash);
	}
	
	/**
	 * Create the hash.  Just a wrapper for incase this class is
	 * expanded on in the future.
	 * 
	 * @param string $nonce Should be a one time used random string for a token request
	 * @param unknown $key Your private activation key created when account was initiated.
	 * @return string A hash string for token requests.
	 */
	private static function hash($nonce,$key){
		return sha1($nonce.$key);
	}
	
	/**
	 * Make the actual http request to the token service
	 * @param unknown $username
	 * @param unknown $nonce
	 * @param unknown $hash
	 * @throws Exception If a token was failed to be created then an error will be thrown.
	 * @return string
	 */
	private static function makeRequest($username, $nonce, $hash){
		$request = (self::$secure) ? 'https://' : 'http://';
		$request.= self::$apiHost . self::$api . '?username=%s&nonce=%s&hash=%s';
		
		$result = file_get_contents(sprintf($request,$username,$nonce,$hash));
		$result = json_decode($result);
		
		if(isset($result->success) && $result->success === 'true'){
			if(!empty($result->token))
				return $result->token;
		}
		
		$error = (!isset($result->message)) ? 'Unknown' : $result->message;
		throw new \Exception('Failed to get token.  Message: '.$error);
	}
}

</pre>
</div>
<script>
$(document).ready(function(){
	SyntaxHighlighter.all();
});
</script>
<?php
/*
 * Proxy Crawler + Auto Check + Auto Save Live Proxy
 *
 * Created by rafinetiz
 */
 
class Proxy {
	private $response;
	private $proxy;
	private $savefile;
	private $typeproxy;
	
	function __construct($savefile, $type) {
		$this->savefile  = $savefile;
		$this->typeproxy = $type;
	}
	
	function ambilProxy() {
		switch($this->typeproxy) {
			case "http":
				$proxyUrl = "https://free-proxy-list.net";
				break;
			case "https":
				$proxyUrl = "https://www.sslproxies.org";
				break;
			case "socks":
				$proxyUrl = "https://www.socks-proxy.net";
				break;
			default:
				echo "#> Unknown proxy type";
				exit();
				break;
		}
		
		echo "#> Mengambil {$this->typeproxy} proxy..." . PHP_EOL;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $proxyUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$resp = curl_exec($ch);
		curl_close($ch);
		$this->response = $resp;
		return $this;
	}
	
	function satukan() {
		preg_match_all('/<tr><td>([0-9.]+)<\/td><td>([0-9]+)<\/td>/', $this->response, $result);
		$this->proxy = array_combine($result[1], $result[2]);
		return $this;
	}
	
	function checkProxy() {
		$i = 0;
		foreach($this->proxy as $ip => $port) {
			echo "\e[44m#> Memeriksa proxy: ".$ip.':'.$port."\e[49m (".$i." of ".count($this->proxy).") | \e[4mCreated by rafinetiz\e[0m\r";
			if($con = @fsockopen($ip, $port, $errno, $error, 10)) {
				echo "\033[K#> \e[32mLive\e[0m => ". $ip .':'. $port . PHP_EOL;
				$this->saveProxy($ip, $port);
				fclose($con);
			} else {
				echo "\033[K#> \e[31mDie\e[0m  => ". $ip .':'. $port . ' | ' . $error . PHP_EOL;
			}
			$i++;
		}
	}
	
	function saveProxy($ip, $port) {
		file_put_contents($this->savefile, "{$ip}:{$port}" . PHP_EOL, FILE_APPEND);
	}
}

if($argc < 2) {
echo <<<"RFZ"
Usage: php {$argv[0]} PROXY_TYPE <filename>

PROXY_TYPE - Type of the proxy
filename   - File tempat menyimpan proxy (default: proxy.txt)

PROXY TYPE:
  http    - HTTP Proxy
  https   - HTTP Proxy with SSL/TLS Support
  socks   - SOCKS 4/5 Proxy

RFZ;
exit(1);
}

$outfile = ($argv[2] == null) ? 'proxy.txt' : (string)$argv[2];
$crawler = new Proxy($outfile, $argv[1]);
$crawler->ambilProxy()->satukan()->checkProxy();

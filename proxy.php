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
		if ($this->typeproxy === 'http') {
			$proxyUrl = 'https://free-proxy-list.net/';
		} else if($this->typeproxy === 'socks') {
			$proxyUrl = 'https://www.socks-proxy.net/';
		} else {
			echo "Unknown proxy\n";
			exit();
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
		if(file_exists($this->savefile)) {
			file_put_contents($this->savefile, "{$ip}:{$port}\n", FILE_APPEND);
		} else {
			file_put_contents($this->savefile, "Live {$this->typeproxy} proxy list:\n{$ip}:{$port}\n");
		}
	}
}

if($argc < 2) {
	echo "Usage: php {$argv[0]} PROXY_TYPE <filename>" . PHP_EOL . PHP_EOL;
	echo "PROXY_TYPE - type of the proxy\n";
	echo "filename   - proxy will be saved to this file (default: proxy.txt)\n";
	echo "Available proxy type:\n http\n socks\n";
	exit(1);
}

$outfile = ($argv[2] == null) ? 'proxy.txt' : (string)$argv[2];
$crawler = new Proxy($outfile, $argv[1]);
$crawler->ambilProxy()->satukan()->checkProxy();

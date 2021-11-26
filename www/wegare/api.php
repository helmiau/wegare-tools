<?php
function tunnel() {
	exec("nohup python3 /root/akun/tunnel.py > /dev/null 2>&1 &");
	sleep(1);
	exec("nohup python3 /root/akun/ssh.py 1 > /dev/null 2>&1 &");
	echo "is connecting to the internet\n";
	for ($i = 1; $i <= 3; $i++) {
		sleep(3);
		exec("cat logs.txt 2>/dev/null | grep \"CONNECTED SUCCESSFULLY\"|awk '{print $4}'|tail -n1", $var);
		if (implode($var) == "SUCCESSFULLY") {
			exec("screen -dmS GProxy badvpn-tun2socks --tundev tun1 --netif-ipaddr 10.0.0.2 --netif-netmask 255.255.255.0 --socks-server-addr 127.0.0.1:1080 --udpgw-remote-server-addr 127.0.0.1:7300 --udpgw-connection-buffer-size 65535 --udpgw-transparent-dns &");
			echo "TERHUBUNG!\n";
			break;
		} else {
			echo "{".$i."}. Reconnect 3s\n";
			exec("nohup python3 /root/akun/ssh.py 1 > /dev/null 2>&1 &");
		}
		echo "Failed!\n";
	}
}

function start() {
	exec("cat /root/akun/stl.txt | awk 'NR==2'", $cek);
	if (empty(implode($cek))) {
		echo "Anda Belum Membuat Profile";
	} else {
		stop();
		exec("cat /root/akun/pillstl.txt", $pillstl);
		if (implode($pillstl) == "1") {
			exec("route -n | grep -i 0.0.0.0 | head -n1 | awk '{print $2}'", $ipmodem);
			exec('echo "ipmodem='.implode($ipmodem).'" > /root/akun/ipmodem.txt');
			exec("cat /root/akun/stl.txt | awk 'NR==2'", $host);
			exec("cat /root/akun/ipmodem.txt | grep -i ipmodem | cut -d= -f2 | tail -n1", $route);
			exec("ip tuntap add dev tun1 mode tun");
			exec("ifconfig tun1 10.0.0.1 netmask 255.255.255.0");
			tunnel();
			exec("route add 8.8.8.8 gw ".implode($route)." metric 0");
			exec("route add 8.8.4.4 gw ".implode($route)." metric 0");
			exec("route add ".implode($host)." gw ".implode($route)." metric 0");
			exec("route add default gw 10.0.0.2 metric 0");
		} else if ($pillstl == "2") {
			tunnel();
		}
		exec("rm -r logs.txt 2>/dev/null");
		file_put_contents("/usr/bin/ping-stl", "#!/bin/bash\n");
		file_put_contents("/usr/bin/ping-stl", "#stl (Wegare)\n", FILE_APPEND);
		file_put_contents("/usr/bin/ping-stl", "httping m.google.com\n", FILE_APPEND);
		exec("chmod +x /usr/bin/ping-stl");
		exec("/usr/bin/ping-stl > /dev/null 2>&1 &");
	}
}

function stop() {
	exec("cat /root/akun/pillstl.txt", $pillstl);
	if (implode($pillstl) == "1") {
		exec("cat /root/akun/stl.txt | awk 'NR==2'", $host);
		exec("cat /root/akun/ipmodem.txt | grep -i ipmodem | cut -d= -f2 | tail -n1", $route);
		exec("killall -q badvpn-tun2socks ssh ping-stl sshpass httping python3");
		exec('route del 8.8.8.8 gw "'.implode($route).'" metric 0 2>/dev/null');
		exec('route del 8.8.4.4 gw "'.implode($route).'" metric 0 2>/dev/null');
		exec('route del "'.implode($host).'" gw "'.implode($route).'" metric 0 2>/dev/null');
		exec("ip link delete tun1 2>/dev/null");
	} else if (implode($pillstl) == "2") {
		exec("iptables -t nat -F OUTPUT 2>/dev/null");
		exec("iptables -t nat -F PROXY 2>/dev/null");
		exec("iptables -t nat -F PREROUTING 2>/dev/null");
		exec("killall -q redsocks python3 ssh ping-stl sshpass httping fping screen");
	}
	exec("/etc/init.d/dnsmasq restart 2>/dev/null");
}

function autoReconnect($val) {
	if ($val) {
		file_put_contents("/etc/crontabs/root", "# BEGIN AUTOREKONEKSTL\n", FILE_APPEND);
		file_put_contents("/etc/crontabs/root", "*/1 * * * *  autorekonek-stl\n", FILE_APPEND);
		file_put_contents("/etc/crontabs/root", "# END AUTOREKONEKSTL\n", FILE_APPEND);
		exec("sed -i '/^$/d' /etc/crontabs/root 2>/dev/null");
		exec("/etc/init.d/cron restart");
		echo "Enable Suksess";
	} else {
		exec('sed -i "/^# BEGIN AUTOREKONEKSTL/,/^# END AUTOREKONEKSTL/d" /etc/crontabs/root > /dev/null');
		exec("/etc/init.d/cron restart");
		echo "Disable Suksess";
	}
}

function saveConfig() {
	$pillstl = $_POST["pillstl"];
	$host = $_POST["host"];
	$port = $_POST["port"];
	$udp = $_POST["udp"];
	$user = $_POST["user"];
	$pass = $_POST["pass"];
	$bug = $_POST["bug"];
	$payload = $_POST["payload"];
	if ($pillstl == "1") {
		$badvpn = "badvpn-tun2socks --tundev tun1 --netif-ipaddr 10.0.0.2 --netif-netmask 255.255.255.0 --socks-server-addr 127.0.0.1:1080 --udpgw-remote-server-addr 127.0.0.1:$udp --udpgw-connection-buffer-size 65535 --udpgw-transparent-dns &";
	} else if ($pillstl == "2") {
		// Belum selesai
	}
	file_put_contents("/usr/bin/gproxy", $badvpn."\n");
	exec("chmod +x /usr/bin/gproxy");
	file_put_contents("/root/akun/settings.ini", "[mode]\n\nconnection_mode = 3\n\n[config]\npayload = ".$payload."\nproxyip = \nproxyport = \n\nauto_replace = 1\n\n[ssh]\nhost = ".$host."\nport = ".$port."\nusername = ".$user."\npassword = ".$pass."\n\n[sni]\nserver_name = ".$bug."\n");
	if (empty($udp)) $udp = "-";
	if (empty($payload)) $payload = "-";
	if (empty($proxy)) $proxy = "-";
	if (empty($pp)) $pp = "-";
	file_put_contents("/root/akun/stl.txt", "sp\n".$host."\n".$port."\n".$user."\n".$pass."\n".$udp."\n".$payload."\n".$proxy."\n".$pp."\n".$bug."\n");
	file_put_contents("/root/akun/pillstl.txt", $pillstl."\n");
	echo "Sett Profile Sukses";
}

$action = $_POST["action"];
switch ($action) {
	case "start";
		start();
		break;
	case "stop";
		stop();
		echo "Stop Sukses";
		break;
	case "saveConfig";
		saveConfig();
		break;
}
?>
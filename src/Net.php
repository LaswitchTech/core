<?php

/**
 * Core Framework - Net
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Net {

	// Constants
	const ListTCP = [
        "FTP DATA" => 20,
        "FTP CONTROL" => 21,
        "FTP" => 21,
        "SSH" => 22,
        "TELNET" => 23,
        "SMTP" => 25,
        "DNS" => 53,
        "HTTP" => 80,
        "POP3" => 110,
        "NNTP" => 119,
        "NTP" => 123,
        "IMAP" => 143,
        "SNMP" => 161,
        "IRC" => 194,
        "HTTPS" => 443,
        "SMTPS" => 465,
        "IMAPS" => 993,
        "POP3S" => 995,
        "VPN" => 1194,
        "OPENVPN" => 1194,
        "SQL" => 3306,
        "MYSQL" => 3306,
        "MARIADB" => 3306,
        "PROXMOX" => 8006,
        "ISPCONFIG" => 8080,
    ];
	const ListUDP = [
        "DNS" => 53,
        "DHCP CLIENT" => 67,
        "DHCP SERVER" => 68,
        "DHCP" => 68,
        "TFTP" => 69,
        "NTP" => 123,
        "NETBIOS" => 137,
        "NETBIOS NAME SERVICE" => 137,
        "NETBIOS DATAGRAM SERVICE" => 138,
        "SNMP" => 161,
        "IKEV1" => 500,
        "ISAKMP" => 500,
        "IKEV1 / ISAKMP" => 500,
        "SYSLOG" => 514,
        "RIP" => 520,
        "VPN" => 1194,
        "OPENVPN" => 1194,
        "UPNP" => 1900,
        "MDNS" => 5353,
        "BONJOUR" => 5353,
        "MDNS / BONJOUR" => 5353,
        "NETBUS" => 12345,
        "STEAM GAME SERVER" => 27015,
    ];
	const Ports = [
        "FTP DATA" => 20,
        "FTP CONTROL" => 21,
        "FTP" => 21,
        "SSH" => 22,
        "TELNET" => 23,
        "SMTP" => 25,
        "DNS" => 53,
        "HTTP" => 80,
        "POP3" => 110,
        "NNTP" => 119,
        "NTP" => 123,
        "NETBIOS" => 137,
        "NETBIOS NAME SERVICE" => 137,
        "NETBIOS DATAGRAM SERVICE" => 138,
        "IMAP" => 143,
        "SNMP" => 161,
        "IRC" => 194,
        "HTTPS" => 443,
        "SMTPS" => 465,
        "ISAKMP" => 500,
        "IKEV1" => 500,
        "IKEV1 / ISAKMP" => 500,
        "SYSLOG" => 514,
        "RIP" => 520,
        "DHCP CLIENT" => 67,
        "TFTP" => 69,
        "UPNP" => 1900,
        "MDNS" => 5353,
        "BONJOUR" => 5353,
        "MDNS / BONJOUR" => 5353,
        "POP3S" => 995,
        "IMAPS" => 993,
        "VPN" => 1194,
        "OPENVPN" => 1194,
        "SQL" => 3306,
        "MYSQL" => 3306,
        "MARIADB" => 3306,
        "DHCP SERVER" => 68,
        "DHCP" => 68,
        "PROXMOX" => 8006,
        "ISPCONFIG" => 8080,
        "NETBUS" => 12345,
        "STEAM GAME SERVER" => 27015,
    ];
	const Timeout = 5;

    // Global Properties
	protected $Log;

    /**
     * Constructor
     */
    public function __construct() {

        // Import Global Variables
        global $LOG;

        // Initialize Properties
        $this->Log = $LOG;
    }

    /**
     * Scan a port.
     *
     * @param  string  $ip
     * @param  string|int  $port
     * @param  int  $timeout
     * @return bool
     * @throws Exception
     */
    public function scan($ip, $port, $timeout = self::Timeout){
        try{

            // Debug Information
            $this->Log->debug("Scanning Port");
            $this->Log->debug("IP: {$ip}");
            $this->Log->debug("Port: {$port}");
            $this->Log->debug("Timeout: {$timeout}");

            // Validate IP Address
            if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE) && !filter_var($ip, FILTER_VALIDATE_DOMAIN)){
                throw new Exception("You need to supply a valid IPv4 address, IPv6 address or Fully Qualified Domain Name (FQDN).");
            }

            // Validate Port Number
            if(!is_string($port) && !is_int($port)){
                throw new Exception("Invalid network port.");
            }

            // Check if Port is a String
            if(is_string($port)){

                // Sanitize Port Name
                $port = strtoupper($port);

                // Check if known
                if(!isset(self::Ports[$port])){
                    throw new Exception("Unknown network port.");
                }

                // Convert Port Name to Number
                $port = self::Ports[$port];
            }

            // Validate Timeout
            if(!is_int($timeout)){
                throw new Exception("Invalid timeout.");
            }

            // Scan Port
            $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);

            // Handle Result
            if($socket){

                // Close Socket
                fclose($socket);

                // Logger
                $this->Log->success("Port {$port} on {$ip} is open.");

                // Return True
                return true;
            } else {

                // Debug Information
                $this->Log->debug("Error Number: {$errno}");
                $this->Log->debug("Error String: {$errstr}");

                // Logger
                $this->Log->error("Port {$port} on {$ip} is closed or blocked.");

                // Return False
                return false;
            }
        } catch (Exception $e) {
            $this->Log->error('Error: '.$e->getMessage());
        }
    }

    /**
     * Send a ping.
     *
     * @param  string  $ip
     * @return string|bool Return latency on success and false on failure
     * @throws Exception
     */
    public function ping($ip){
        try{

            // Debug Information
            $this->Log->debug("Sending Ping");
            $this->Log->debug("IP: {$ip}");

            // Validate IP Address
            if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE) && !filter_var($ip, FILTER_VALIDATE_DOMAIN)){
                throw new Exception("You need to supply a valid IPv4 address, IPv6 address or Fully Qualified Domain Name (FQDN).");
            }

            // Construct the ping request
            $ping = sprintf('ping -c 1 %s', escapeshellarg($ip));
            $output = '';

            // Debug Information
            $this->Log->debug("Request: {$ping}");

            // Send the ping request
            exec($ping, $output, $status);

            // Check if the ping was successful
            if ($status === 0) {

                // Sanitize Resonse
                if(!isset($output[1])){
                    throw new Exception("Unable to retrieve the command's response.");
                }
                $output = $output[1];

                // Retrieve Latency
                $output = preg_match('/time=([\d.]+)/', $output, $matches);

                // Check if latency was retrieved
                if($output){
                    $output = $matches[1];
                } else {
                    throw new Exception("Unable to retrieve latency.");
                }

                // Logger
                $this->Log->info("Ping latency is {$output}ms.");
                $this->Log->success("Ping to {$ip} was successful.");

                // Return Latency
                return $output;
            } else {

                // Logger
                $this->Log->error("Ping to {$ip} failed.");

                // Return False
                return false;
            }
        } catch (Exception $e) {
            $this->Log->error('Error: '.$e->getMessage());
        }
    }

    /**
     * Perform a DNS lookup.
     *
     * @param  string  $hostname
     * @param  string  $type
     * @return mixed  Returns an array of IP addresses on success, or false on failure
     * @throws Exception
     */
    public function lookup($hostname, $type = 'A') {
        try {
            // Debug Information
            $this->Log->debug("Performing DNS Lookup");
            $this->Log->debug("Hostname: {$hostname}");
            $this->Log->debug("Type: {$type}");

            // Validate hostname
            if (!filter_var($hostname, FILTER_VALIDATE_DOMAIN)) {
                throw new Exception("Invalid hostname.");
            }

            // Validate DNS record type
            if (!in_array($type, ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'PTR', 'SOA', 'TXT'])) {
                throw new Exception("Invalid DNS record type.");
            }

            // Perform the DNS lookup
            $result = dns_get_record($hostname, constant('DNS_' . $type));

            // Check if the DNS lookup failed
            if ($result === false) {
                throw new Exception("DNS lookup failed.");
            }

            // Extract IP addresses from the results
            $ips = array_filter(array_map(function($r) {
                if ($r['type'] === 'A' || $r['type'] === 'AAAA') {
                    return $r['ip'];
                }
                return null;
            }, $result));

            // Logger
            if (count($ips) > 0) {
                $this->Log->success("DNS lookup for {$hostname} succeeded. IP addresses: " . implode(', ', $ips));
            } else {
                $this->Log->error("DNS lookup for {$hostname} succeeded, but no IP addresses were found.");
            }

            // Return IP addresses or false
            return count($ips) > 0 ? $ips : false;
        } catch (Exception $e) {
            $this->Log->error('Error: ' . $e->getMessage());
            return false;
        }
    }
}

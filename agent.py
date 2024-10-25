from flask import Flask, request, jsonify
from scapy.all import *
import socket
import subprocess
import statistics
import platform
import re
import time
import threading
from twilio.rest import Client
import mysql.connector
import ipaddress

# Configurações do Twilio
TWILIO_SID = 'TWILIO_SID'  # Substitua pelo seu SID
TWILIO_AUTH_TOKEN = 'TWILIO_AUTH_TOKEN'  # Substitua pelo seu token
TWILIO_SMS_FROM = 'TWILIO_SMS_FROM'  # Substitua pelo seu número do Twilio

# Inicializa cliente Twilio
client = Client(TWILIO_SID, TWILIO_AUTH_TOKEN)

# Conexão com o banco de dados MySQL
db_config = {
    'host': 'localhost',
    'user': 'root',      # Substitua pelo seu usuário
    'password': 'root',     # Substitua pela sua senha
    'database': 'network_monitoring'
}

app = Flask(__name__)

def discover_active_hosts(network):
    net = ipaddress.IPv4Network(network, strict=False)
    answered, unanswered = srp(Ether(dst="ff:ff:ff:ff:ff:ff")/ARP(pdst=f"{net.network_address}/24"), timeout=2, verbose=False)
    active_hosts = []
    
    for send, receive in answered:
        ip = receive.psrc
        mac = receive.hwsrc
        ttl = get_ttl(ip)
        os_type = guess_os(ttl)

        # Inserir no banco de dados
        insert_active_host(ip, mac, ttl, os_type)
        
        active_hosts.append({"IP": ip, "MAC": mac, "TTL": ttl, "Sistema": os_type})

    return active_hosts

def get_ttl(ip):
    icmp = IP(dst=ip)/ICMP()
    response = sr1(icmp, timeout=2, verbose=False)
    return response.ttl if response else None

def guess_os(ttl):
    if ttl is None:
        return "Indeterminado"
    elif ttl > 100:
        return "Windows"
    elif ttl <= 64:
        return "Linux-Unix"
    else:
        return "Indeterminado"

def insert_active_host(ip, mac, ttl, os_type):
    connection = mysql.connector.connect(**db_config)
    cursor = connection.cursor()
    cursor.execute("INSERT INTO active_hosts (ip, mac, ttl, os) VALUES (%s, %s, %s, %s)", (ip, mac, ttl, os_type))
    connection.commit()
    cursor.close()
    connection.close()

def check_port(ip, port):
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as sock:
            sock.settimeout(1)
            result = sock.connect_ex((ip, port))
            return result == 0
    except Exception as e:
        return False

def scan_ports(ip):
    open_ports = []
    common_ports = {
        21: "FTP", 22: "SSH", 23: "Telnet", 25: "SMTP",
        53: "DNS", 80: "HTTP", 110: "POP3", 143: "IMAP",
        443: "HTTPS", 3306: "MySQL", 3389: "RDP", 8080: "HTTP alternativo"
    }

    for port, service in common_ports.items():
        if check_port(ip, port):
            open_ports.append((port, service))
            insert_open_port(ip, port, service)

    return open_ports

def insert_open_port(ip, port, service):
    connection = mysql.connector.connect(**db_config)
    cursor = connection.cursor()
    cursor.execute("INSERT INTO open_ports (ip, port, service) VALUES (%s, %s, %s)", (ip, port, service))
    connection.commit()
    cursor.close()
    connection.close()

def ping_ip(ip, count=4):
    param = '-n' if platform.system().lower() == 'windows' else '-c'
    cmd = ['ping', param, str(count), ip]
    try:
        response = subprocess.check_output(cmd, stderr=subprocess.STDOUT, universal_newlines=True)
        times = re.findall(r'tempo=(\d+)ms', response)
        if times:
            return statistics.mean([float(time) for time in times])
        return None
    except subprocess.CalledProcessError:
        return None

def send_sms(to_number, message):
    try:
        client.messages.create(body=message, from_=TWILIO_SMS_FROM, to=to_number)
    except Exception as e:
        print(f"Erro ao enviar SMS: {e}")

def monitor_ping(ip_to_monitor, sms_to, rede, alerta_variacao, intervalo_monitoramento):
    ping_anterior = ping_ip(ip_to_monitor)
    while True:
        ping_atual = ping_ip(ip_to_monitor)
        if ping_atual and abs(ping_atual - ping_anterior) > alerta_variacao:
            mensagem_alerta = f"Alerta: Ping de {ip_to_monitor} variou de {ping_anterior:.2f} ms para {ping_atual:.2f} ms"
            send_sms(sms_to, mensagem_alerta)
            ping_anterior = ping_atual
        time.sleep(intervalo_monitoramento)

@app.route('/discover_hosts', methods=['POST'])
def api_discover_hosts():
    data = request.get_json()
    network = data['network']
    active_hosts = discover_active_hosts(network)
    return jsonify(active_hosts)

@app.route('/scan_ports', methods=['POST'])
def api_scan_ports():
    data = request.get_json()
    ip = data['ip']
    open_ports = scan_ports(ip)
    return jsonify(open_ports)

@app.route('/ping_monitor', methods=['POST'])
def ping_monitor():
    data = request.get_json()
    ip = data['ip']
    alert_variation = data['alert_variation']
    interval = data['interval']
    sms_to = data['sms_to']
    
    # Adicione seu número de celular aqui
    # Iniciar o monitoramento em um thread separado
    monitor_thread = threading.Thread(target=monitor_ping, args=(ip, sms_to, "Rede", alert_variation, interval))
    monitor_thread.start()
    
    return jsonify({"message": f"Monitoramento iniciado para {ip}"})

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=2511)

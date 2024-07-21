from flask import Flask, render_template, request, jsonify
from flask_socketio import SocketIO, emit
import socket
import ipaddress
from pythonping import ping
from concurrent.futures import ThreadPoolExecutor
from threading import Thread
from twilio.rest import Client
import time
import subprocess
import platform

app = Flask(__name__)
socketio = SocketIO(app, async_mode='eventlet')

# Configuração do Twilio
account_sid = 'Your_account_sid'
auth_token = 'Your_token'
twilio_client = Client(account_sid, auth_token)
whatsapp_number = 'whatsapp:from'
your_whatsapp_number = 'whatsapp:to'

def send_whatsapp_alert(message):
    twilio_client.messages.create(
        body=message,
        from_=whatsapp_number,
        to=your_whatsapp_number
    )

def is_alive(ip):
    """Check if a given IP is alive by sending a ping."""
    param = '-n' if platform.system().lower() == 'windows' else '-c'
    try:
        output = subprocess.check_output(['ping', param, '1', ip], stderr=subprocess.STDOUT, universal_newlines=True)
        # Analise o output de forma mais robusta
        if "TTL=" in output:
            return True  # Presume-se que TTL indica sucesso
        else:
            return False
    except subprocess.CalledProcessError as e:
        print(f"Erro ao executar ping: {e}")
        return False
def scan_network(ip_range):
    ip_list = [str(ip) for ip in ipaddress.IPv4Network(ip_range)]
    alive_hosts = []
    for ip in ip_list:
        if is_alive(ip):
            alive_hosts.append(ip)
            socketio.emit('network_update', {'status': f'{ip} is alive'})
        else:
            socketio.emit('network_update', {'status': f'{ip} is offline'})
    return alive_hosts


def scan_ports(ip, ports):
    if not is_alive(ip):
        socketio.emit('port_update', {'host': ip, 'status': 'offline'})
        return []

    open_ports = []
    with ThreadPoolExecutor(max_workers=100) as executor:
        futures = {executor.submit(check_port, ip, port): port for port in ports}
        for future in futures:
            port = futures[future]
            if future.result():
                open_ports.append(port)
                socketio.emit('port_update', {'host': ip, 'port': port})
    return open_ports


def check_port(ip, port):
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.settimeout(0.5)
    try:
        result = sock.connect_ex((ip, port))
        return result == 0
    finally:
        sock.close()

def monitor_ping(host):
    while True:
        response_list = ping(host, size=40, count=10)
        avg_ping = sum(rtt.time_elapsed_ms for rtt in response_list) / 10
        if avg_ping > 100:  # Limite para ping alto
            send_whatsapp_alert(f'High ping detected to {host}: {avg_ping} ms')
        socketio.emit('ping_update', {'host': host, 'avg_ping': avg_ping})
        time.sleep(10)

def monitor_port(host, port):
    previous_status = None
    while True:
        status = check_port(host, port)
        if previous_status is not None and previous_status != status:
            status_msg = 'open' if status else 'closed'
            send_whatsapp_alert(f'Port {port} on {host} is now {status_msg}')
            socketio.emit('port_status_update', {'host': host, 'port': port, 'status': status_msg})
        previous_status = status
        time.sleep(10)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/api/scan_network', methods=['POST'])
def api_scan_network():
    ip_range = request.json['ip_range']
    hosts = scan_network(ip_range)
    return jsonify(hosts)

@app.route('/api/scan_ports', methods=['POST'])
def api_scan_ports():
    data = request.json
    ip_range = data.get('ip_range')
    ports_input = data.get('ports', '')

    # Processar o intervalo de IPs
    ip_list = [str(ip) for ip in ipaddress.IPv4Network(ip_range)]

    # Processar as portas, usando 1-1024 como padrão se o campo estiver vazio
    if not ports_input:
        ports = list(range(1, 1025))
    else:
        ports = list(map(int, ports_input.split(',')))  # Converte as portas para uma lista de inteiros

    all_open_ports = {}
    for ip in ip_list:
        if is_alive(ip):
            open_ports = scan_ports(ip, ports)  # Passa as portas para a função
            all_open_ports[ip] = open_ports
        else:
            all_open_ports[ip] = 'offline'
    
    return jsonify(all_open_ports)

@app.route('/api/monitor_ping', methods=['POST'])
def api_monitor_ping():
    host = request.json['host']
    Thread(target=monitor_ping, args=(host,)).start()
    return jsonify({'status': 'Monitoring started'})

@app.route('/api/monitor_port', methods=['POST'])
def api_monitor_port():
    host = request.json['host']
    port = request.json['port']
    Thread(target=monitor_port, args=(host, port)).start()
    return jsonify({'status': 'Monitoring started'})

@app.route('/scan_network', methods=['POST'])
def scan_network_page():
    ip_range = request.form['ip_range']
    hosts = scan_network(ip_range)
    return render_template('scan_network.html', hosts=hosts)

@app.route('/scan_ports', methods=['POST'])
def scan_ports_page():
    ip_range = request.form['ip_range_ports']
    ip_list = [str(ip) for ip in ipaddress.IPv4Network(ip_range)]
    all_open_ports = {}

    for ip in ip_list:
        if is_alive(ip):
            open_ports = scan_ports(ip, range(1, 1025))  # Verificando portas de 1 a 1024
            all_open_ports[ip] = open_ports
        else:
            all_open_ports[ip] = 'offline'
    
    return render_template('scan_ports.html', open_ports=all_open_ports)

@socketio.on('connect')
def handle_connect():
    emit('status', {'status': 'Connected to server'})

if __name__ == '__main__':
    socketio.run(app, debug=True)
# Testando a função is_alive com IPs específicos

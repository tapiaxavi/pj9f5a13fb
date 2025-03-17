<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpus = $_POST["cpus"];
    $ram = $_POST["ram"];
    $vm_name = $_POST["vm_name"];
    $hostname = $_POST["hostname"];
    $host_folder = $_POST["host_folder"];
    $vm_folder = $_POST["vm_folder"];
    $port_host = $_POST["port_host"];
    $port_vm = $_POST["port_vm"];
    $install_microk8s = isset($_POST["microk8s"]) && $_POST["microk8s"] === "yes";
}

// Script de aprovisionamiento base
$provision_script = <<<EOT
    sudo apt-get -y update
    sudo apt-get -y install net-tools whois aptitude git zip unzip
    sudo apt-get -y install apt-transport-https ca-certificates curl gnupg2 software-properties-common
    curl -fsSL https://download.docker.com/linux/debian/gpg | sudo apt-key add -
    sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
    sudo apt-get update -y
    sudo apt-get -y install docker-ce docker-ce-cli containerd.io docker-compose
    sudo chown -R vagrant:vagrant /home/vagrant/pj9f4a86
    sudo gpasswd -a vagrant docker
EOT;

// Agregar instalación de MicroK8s si se seleccionó
if ($install_microk8s) {
    $provision_script .= <<<EOT


    # Instalación de MicroK8s
    sudo snap install microk8s --classic
    sudo usermod -a -G microk8s vagrant
    sudo chown -R vagrant ~/.kube
    EOT;
}

// Generar el Vagrantfile
$vagrantfile = <<<EOT
# -*- mode: ruby -*-
# vi: set ft=ruby :


# VARIABLES

BOX_IMAGE = "debian/bookworm64"
PROVIDER = "virtualbox"
NUM_CPUS = $cpus
MEMORIA_RAM = $ram
NOM_MAQUINA = "$vm_name"
HOSTNAME = "$hostname"
CARPETA_MAQ_FIS = "$host_folder"
CARPETA_MAQ_VIR = "$vm_folder"
PORT_VIR1 = $port_vm
PORT_FIS1 = $port_host
PROT = "tcp"


# CONFIGURACIÓN DE LA MÁQUINA

Vagrant.configure("2") do |config|
    config.vm.box = BOX_IMAGE
    config.vm.provider PROVIDER do |vb|
        vb.cpus = NUM_CPUS
        vb.memory = MEMORIA_RAM
        vb.name = NOM_MAQUINA
    end

    # Carpeta compartida
    config.vm.synced_folder CARPETA_MAQ_FIS, CARPETA_MAQ_VIR

    # Redireccionamiento de puertos
    config.vm.network "forwarded_port", guest: PORT_VIR1, host: PORT_FIS1, protocol: PROT

    # Configuración del hostname
    config.vm.hostname = HOSTNAME

    # Configuración del aprovisionamiento
    config.vm.provision "shell", inline: <<-SHELL
        $provision_script
    SHELL
end
EOT;

// Establecer los encabezados para forzar la descarga del archivo
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="Vagrantfile"');
header('Content-Length: ' . strlen($vagrantfile));

// Imprimir el contenido del Vagrantfile para que se descargue
echo $vagrantfile;
exit;
?>

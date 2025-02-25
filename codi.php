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

# CONFIGURACIÓ DE LA MÀQUINA

Vagrant.configure("2") do |config|
    # BOX
    config.vm.box = BOX_IMAGE

    # CONFIGURACIÓ ESPECÍFICA DEL PROVIDER
    config.vm.provider PROVIDER do |provider|
        provider.name = NOM_MAQUINA
        provider.memory = MEMORIA_RAM
        provider.cpus = NUM_CPUS
        provider.customize ['modifyvm', :id, '--clipboard', 'bidirectional']
    end

    # CONFIGURACIÓ GENERAL
    config.vm.hostname = HOSTNAME
    config.vm.synced_folder CARPETA_MAQ_FIS, CARPETA_MAQ_VIR
    config.vm.network "public_network"
    config.vm.network "forwarded_port", guest: PORT_VIR1, host: PORT_FIS1, protocol: PROT

    # PROGRAMARI A INSTAL·LAR I ORDRES A EXECUTAR DURANT LA CREACIÓ DE LA MÀQUINA (PROVISION)
    config.vm.provision "shell", inline: <<-SHELL
        sudo apt-get -y update
        sudo apt-get -y install net-tools whois aptitude git zip unzip
        sudo apt-get -y install apt-transport-https ca-certificates curl gnupg2 software-properties-common
        curl -fsSL https://download.docker.com/linux/debian/gpg | sudo apt-key add -
        sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
        sudo apt-get update -y
        sudo sudo apt-get -y install docker-ce docker-ce-cli containerd.io docker-compose
        sudo chown -R vagrant:vagrant /home/vagrant/pj9f4a86
        sudo gpasswd -a vagrant docker        
    SHELL
end
EOT;

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="Vagrantfile"');
    echo $vagrantfile;
    exit;
}
?>

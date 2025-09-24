# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  # Box cơ bản
  config.vm.box = "ubuntu/focal64"
  config.vm.box_version = "20240821.0.1"

  # Port forward (HTTP 80 -> localhost:8080)
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"
  config.vm.network "forwarded_port", guest: 8081, host: 8081
  config.vm.network "forwarded_port", guest: 6379, host: 6379



  # Private network
  config.vm.network "private_network", ip: "192.168.33.10"

  # Public network (bridged)
  config.vm.network "public_network"

  # Share folder từ host -> guest
  config.vm.synced_folder "./sources", "/vagrant"
  config.vm.synced_folder "sources", "/vagrant/sources"

  # Disable default share
  config.vm.synced_folder ".", "/vagrant", disabled: true

  # Cấu hình VirtualBox
  config.vm.provider "virtualbox" do |vb|
    vb.gui = true
    vb.memory = "4096"
    vb.cpus = 2
  end

  # Script provision
  config.vm.provision "shell", inline: <<-SHELL
    apt-get update
    apt-get install -y apache2
    sudo apt-get install -y docker.io docker-compose
    sudo usermod -aG docker vagrant
    sudo apt install -y make
    sudo apt install -y git
    sudo apt install -y net-tools
  SHELL

    config.vm.provision "shell", inline: <<-SHELL
    apt-get update
    apt-get install -y apache2
    sudo apt-get install -y docker.io docker-compose
    sudo usermod -aG docker vagrant
    sudo apt install -y make
    sudo apt install -y git
    sudo apt install -y net-tools
    sudo apt-get install -y phpmyadmin php-mbstring php-zip php-gd php-json php-curl
    sudo phpenmod mbstring
    sudo systemctl restart apache2
    sudo ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin
  SHELL

  # Timeout boot
  config.vm.boot_timeout = 600
end

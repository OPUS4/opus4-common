# -*- mode: ruby -*-
# vi: set ft=ruby :

$setup = <<SCRIPT
# Downgrade to PHP 7.1
apt-add-repository -y ppa:ondrej/php
apt-get -yq update
apt-get -yq install php7.1

# Install required PHP packages
apt-get -yq install php7.1-dom
apt-get -yq install php7.1-mbstring

# Install Composer for running tests
apt-get -yq install composer
SCRIPT

Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-20.04"

  config.vm.provision 'shell', inline: $setup
end

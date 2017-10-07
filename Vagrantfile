# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
    config.vm.box = "ubuntu/xenial64"

    config.vm.synced_folder ".", "/vagrant",
        id: "core",
        :nfs => true, # This really improves VM speed
        :mount_options => ['nolock,vers=3,udp,noatime']

    config.vm.network "private_network", ip: "192.168.10.10"

    config.vm.network "forwarded_port", guest: 80,  host: 8080, host_ip: "127.0.0.1"
    config.vm.network "forwarded_port", guest: 443, host: 4443, host_ip: "127.0.0.1"

    # Assign a quarter of host memory and all available CPU's to VM
    # Depending on host OS this has to be done differently.
    config.vm.provider :virtualbox do |vb|
        host = RbConfig::CONFIG['host_os']
        vb.gui = false

        vb.customize ["modifyvm", :id, "--memory", 3072]
        vb.customize ["modifyvm", :id, "--cpus", 3]
    end

    config.vm.provision :shell, path: "vagrant/bootstrap.sh"

    # Run Ansible on the Vagrant Guest
    config.vm.provision "ansible_local", run: "always" do |ansible|
        ansible.playbook = "/vagrant/ansible/vagrant.yml"
        ansible.become = true
    end

end
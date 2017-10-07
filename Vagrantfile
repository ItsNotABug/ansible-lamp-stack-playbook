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

        if host =~ /darwin/
            cpus = `sysctl -n hw.ncpu`.to_i
            mem = `sysctl -n hw.memsize`.to_i / 1024 / 3072 / 3

        elsif host =~ /linux/
            cpus = `nproc`.to_i
            mem = `grep 'MemTotal' /proc/meminfo | sed -e 's/MemTotal://' -e 's/ kB//'`.to_i / 3072 / 3

        # Windows...
        else
            cpus = 3
            mem = 3072
        end

        vb.customize ["modifyvm", :id, "--memory", mem]
        vb.customize ["modifyvm", :id, "--cpus", cpus]
    end

    config.vm.provision :shell, path: "vagrant/bootstrap.sh"

    # Run Ansible on the Vagrant Guest
    config.vm.provision "ansible_local", run: "always" do |ansible|
        ansible.playbook = "/vagrant/ansible/vagrant.yml"
        ansible.sudo = true
    end

end
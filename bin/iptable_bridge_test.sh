#!/usr/bin/env bash


watch_incoming_command() {
 
    # Absolute path to this script. /home/user/bin/foo.sh
    SCRIPT=$(readlink -f $0)
    # Absolute path this script is in. /home/user/bin
    SCRIPTPATH=`dirname $SCRIPT`

    iptables_watching_file="${SCRIPTPATH}/test.txt"

    echo "[${timesamp}] Watching ${iptables_watching_file}...(${1})"

    if [ -e "${iptables_watching_file}" ]; then

        # command_code, ipv4/6, ip, port, protocol, action

        echo "file exist."

        lines=$(<$iptables_watching_file)
        
        # Start the loop.
        while IFS=',' read -r command type ip subnet port protocol action; do

            #if [ "${debug_mode}" == "1" ]; then
                echo "command: $command"
                echo "type: $type"
                echo "ip: $ip"
                echo "subnet: $subnet"
                echo "port: $port"
                echo "protocol: $protocol"
                echo "action: $action"

                # Check if the port is a number
            this_port=""

            # Check what protocol you want to apply on this rule.
            this_protocol=""

            # Check what action you want to apply on this rule.
            this_action=""

            this_command="-A"

            this_ip="-s ${ip}"

            this_subnet=""

            if [[ "$subnet" =~ ^[0-9]+$ ]]; then
                this_subnet="/${subnet}"
            fi

            if [[ "$port" =~ ^[0-9]+$ ]]; then
                this_port=" --dport ${port}"
            fi

            if [ "$protocol" == "udp" ]; then
                this_protocol=" -p udp"
            fi

            if [ "$protocol" == "tcp" ]; then
                this_protocol=" -p tcp"
            fi

            if [ "$action" == "deny" ]; then
                this_action=" -j DROP"
            fi

            if [ "$action" == "allow" ]; then
                this_action=" -j ACCEPT"
            fi

            if [ "$command" == "delete" ]; then
                # The default is "-A".
                this_command="-D"
            fi

            if [ "$this_action" != "" ]; then

                if [ "$type" == "4" ]; then

                    # We have to check the IP whether is a valid IPv4 string.
                    if [[ "${ip}" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then

                        # `--dport` comes along with `-p`
                        if [[ "$this_protocol" == "" && "$this_port" != "" ]]; then

                            iptables_command="${this_command} INPUT ${this_ip}${this_subnet} -p tcp ${this_port}${this_action}"
                            $(eval iptables "$iptables_command")
                            echo "Perform command => iptables ${iptables_command}"

                            iptables_command="${this_command} INPUT ${this_ip}${this_subnet} -p udp ${this_port}${this_action}"
                            $(eval iptables "$iptables_command")
                            echo "Perform command => iptables ${iptables_command}"
                        else
                            iptables_command="${this_command} INPUT ${this_ip}${this_subnet}${this_protocol}${this_port}${this_action}"
                            $(eval iptables "$iptables_command")
                            echo "Perform command => iptables ${iptables_command}"
                        fi

                    else
                        echo "Invalid IPv4 address."
                    fi
                fi

                if [ "$type" == "6" ]; then

                    # We have to check the IP whether is a valid IPv6 string.
                    if [[ "$ip" =~ ^([0-9a-fA-F]{0,4}:){1,7}[0-9a-fA-F]{0,4}$ ]]; then

                        if [[ "$this_protocol" == "" && "$this_port" != "" ]]; then

                            ip6tables_command="${this_command} INPUT ${this_ip} -p tcp ${this_port}${this_action}"
                            $(eval ip6tables "$ip6tables_command")
                            echo "Perform command => ip6tables ${ip6tables_command}"

                            ip6tables_command="${this_command} INPUT ${this_ip} -p udp ${this_port}${this_action}"
                            $(eval ip6tables "$ip6tables_command")
                            echo "Perform command => ip6tables ${ip6tables_command}"
                        else
                            ip6tables_command="${this_command} INPUT ${this_ip}${this_protocol}${this_port}${this_action}"
                            $(eval ip6tables "$ip6tables_command")
                            echo "Perform command => ip6tables ${ip6tables_command}"
                        fi
                    else
                        echo "Invalid IPv6 address."
                    fi
                fi  
            fi

        done <<< "${lines}"
        IFS=''

    else
        echo "Missing file: ${iptables_watching_file}"
    fi

}

watch_incoming_command

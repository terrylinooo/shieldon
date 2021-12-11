#!/usr/bin/env bash
#>                           +--------------------+
#>                           | iptables_bridge.sh |   
#>                           +--------------------+
#-
#- SYNOPSIS
#-
#-    iptables_bridge.sh [-h] [-i] [-w [log_directory]]
#-
#- OPTIONS
#-
#-    -w ?, --watch=?      Watch the directory where the firewall logs are stored.
#-    -c, --clear          Clear all input records.
#-    -t, --test           In testing mode.
#-    -h, --help           Print this help.
#-    -i, --info           Print script information.
#-
#- EXAMPLES
#-
#-    $ ./iptables_bridge.sh -w /tmp/shieldon_iptable
#-    $ ./iptables_bridge.sh --watch=/tmp/shieldon_iptable
#+
#+ IMPLEMENTATION:
#+
#+    package    Shieldon
#+    copyright  https://github.com/terrylinooo/shieldon
#+    license    MIT
#+    authors    Terry Lin (terrylinooo)
#+ 
#==============================================================================

#==============================================================================
# Part 1. Config
#==============================================================================

per_second="1"
debug_mode="0"
timestamp="$(date +%s)"
date="$(date +%Y-%m-%d' '%H:%M:%S)"

# Absolute path to this script.
SCRIPT=$(readlink -f $0)
# Absolute path this script is in.
SCRIPTPATH=`dirname $SCRIPT`

DEV_MODE="false"

#==============================================================================
# Part 2. Option (DO NOT MODIFY)
#==============================================================================

# Print script help
show_script_help() {
    echo 
    head -50 ${0} | grep -e "^#[-|>]" | sed -e "s/^#[-|>]*/ /g"
    echo 
}

# Print script info
show_script_information() {
    echo 
    head -50 ${0} | grep -e "^#[+|>]" | sed -e "s/^#[+|>]*/ /g"
    echo 
}

# Receive arguments.
if [ "$#" -gt 0 ]; then
    while [ "$#" -gt 0 ]; do
        case "$1" in
            # Specify the path of iptables logs' directory.
            "-w") 
                iptables_log_folder="${2}"
                shift 2
            ;;
            "--watch="*) 
                iptables_log_folder="${1#*=}"
                shift 1
            ;;
            # Clear INPUT chain rules.
            "-c"|"--clear")
                # Prevent you block yourself out of the server if the default rule is DENY...
                iptables -P INPUT ACCEPT
                # Flush rules.
                iptables -F INPUT
                exit 1
            ;;
            # Testing
            "-t"|"--test")
                DEV_MODE="true"
                shift 1
            ;;
            # Info
            "-i"|"--information")
                show_script_information
                exit 1
            ;;
            "-"*)
                echo "Unknown option: ${1}"
                exit 1
            ;;
            *)
                echo "Unknown option: ${1}"
                exit 1
            ;;
        esac
    done
fi

#==============================================================================
# Part 4. Function
#==============================================================================

watch_incoming_command() {

    # Assign absolute path.
    iptables_watching_file="${iptables_log_folder}/iptables_queue.log"
    ipv4_status_log_file="${iptables_log_folder}/ipv4_status.log"
    ipv6_status_log_file="${iptables_log_folder}/ipv6_status.log"
    ipv4_command_log_file="${iptables_log_folder}/ipv4_command.log"
    ipv6_command_log_file="${iptables_log_folder}/ipv6_command.log"

    if [ "${DEV_MODE}" == "true" ]; then
        iptables_watching_file="${SCRIPTPATH}/test.txt"
        ipv4_status_log_file="${SCRIPTPATH}/ipv4_status.log"
        ipv6_status_log_file="${SCRIPTPATH}/ipv6_status.log"
        ipv4_command_log_file="${SCRIPTPATH}/ipv4_command.log"
        ipv6_command_log_file="${SCRIPTPATH}/ipv6_command.log"
    fi

    # echo "${1}"

    if [ -e "$iptables_watching_file" ]; then

        # command_code, ipv4/6, action, ip, port, protocol, action
        lines=$(<"$iptables_watching_file")
        
        while IFS=',' read -r command type ip subnet port protocol action; do

            if [ "$debug_mode" == "1" ]; then
                echo "command: $command"
                echo "type: $type"
                echo "ip: $ip"
                echo "subnet: $subnet"
                echo "port: $port"
                echo "protocol: $protocol"
                echo "action: $action"
            fi

            ## Reset iptables INPUT chain.
            if [ "$command" == "reset" ]; then
   
                if [ "$type" == "4" ]; then
                    iptables -P INPUT ACCEPT
                    iptables -F INPUT
                    iptables -Z INPUT
                    cat /dev/null > "$ipv4_command_log_file"
                fi

                if [ "$type" == "6" ]; then
                    ip6tables -P INPUT ACCEPT
                    ip6tables -F INPUT
                    ip6tables -Z INPUT
                    cat /dev/null > "$ipv6_command_log_file"
                fi

                continue
            fi

            current_command="${command},${type},${ip},${subnet},${port},${protocol},${action}"

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

                        if [[ "$this_protocol" == "" && "$this_port" != "" ]]; then

                            iptables_command="${this_command} INPUT ${this_ip}${this_subnet} -p tcp ${this_port}${this_action}"
                            $(eval iptables "$iptables_command")
                            echo "[${date}] Perform command => iptables ${iptables_command}"

                            iptables_command="${this_command} INPUT ${this_ip}${this_subnet} -p udp ${this_port}${this_action}"
                            $(eval iptables "$iptables_command")
                            echo "[${date}] Perform command => iptables ${iptables_command}"

                            if [ "$command" == "add" ]; then
                                current_command_1="${command},${type},${ip},${subnet},${port},tcp,${action}"
                                current_command_2="${command},${type},${ip},${subnet},${port},udp,${action}"

                                echo "$current_command_1" >> "$ipv4_command_log_file"
                                echo "$current_command_2" >> "$ipv4_command_log_file"
                            fi
                        else
                            iptables_command="${this_command} INPUT ${this_ip}${this_subnet}${this_protocol}${this_port}${this_action}"
                            $(eval iptables "$iptables_command")
                            echo "[${date}] Perform command => iptables ${iptables_command}"

                            if [ "$command" == "add" ]; then
                                echo "$current_command" >> "$ipv4_command_log_file"
                            fi
                        fi
                    else
                        echo "Invalid IPv4 address."
                    fi
                fi

                if [ "$type" == "6" ]; then

                    if [[ "$ip" =~ ^([0-9a-fA-F]{0,4}:){1,7}[0-9a-fA-F]{0,4}$ ]]; then

                        if [[ "$this_protocol" == "" && "$this_port" != "" ]]; then

                            ip6tables_command="${this_command} INPUT ${this_ip} -p tcp ${this_port}${this_action}"
                            $(eval ip6tables "$ip6tables_command")
                            echo "[${date}] Perform command => ip6tables ${ip6tables_command}"

                            ip6tables_command="${this_command} INPUT ${this_ip} -p udp ${this_port}${this_action}"
                            $(eval ip6tables "$ip6tables_command")
                            echo "[${date}] Perform command => ip6tables ${ip6tables_command}"

                            if [ "$command" == "add" ]; then
                                current_command_1="${command},${type},${ip},${subnet},${port},tcp,${action}"
                                current_command_2="${command},${type},${ip},${subnet},${port},udp,${action}"

                                echo "$current_command_1" >> "$ipv6_command_log_file"
                                echo "$current_command_2" >> "$ipv6_command_log_file"
                            fi
                        else
                            ip6tables_command="${this_command} INPUT ${this_ip}${this_subnet}${this_protocol}${this_port}${this_action}"
                            $(eval ip6tables "$ip6tables_command")
                            echo "[${date}] Perform command => ip6tables ${ip6tables_command}"

                            if [ "$command" == "add" ]; then
                                echo "$current_command" >> "$ipv6_command_log_file"
                            fi
                        fi
                    else
                        echo "[${date}] Invalid IPv6 address."
                    fi
                fi  
            fi

        done <<< "$lines"

        if [ ${1} == 1 ]; then
            status_iptables=$(iptables -L)
            status_ip6tables=$(ip6tables -L)

            # Update iptables and ip6tables status content.
            echo "${status_iptables}" > "${ipv4_status_log_file}"
            echo "${status_ip6tables}" > "${ipv6_status_log_file}"
        fi

        #==============================================================================
        # Part 4. Done. Empty the iptables_queue.log
        #==============================================================================

        if [ "${DEV_MODE}" == "false" ]; then
            truncate -s 0 "$iptables_watching_file"
        fi

        # Continue to wait for new commands to come.
    else
        echo "[${date}] Missing file: ${iptables_watching_file}"
    fi
}

#==============================================================================
# Part 5. Watch
#==============================================================================

if [ "${DEV_MODE}" == "false" ]; then
    i="0"
    while [ $i -lt 60 ]; do
        watch_incoming_command "$i"
        i=$(($i+$per_second))
        sleep "$per_second"
    done
else
    watch_incoming_command
fi

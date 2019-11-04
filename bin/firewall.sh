#!/usr/bin/env bash
#>                           +---------------+
#>                           |  firewall.sh  |   
#>                           +---------------+
#-
#- SYNOPSIS
#-
#-    firewall.sh [-h] [-i] [-w [log_directory]]
#-
#- OPTIONS
#-
#-    -w ?, --watch=?      Watch the directory where the firewall logs are stored.
#-    -c, --clear          Clear all input records.
#-    -h, --help           Print this help.
#-    -i, --info           Print script information.
#-
#- EXAMPLES
#-
#-    $ ./firewall.sh -w /tmp/shieldon_iptable
#-    $ ./firewall.sh --watch=/tmp/shieldon_iptable
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

# iptables_log_folder=""
# iptables_watching_file="iptables_queue.log"
# iptables_status_log_file="iptables_status.log"

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
# Part 3. Main part.
#==============================================================================

# Assign absolute path.
iptables_watching_file="${iptables_log_folder}/iptables_queue.log"
iptables_status_log_file="${iptables_log_folder}/iptables_status.log"

if [ -e "${iptables_watching_file}" ]; then

    # command_code, ipv4/6, action, ip, port, protocol, action
    while IFS=',' read -r command_code ip_type ip port protocol action; do

        # Check if the port is a number
        this_port=""

        # Check what protocol you want to apply on this rule.
        this_protocol=""

        # Check what action you want to apply on this rule.
        this_action=""

        this_command="-A"

        this_ip="-s ${ip}"

        if [[ "${port}" =~ ^[0-9]+$ ]]; then
            this_port="--dport ${port}"
        fi

        if [ "${protocol}" == "udp" ]; then
            this_protocol="-p udp"
        fi

        if [ "${protocol}" == "tcp" ]; then
            this_protocol="-p tcp"
        fi

        if [ "${action}" == "deny" ]; then
            this_action="j DROP"
        fi

        if [ "${protocol}" == "allow" ]; then
            this_action="j ACCEPT"
        fi

        if [ "${command_code}" == "delete" ]; then
            this_command="-D"
        fi

        if [ "${this_action}" != "" ]; then
            if [ "${ip_type}" == "4" ]; then
                iptables "${this_command}" INPUT "${this_ip}" "${this_port}" "${this_protocol}" "${this_action}"
            fi

            if [ "${ip_type}" == "6" ]; then
                ip6tables "${this_command}" INPUT "${this_ip}" "${this_port}" "${this_protocol}" "${this_action}"
            fi  
        fi

    done < "${iptables_watching_file}"
fi

status_iptables=$(iptables -L)
status_ip6tables=$(ip6tables -L)

# Update iptables and ip6tables status content.
echo "${status_iptables} \n\n----\n\n ${status_ip6tables}" > "${iptables_status_log_file}"

#==============================================================================
# Part 4. Done. Empty the iptables_queue.log
#==============================================================================

truncate -s 0 "${iptables_watching_file}"

# Continue to wait for new commands to come.
